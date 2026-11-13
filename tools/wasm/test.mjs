#!/usr/bin/env node
/**
 * Run the PHPUnit suite inside php-wasm.
 * Usage: node --experimental-wasm-jspi tools/wasm/test.mjs [phpunit args]
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
$_SERVER['argv'] = ${JSON.stringify(['phpunit', '--configuration', 'phpunit.xml', ...args])};
$_SERVER['argc'] = ${args.length + 3};
$_SERVER['SCRIPT_NAME'] = 'vendor/phpunit/phpunit/phpunit';
require ${JSON.stringify(base + '/tools/wasm/phpunit-bootstrap.php')};
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
