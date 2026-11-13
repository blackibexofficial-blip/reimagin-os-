#!/usr/bin/env python3
"""Resolve latest vX.Y.Z tag for a GitHub repo via the API, preferring the newest stable tag."""
import json, sys, urllib.request, re

def tags(repo):
    out = []
    for page in (1, 2, 3):
        url = f"https://api.github.com/repos/{repo}/tags?per_page=100&page={page}"
        try:
            with urllib.request.urlopen(url, timeout=30) as r:
                data = json.load(r)
        except Exception as e:
            print(f"  ERR {repo} p{page}: {e}", file=sys.stderr)
            break
        if not data:
            break
        out.extend(t["name"] for t in data)
        if len(data) < 100:
            break
    return out

def best(tags):
    stable = [t for t in tags if re.fullmatch(r"v?\d+\.\d+\.\d+", t)]
    if not stable:
        stable = [t for t in tags if re.fullmatch(r"v?\d+\.\d+(\.\d+)?", t)]
    def key(t):
        parts = re.findall(r"\d+", t)
        return tuple(int(p) for p in parts)
    stable.sort(key=key)
    return stable[-1] if stable else (tags[0] if tags else None)

if __name__ == "__main__":
    for repo in sys.argv[1:]:
        t = best(tags(repo))
        print(f"{repo}\t{t}")
