<?php

declare(strict_types=1);

namespace SleepyOwl\Base;

class Autoloader
{
    private string $namespace;

    private string $baseDir;

    public function __construct(string $namespace, string $baseDir)
    {
        $this->namespace = rtrim($namespace, '\\') . '\\';
        $this->baseDir   = rtrim($baseDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
    }

    public function register(): void
    {
        spl_autoload_register([$this, 'load']);
    }

    public function load(string $class): void
    {
        if (strncmp($this->namespace, $class, strlen($this->namespace)) !== 0) {
            return;
        }

        $relative = substr($class, strlen($this->namespace));
        $file     = $this->baseDir . str_replace('\\', DIRECTORY_SEPARATOR, $relative) . '.php';

        if (is_file($file)) {
            require $file;
        }
    }
}
