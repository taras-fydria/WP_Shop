<?php

declare(strict_types=1);

namespace SleepyOwl\Migration;

final class MigrationRunner
{
    private const VERSION_OPTION = 'sleepy_owl_shop_db_version';

    private static array $migrations = [
        1 => Migration001CreateVendorsTable::class,
    ];

    public static function run(\wpdb $wpdb): void
    {
        $current = (int) get_option(self::VERSION_OPTION, 0);

        foreach (self::$migrations as $version => $class) {
            if ($version > $current) {
                (new $class())->run($wpdb);
                update_option(self::VERSION_OPTION, $version);
            }
        }
    }
}