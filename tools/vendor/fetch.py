#!/usr/bin/env python3
"""
Vendor the PHP dependencies from GitHub tarballs.

Composer cannot run in this environment (no Packagist access), so this script
resolves a tag per package, downloads the tarball and extracts it into vendor/.
It is idempotent: already-cached tarballs and extracted packages are reused.

    python3 tools/vendor/fetch.py
    python3 tools/wasm/gen-autoload.py
"""
import json, os, re, subprocess, sys, concurrent.futures as cf
import urllib.request
sys.path.insert(0, os.path.dirname(__file__))
from packages import PACKAGES, PINS

BUILD = os.path.dirname(os.path.dirname(os.path.dirname(os.path.abspath(__file__))))
VENDOR = os.path.join(BUILD, "vendor")
CACHE = os.environ.get("VENDOR_CACHE", os.path.join(BUILD, "..", ".vendor-cache"))
os.makedirs(CACHE, exist_ok=True)

def api(url):
    req = urllib.request.Request(url, headers={"Accept": "application/vnd.github+json"})
    with urllib.request.urlopen(req, timeout=40) as r:
        return json.load(r)

def best_tag(repo):
    tags = []
    for page in (1, 2, 3, 4):
        try:
            data = api(f"https://api.github.com/repos/{repo}/tags?per_page=100&page={page}")
        except Exception:
            break
        if not data:
            break
        tags += [t["name"] for t in data]
        if len(data) < 100:
            break
    stable = [t for t in tags if re.fullmatch(r"v?\d+\.\d+\.\d+", t)]
    pool = stable or [t for t in tags if re.fullmatch(r"v?\d+(\.\d+)?", t)] or tags
    def key(t):
        return tuple(int(p) for p in re.findall(r"\d+", t))
    pool.sort(key=key)
    return pool[-1] if pool else None

def fetch(pkg, repo):
    tag = PINS.get(pkg) or best_tag(repo)
    if not tag:
        return (pkg, repo, None, "no tag")
    tarball = os.path.join(CACHE, pkg.replace("/", "__") + ".tar.gz")
    if not os.path.exists(tarball) or os.path.getsize(tarball) < 400:
        url = f"https://codeload.github.com/{repo}/tar.gz/refs/tags/{tag}"
        try:
            with urllib.request.urlopen(url, timeout=120) as r, open(tarball, "wb") as f:
                f.write(r.read())
        except Exception as e:
            return (pkg, repo, tag, f"download failed: {e}")
    dest = os.path.join(VENDOR, pkg)
    os.makedirs(dest, exist_ok=True)
    subprocess.run(["tar", "xzf", tarball, "-C", dest, "--strip-components=1"], check=True)
    return (pkg, repo, tag, "ok")

if __name__ == "__main__":
    results = []
    with cf.ThreadPoolExecutor(max_workers=8) as ex:
        for r in ex.map(lambda p: fetch(*p), PACKAGES):
            results.append(r)
            print("\t".join(str(x) for x in r), flush=True)
    ok = [r for r in results if r[3] == "ok"]
    print(f"\n{len(ok)}/{len(results)} extracted")
    json.dump([{"name": r[0], "repo": r[1], "tag": r[2]} for r in ok],
              open(os.path.join(VENDOR, ".sources.json"), "w"), indent=2)
