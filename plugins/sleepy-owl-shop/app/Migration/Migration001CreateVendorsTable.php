<?php

declare(strict_types=1);

namespace SleepyOwl\Migration;

final class Migration001CreateVendorsTable implements MigrationInterface
{
    public function run(\wpdb $wpdb): void
    {
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        $charset = $wpdb->get_charset_collate();

        dbDelta("CREATE TABLE {$wpdb->prefix}so_vendors (
            id              VARCHAR(36)  NOT NULL,
            business_name   VARCHAR(255) NOT NULL,
            status          VARCHAR(20)  NOT NULL DEFAULT 'pending',
            commission_rate DECIMAL(5,2) NOT NULL DEFAULT 0,
            created_at      DATETIME     NOT NULL,
            PRIMARY KEY (id)
        ) {$charset};");
    }
}