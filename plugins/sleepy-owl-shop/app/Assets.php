<?php

declare(strict_types=1);

namespace SleepyOwl;

use SleepyOwl\Base\IHookRegister;
use SleepyOwl\Base\Singleton;

class Assets extends Singleton implements IHookRegister
{
    public function registerHooks(): void
    {
        add_action('wp_enqueue_scripts', [$this, 'enqueue']);
    }

    public function enqueue(): void
    {
        wp_enqueue_style('sleepy-owl-shop', SLEEPY_OWL_SHOP_PLUGIN_URL . 'dist/css/main.css');
        wp_enqueue_script('sleepy-owl-shop', SLEEPY_OWL_SHOP_PLUGIN_URL . 'dist/js/main.js', [], false, true);
    }
}
