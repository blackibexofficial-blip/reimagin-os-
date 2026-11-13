#!/usr/bin/env bash
# Convenience wrapper: pretty-print artisan output from php-wasm.
#   tools/wasm/run.sh migrate:fresh --seed
set -u
cd "$(dirname "$0")/../.." || exit 1
node --experimental-wasm-jspi tools/wasm/artisan.mjs "$@" 2>&1 | python3 tools/wasm/pretty.py
