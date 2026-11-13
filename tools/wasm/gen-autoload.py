#!/usr/bin/env python3
"""
Generate tools/wasm/psr4.php from vendor/.sources.json plus composer's own
psr-4 declarations.

Composer is not available in this sandbox (no Packagist access), so the
autoloader that Laravel's bootstrap requires is generated here instead. Run this
after adding a class or a vendor package:

    python3 tools/wasm/gen-autoload.py
"""
import json
import os
import re

BASE = os.path.dirname(os.path.dirname(os.path.dirname(os.path.abspath(__file__))))
VENDOR = os.path.join(BASE, "vendor")
OUT = os.path.join(BASE, "tools", "wasm", "psr4.php")

# Composer is unavailable, so the entry point Laravel's public/index.php expects
# (vendor/autoload.php) is written here as a thin proxy onto the generated map.
COMPOSER_ENTRY = os.path.join(VENDOR, "autoload.php")
COMPOSER_ENTRY_SOURCE = """<?php
/*
 * Stand-in for Composer's generated autoloader (written by
 * tools/wasm/gen-autoload.py). Regenerate with `npm run autoload`.
 */

return require dirname(__DIR__).'/tools/wasm/autoload.php';
"""

# Fallbacks for packages that declare no psr-4 autoload at all.
EXTRA_PSR4 = {
    "sebastian/version": {"SebastianBergmann\\Version\\": "src"},
    "sebastian/type": {"SebastianBergmann\\Type\\": "src"},
    "sebastian/lines-of-code": {"SebastianBergmann\\LinesOfCode\\": "src"},
    "sebastian/object-enumerator": {"SebastianBergmann\\ObjectEnumerator\\": "src"},
    "sebastian/object-reflector": {"SebastianBergmann\\ObjectReflector\\": "src"},
    "sebastian/recursion-context": {"SebastianBergmann\\RecursionContext\\": "src"},
    "sebastian/code-unit-reverse-lookup": {"SebastianBergmann\\CodeUnitReverseLookup\\": "src"},
    "phar-io/version": {"PharIo\\Version\\": "src"},
    "theseer/tokenizer": {"TheSeer\\Tokenizer\\": "src"},
    "symfony/polyfill-php86": {},
}

EXTRA = {
    "nesbot/carbon": {"Carbon\\": "src/Carbon"},
    "phpoption/phpoption": {"PhpOption\\": "src/PhpOption"},
    "graham-campbell/result-type": {"GrahamCampbell\\ResultType\\": "src"},
    "masterminds/html5": {"Masterminds\\": "src"},
    "monolog/monolog": {"Monolog\\": "src/Monolog"},
    "voku/portable-ascii": {"voku\\": "src/voku"},
    "nikic/php-parser": {"PhpParser\\": "lib/PhpParser"},
    "myclabs/deep-copy": {"DeepCopy\\": "src/DeepCopy"},
}


def read_json(path):
    try:
        with open(path, encoding="utf-8") as fh:
            return json.load(fh)
    except Exception:
        return {}


def psr4_for(prefix, path):
    """Normalise a composer psr-4 value into absolute directories."""
    out = []
    for rel in (path if isinstance(path, list) else [path]):
        target = os.path.normpath(os.path.join(prefix, rel.lstrip("/")))
        if os.path.isdir(target):
            out.append(target)
    return out


def main():
    psr4 = {}
    classmap = {}
    files = []

    app = read_json(os.path.join(BASE, "composer.json"))
    sections = [app.get("autoload", {}), app.get("autoload-dev", {})]

    for section in sections:
        for ns, paths in section.get("psr-4", {}).items():
            dirs = psr4_for(BASE, paths)
            if dirs:
                psr4.setdefault(ns, []).extend(dirs)
        for path in section.get("files", []):
            files.append(os.path.join(BASE, path))
        for path in section.get("classmap", []):
            classmap[path] = None

    sources = read_json(os.path.join(VENDOR, ".sources.json"))
    for pkg in sources:
        name = pkg["name"]
        root = os.path.join(VENDOR, name)
        meta = read_json(os.path.join(root, "composer.json"))
        declared = meta.get("autoload", {}).get("psr-4", {})

        if not declared and name in EXTRA:
            declared = EXTRA[name]

        if not declared and name in EXTRA_PSR4:
            declared = EXTRA_PSR4[name]

        for path in meta.get("autoload", {}).get("classmap", []):
            target = os.path.normpath(os.path.join(root, path))
            if os.path.isdir(target):
                for dirpath, _dirs, filenames in os.walk(target):
                    for filename in filenames:
                        if filename.endswith(".php"):
                            full = os.path.join(dirpath, filename)
                            with open(full, encoding="utf-8", errors="ignore") as fh:
                                text = fh.read()
                            import re as _re
                            found = _re.search(r"^namespace\s+([^;]+);", text, _re.M)
                            cls = _re.search(
                                r"^(?:(?:final|abstract|readonly)\s+)*(?:class|interface|trait|enum)\s+(\w+)",
                                text,
                                _re.M,
                            )
                            if found and cls:
                                classmap[found.group(1).strip() + "\\" + cls.group(1)] = full

        if not declared:
            print(f"  warn: no psr-4 for {name}")
            continue

        for ns, paths in declared.items():
            dirs = psr4_for(root, paths)
            if dirs:
                psr4.setdefault(ns, []).extend(dirs)

        for rel in meta.get("autoload", {}).get("files", []):
            target = os.path.join(root, rel)
            if os.path.isfile(target):
                files.append(target)

        # polyfill classmap stubs declare global classes conditionally, so they
        # must be loaded as files rather than mapped by class name. PHP 8.4
        # already ships Attribute/Override/SortDirection, so only the stubs for
        # features newer than the runtime are needed.
        if not name.startswith("symfony/polyfill-php8"):
            continue

        if name != "symfony/polyfill-php86":
            continue

        for rel in meta.get("autoload", {}).get("classmap", []):
            stub_dir = os.path.join(root, rel)
            if not os.path.isdir(stub_dir):
                continue
            for dirpath, _dirs, filenames in os.walk(stub_dir):
                for filename in sorted(filenames):
                    if filename.endswith(".php"):
                        files.append(os.path.join(dirpath, filename))

    # phpunit ships PEAR-style class names (PHPUnit\TextUI\Application in
    # src/TextUI/Application.php) which psr-4 covers, but its phar loader needs
    # the classmap entry points registered explicitly.
    classmap["vendor/phpunit/phpunit/phpunit"] = None

    classes = {k: v for k, v in classmap.items() if v}

    with open(OUT, "w", encoding="utf-8") as fh:
        fh.write("<?php\n/* Generated by tools/wasm/gen-autoload.py — do not edit. */\n")
        fh.write("$psr4 = " + php_array(psr4) + ";\n")
        fh.write("$classmap = " + php_array(classes) + ";\n")
        fh.write("$files = " + php_array(files) + ";\n")
        fh.write("return ['psr4' => $psr4, 'classmap' => $classmap, 'files' => $files];\n")


    os.makedirs(VENDOR, exist_ok=True)
    with open(COMPOSER_ENTRY, "w", encoding="utf-8") as fh:
        fh.write(COMPOSER_ENTRY_SOURCE)

    print(f"{len(psr4)} psr-4 prefixes, {len(classes)} classmap symbols, {len(files)} files")


def php_array(value):
    if isinstance(value, dict):
        if not value:
            return "[]"
        inner = ",\n".join(
            php_scalar(k) + " => " + php_array(v) for k, v in value.items()
        )
        return "[\n" + inner + ",\n]"
    if isinstance(value, list):
        if not value:
            return "[]"
        if all(isinstance(v, str) for v in value):
            return "[" + ", ".join(php_scalar(v) for v in value) + "]"
        return "[\n" + ",\n".join(php_array(v) for v in value) + ",\n]"
    return php_scalar(value)


def php_scalar(value):
    return "'" + str(value).replace("\\", "\\\\").replace("'", "\\'") + "'"


if __name__ == "__main__":
    main()
