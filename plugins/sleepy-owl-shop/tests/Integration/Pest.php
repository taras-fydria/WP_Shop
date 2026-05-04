<?php

declare(strict_types=1);

beforeEach(function () {
    global $wpdb;
    $wpdb->query('START TRANSACTION');
});

afterEach(function () {
    global $wpdb;
    $wpdb->query('ROLLBACK');
    wp_cache_flush();
});