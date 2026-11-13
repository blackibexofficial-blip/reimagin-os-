#!/usr/bin/env node
/** Run a PHP file with the Laravel app bootstrapped inside php-wasm. */
import { PHP } from '@php-wasm/universal';
import { loadNodeRuntime, useHostFilesystem } from '@php-wasm/node';
import path from 'node:path';
import process from 'node:process';

const base = path.resolve(import.meta.dirname, '../..');
process.chdir(base);
const [file, ...rest] = process.argv.slice(2);
if (!file) { console.error('usage: exec.mjs <script.php>'); process.exit(1); }

const php = new PHP(await loadNodeRuntime('8.4', { emscriptenOptions: { processId: process.pid % 100000 } }));
useHostFilesystem(php);

const code = `<?php
$GLOBALS['RL_EXEC_FILE'] = ${JSON.stringify(path.resolve(base, file))};
$GLOBALS['RL_EXEC_ARGS'] = ${JSON.stringify(rest)};
require ${JSON.stringify(base + '/tools/wasm/exec.php')};
`;

let result;
try { result = await php.run({ code }); } finally { try { php.exit(); } catch {} }
if (result.text) process.stdout.write(result.text);
if (result.errors) process.stderr.write(result.errors);
process.exit(result.exitCode ?? 0);
