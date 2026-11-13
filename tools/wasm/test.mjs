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
} catch (error) {
  /* A failing suite exits non-zero, which PHP.run reports by throwing; the
   * buffered output is what the developer actually needs to read. */
  const response = error?.response;
  if (response?.text) process.stdout.write(response.text);
  if (response?.errors) process.stderr.write(response.errors);
  process.exit(typeof response?.exitCode === 'number' ? response.exitCode : 1);
} finally {
  try { php.exit(); } catch { /* already exited */ }
}

if (result.text) process.stdout.write(result.text);
if (result.errors) process.stderr.write(result.errors);
process.exit(result.exitCode ?? 0);
