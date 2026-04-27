<?php

declare(strict_types=1);

namespace SleepyOwl;

use SleepyOwl\Base\IHookRegister;
use SleepyOwl\Base\Singleton;

class Plugin extends Singleton
{
    /**
     * @var array<class-string<IHookRegister&Singleton>>
     */
    private array $registerClasses = [
        Assets::class,
    ];


    protected function __construct()
    {
        parent::__construct();
        $this->registerHooks();
    }

    public function registerHooks(): void
    {
        foreach ($this->registerClasses as $registerClass) {
            $instance = $registerClass::getInstance();
            $instance->registerHooks();
        }
    }
}