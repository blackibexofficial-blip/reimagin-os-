#!/usr/bin/env python3
"""Strip Laravel's HTML error/console chrome so php-wasm output is readable."""
import html
import re
import sys

text = sys.stdin.read()
text = re.sub(r"<style.*?</style>", "", text, flags=re.S)
text = re.sub(r"<script.*?</script>", "", text, flags=re.S)
text = re.sub(r"<[^>]+>", " ", text)
text = html.unescape(text)
text = re.sub(r"[ \t]+", " ", text)
text = re.sub(r"\n\s*\n+", "\n", text)
print(text.strip()[:6000])
