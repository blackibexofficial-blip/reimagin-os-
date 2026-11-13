<?php

namespace Composer\Autoload;

/**
 * Minimal stand-in for composer's ClassLoader.
 *
 * The php-wasm development runtime has no composer install step, so autoloading
 * is driven by tools/wasm/psr4.php. Implemented methods are the ones Laravel,
 * PHPUnit and their helpers actually call.
 */
class ClassLoader
{
    /** @var array<string, ClassLoader> */
    private static array $registeredLoaders = [];

    /** @var array<string, string[]> */
    private array $prefixesPsr4 = [];

    /** @var array<string, string> */
    private array $classMap = [];

    private bool $classMapAuthoritative = false;

    public static function getRegisteredLoaders(): array
    {
        return self::$registeredLoaders;
    }

    public static function getRegisteredLoader(string $vendorDir): ?self
    {
        $normalised = rtrim($vendorDir, '/');

        foreach (self::$registeredLoaders as $vendor => $loader) {
            if (rtrim($vendor, '/') === $normalised) {
                return $loader;
            }
        }

        return null;
    }

    public function register(bool $prepend = false): void
    {
        spl_autoload_register([$this, 'loadClass'], true, $prepend);

        self::$registeredLoaders[defined('RL_BASE_PATH') ? RL_BASE_PATH.'/vendor' : 'vendor'] = $this;
    }

    public function unregister(): void
    {
        spl_autoload_unregister([$this, 'loadClass']);

        foreach (self::$registeredLoaders as $vendor => $loader) {
            if ($loader === $this) {
                unset(self::$registeredLoaders[$vendor]);
            }
        }
    }

    public function addPsr4(string $prefix, array|string $paths, bool $prepend = false): void
    {
        foreach ((array) $paths as $path) {
            if ($prepend) {
                array_unshift($this->prefixesPsr4[$prefix], $path);
            } else {
                $this->prefixesPsr4[$prefix][] = $path;
            }
        }
    }

    public function setPsr4(string $prefix, array|string $paths): void
    {
        $this->prefixesPsr4[$prefix] = array_values((array) $paths);
    }

    public function getPrefixesPsr4(): array
    {
        return $this->prefixesPsr4;
    }

    public function getPrefixes(): array
    {
        return [];
    }

    public function getFallbackDirsPsr4(): array
    {
        return $this->prefixesPsr4[''] ?? [];
    }

    public function addClassMap(array $classMap): void
    {
        $this->classMap = array_merge($this->classMap, $classMap);
    }

    public function getClassMap(): array
    {
        return $this->classMap;
    }

    public function setClassMapAuthoritative(bool $classMapAuthoritative): void
    {
        $this->classMapAuthoritative = $classMapAuthoritative;
    }

    public function isClassMapAuthoritative(): bool
    {
        return $this->classMapAuthoritative;
    }

    public function findFile(string $class): string|false
    {
        if (isset($this->classMap[$class])) {
            return $this->classMap[$class];
        }

        if ($this->classMapAuthoritative) {
            return false;
        }

        $logical = ltrim($class, '\\');

        // Composer tries every matching prefix, longest first, because a nested
        // psr-4 prefix (Illuminate\Support\) only covers a subset of that
        // namespace and must not shadow the parent (Illuminate\).
        $matches = [];
        foreach ($this->prefixesPsr4 as $prefix => $paths) {
            if ($prefix !== '' && str_starts_with($logical, $prefix)) {
                $matches[$prefix] = $paths;
            }
        }

        uksort($matches, fn ($a, $b) => strlen($b) <=> strlen($a));

        foreach ($matches as $prefix => $paths) {
            $relative = str_replace('\\', '/', substr($logical, strlen($prefix))).'.php';

            foreach ($paths as $path) {
                $file = rtrim($path, '/').'/'.$relative;

                if (is_file($file)) {
                    return $file;
                }
            }
        }

        return false;
    }

    public function loadClass(string $class): bool
    {
        if ($file = $this->findFile($class)) {
            require $file;

            return true;
        }

        return false;
    }
}
