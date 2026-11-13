#!/usr/bin/env node
/**
 * Run `php artisan <command>` inside php-wasm.
 *
 * Usage: node --experimental-wasm-jspi tools/wasm/artisan.mjs <command> [args]
 */
import { PHP } from '@php-wasm/universal';
import { loadNodeRuntime, useHostFilesystem } from '@php-wasm/node';
import path from 'node:path';
import process from 'node:process';

const base = path.resolve(import.meta.dirname, '../..');
process.chdir(base);

const args = process.argv.slice(2);

const php = new PHP(await loadNodeRuntime('8.4', {
  emscriptenOptions: { processId: process.pid % 100000 },
}));
useHostFilesystem(php);

const code = `<?php
$GLOBALS['RL_ARGV'] = ${JSON.stringify(['artisan', ...args])};
require ${JSON.stringify(base + '/tools/wasm/artisan.php')};
`;

let result;
try {
  result = await php.run({ code });
} finally {
  try { php.exit(); } catch { /* already exited */ }
}

if (result.text) process.stdout.write(result.text);
if (result.errors) process.stderr.write(result.errors);
process.exit(result.exitCode ?? 0);
