# ── Stage 1: build webpack assets ─────────────────────────────────────────────
ARG PLUGIN_SLUG=sleepy-owl-shop

FROM node:20-alpine AS assets
ARG PLUGIN_SLUG
WORKDIR /app
COPY plugins/${PLUGIN_SLUG}/package*.json ./
RUN npm ci
COPY plugins/${PLUGIN_SLUG}/webpack.config.js ./
COPY plugins/${PLUGIN_SLUG}/src/ ./src/
RUN npm run build

# ── Stage 2: install PHP dependencies ─────────────────────────────────────────
FROM composer:2 AS deps
ARG PLUGIN_SLUG
WORKDIR /app
COPY plugins/${PLUGIN_SLUG}/composer.json plugins/${PLUGIN_SLUG}/composer.lock* ./
RUN composer install --no-dev --optimize-autoloader --no-interaction

# ── Stage 3: WordPress + WP-CLI base (used by dev) ────────────────────────────
FROM wordpress:php8.2-fpm AS base

RUN apt-get update \
    && apt-get install -y --no-install-recommends netcat-openbsd \
    && rm -rf /var/lib/apt/lists/* \
    && curl -sS -o /usr/local/bin/wp \
       https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar \
    && chmod +x /usr/local/bin/wp

COPY docker/setup.sh /usr/local/bin/setup.sh
RUN chmod +x /usr/local/bin/setup.sh

# ── Stage 4: production — bake plugin into image ──────────────────────────────
FROM base AS prod

ARG PLUGIN_SLUG
ENV PLUGIN_SLUG=${PLUGIN_SLUG}

COPY plugins/${PLUGIN_SLUG}/ /docker/plugin-files/
COPY --from=deps /app/vendor /docker/plugin-files/vendor
COPY --from=assets /app/dist /docker/plugin-files/dist