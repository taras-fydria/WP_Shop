<?php
/**
 * Plugin Name: Sleepy Owl Shop
 * Version:     1.0.0
 */

declare(strict_types=1);

use SleepyOwl\Base\Autoloader;
use SleepyOwl\Plugin;

define('SLEEPY_OWL_SHOP_PLUGIN_URL', plugin_dir_url(__FILE__));

require_once __DIR__ . '/app/Base/Autoloader.php';

( new Autoloader('SleepyOwl', __DIR__ . '/app') )->register();

Plugin::getInstance();