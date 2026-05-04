<?php

declare(strict_types=1);

define('WP_ROOT', getenv('WP_ROOT') ?: '/var/www/html');

require_once WP_ROOT . '/wp-load.php';
require_once dirname(__DIR__) . '/vendor/autoload.php';