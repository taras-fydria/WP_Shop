#!/bin/bash
set -eo pipefail

# ── Wait for database ──────────────────────────────────────────────────────────
DB_FULL="${WORDPRESS_DB_HOST:-db:3306}"
DB_HOST="${DB_FULL%%:*}"
DB_PORT="${DB_FULL##*:}"
[[ "$DB_PORT" == "$DB_HOST" ]] && DB_PORT=3306

echo "[setup] Waiting for database at ${DB_HOST}:${DB_PORT}..."
until nc -z "$DB_HOST" "$DB_PORT" 2>/dev/null; do sleep 2; done
echo "[setup] Database ready."

# ── Wait for WordPress files (populated by wordpress service entrypoint) ───────
echo "[setup] Waiting for WordPress files..."
until [ -f /var/www/html/wp-load.php ]; do sleep 2; done
echo "[setup] WordPress files ready."

# ── Link plugin when not bind-mounted (production) ────────────────────────────
PLUGIN_DIR="/var/www/html/wp-content/plugins/${PLUGIN_SLUG}"
if [ ! -e "${PLUGIN_DIR}" ]; then
    echo "[setup] Production mode: linking plugin files..."
    ln -s /docker/plugin-files "${PLUGIN_DIR}"
fi

# ── Install WordPress (skip if already installed) ─────────────────────────────
if ! wp core is-installed --path=/var/www/html 2>/dev/null; then
    echo "[setup] Installing WordPress..."
    wp core install \
        --path=/var/www/html \
        --url="${WP_URL}" \
        --title="${WP_TITLE}" \
        --admin_user="${WP_ADMIN_USER}" \
        --admin_password="${WP_ADMIN_PASSWORD}" \
        --admin_email="${WP_ADMIN_EMAIL}" \
        --skip-email

    echo "[setup] Activating plugin '${PLUGIN_SLUG}'..."
    wp plugin activate "${PLUGIN_SLUG}" --path=/var/www/html

    echo "[setup] Done."
else
    echo "[setup] WordPress already installed — skipping."
fi