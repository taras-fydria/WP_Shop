<?php

declare(strict_types=1);

namespace SleepyOwl\Migration;

interface MigrationInterface
{
    public function run(\wpdb $wpdb): void;
}