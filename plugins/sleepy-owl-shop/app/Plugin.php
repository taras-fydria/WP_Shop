<?php

declare(strict_types=1);

namespace SleepyOwl;

use SleepyOwl\Base\IHookRegister;
use SleepyOwl\Base\Singleton;
use SleepyOwl\Migration\MigrationRunner;

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
        $this->registerActivationHook();
        $this->registerHooks();
    }

    private function registerActivationHook(): void
    {
        register_activation_hook(
            SLEEPY_OWL_SHOP_PLUGIN_FILE,
            static function (): void {
                global $wpdb;
                MigrationRunner::run($wpdb);
            }
        );
    }

    public function registerHooks(): void
    {
        foreach ($this->registerClasses as $registerClass) {
            $instance = $registerClass::getInstance();
            $instance->registerHooks();
        }
    }
}