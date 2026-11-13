#!/usr/bin/env node
/**
 * Serve the Laravel app through php-wasm over plain HTTP.
 *
 * Usage: node --experimental-wasm-jspi tools/wasm/serve.mjs [port]
 *
 * Every request is proxied into the PHP runtime's own request handler, so
 * routing, sessions and Blade behave exactly as they would behind PHP-FPM.
 * The server binds 0.0.0.0 because the preview URL is served from outside
 * this sandbox, and it rewrites unknown paths to public/index.php so Laravel
 * (not the handler) decides what 404 means.
 */
import { PHP, PHPRequestHandler } from '@php-wasm/universal';
import { loadNodeRuntime, useHostFilesystem } from '@php-wasm/node';
import http from 'node:http';
import path from 'node:path';
import process from 'node:process';

const base = path.resolve(import.meta.dirname, '../..');
const port = Number(process.argv[2] || process.env.PORT || 8000);
const publicDir = path.join(base, 'public');

process.chdir(base);

const php = new PHP(await loadNodeRuntime('8.4', {
  emscriptenOptions: { processId: process.pid % 100000 },
}));
useHostFilesystem(php);

const handler = new PHPRequestHandler({
  php,
  documentRoot: publicDir,
  absoluteUrl: `http://localhost:${port}`,
  rewriteRules: [
    { match: /^\/index\.php/, replacement: '/index.php' },
  ],
  getFileNotFoundAction: (relativePath) => {
    // Static assets that genuinely do not exist should 404; everything else
    // belongs to Laravel's front controller.
    if (/\.(?:css|js|map|png|jpe?g|gif|svg|webp|avif|ico|woff2?|ttf|txt|xml)$/i.test(relativePath)) {
      return { type: '404' };
    }
    return { type: 'internal-redirect', uri: '/index.php' };
  },
});

function collectBody(req) {
  return new Promise((resolve, reject) => {
    const chunks = [];
    req.on('data', (chunk) => chunks.push(chunk));
    req.on('end', () => resolve(Buffer.concat(chunks)));
    req.on('error', reject);
  });
}

const server = http.createServer(async (req, res) => {
  try {
    const body = await collectBody(req);
    const headers = {};
    for (const [key, value] of Object.entries(req.headers)) {
      if (typeof value === 'string') headers[key] = value;
    }

    const response = await handler.request({
      url: req.url || '/',
      method: req.method || 'GET',
      headers,
      body: body.length ? body : undefined,
    });

    res.statusCode = response.httpStatusCode || 200;
    for (const [key, values] of Object.entries(response.headers || {})) {
      if (key.toLowerCase() === 'content-length') continue;
      res.setHeader(key, Array.isArray(values) ? values : String(values));
    }
    const payload = response.bytes;
    res.end(payload ? Buffer.from(payload) : '');
  } catch (error) {
    console.error(`[serve] ${req.method} ${req.url} failed:`, error?.message || error);
    res.statusCode = 500;
    res.setHeader('content-type', 'text/plain; charset=utf-8');
    res.end(`PHP runtime error: ${error?.message || error}`);
  }
});

server.listen(port, '0.0.0.0', () => {
  console.log(`Reimagin OS is serving http://0.0.0.0:${port} from ${publicDir}`);
});
