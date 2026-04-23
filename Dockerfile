# ── Stage 1: build webpack assets ─────────────────────────────────────────────
FROM node:20-alpine AS assets
WORKDIR /app
COPY plugin/package*.json ./
RUN npm ci
COPY plugin/webpack.config.js ./
COPY plugin/src/ ./src/
RUN npm run build

# ── Stage 2: WordPress + WP-CLI ───────────────────────────────────────────────
FROM wordpress:php8.2-fpm

ARG PLUGIN_SLUG=sleepy-owl-shop
ENV PLUGIN_SLUG=${PLUGIN_SLUG}

RUN apt-get update \
    && apt-get install -y --no-install-recommends netcat-openbsd \
    && rm -rf /var/lib/apt/lists/* \
    && curl -sS -o /usr/local/bin/wp \
       https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar \
    && chmod +x /usr/local/bin/wp

# Plugin files for production (no bind-mount in prod)
COPY plugin/ /docker/plugin-files/
COPY --from=assets /app/dist /docker/plugin-files/dist

COPY docker/setup.sh /usr/local/bin/setup.sh
RUN chmod +x /usr/local/bin/setup.sh