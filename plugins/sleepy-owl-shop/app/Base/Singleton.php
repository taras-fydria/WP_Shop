<?php

declare(strict_types=1);

namespace SleepyOwl\Base;

abstract class Singleton
{
    /** @var array<class-string, static> */
    private static array $instances = [];

    protected function __construct()
    {
    }

    public static function getInstance(): static
    {
        $class = static::class;

        if (!isset(self::$instances[$class])) {
            self::$instances[$class] = new static();
        }

        return self::$instances[$class];
    }

    private function __clone()
    {
    }

    public function __wakeup(): void
    {
        throw new \RuntimeException('Cannot unserialize a singleton.');
    }
}
