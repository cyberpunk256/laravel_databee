# # syntax = docker/dockerfile:experimental

# # Default to PHP 8.2, but we attempt to match
# # the PHP version from the user (wherever `flyctl launch` is run)
# # Valid version values are PHP 7.4+
# ARG PHP_VERSION=8.2
# ARG NODE_VERSION=18
# # FROM fideloper/fly-laravel:${PHP_VERSION} as base
# FROM php:${PHP_VERSION}-fpm as base

# # PHP_VERSION needs to be repeated here
# # See https://docs.docker.com/engine/reference/builder/#understand-how-arg-and-from-interact
# ARG PHP_VERSION

# LABEL fly_launch_runtime="laravel"

# # copy application code, skipping files based on .dockerignore
# COPY . /var/www/html

# RUN apt-get update && apt-get install -y libssl-dev \
#     && docker-php-ext-install openssl \
#     && apt-get clean

# RUN COMPOSER_MEMORY_LIMIT=-1 composer install --optimize-autoloader --no-dev \
#     && mkdir -p storage/logs \
#     && php artisan optimize:clear \
#     && chown -R www-data:www-data /var/www/html \
#     && sed -i 's/protected \$proxies/protected \$proxies = "*"/g' app/Http/Middleware/TrustProxies.php \
#     && echo "MAILTO=\"\"\n* * * * * www-data /usr/bin/php /var/www/html/artisan schedule:run" > /etc/cron.d/laravel \
#     && cp .fly/entrypoint.sh /entrypoint \
#     && chmod +x /entrypoint
# # If we're using Octane...
# RUN if grep -Fq "laravel/octane" /var/www/html/composer.json; then \
#         rm -rf /etc/supervisor/conf.d/fpm.conf; \
#         if grep -Fq "spiral/roadrunner" /var/www/html/composer.json; then \
#             mv /etc/supervisor/octane-rr.conf /etc/supervisor/conf.d/octane-rr.conf; \
#             if [ -f ./vendor/bin/rr ]; then ./vendor/bin/rr get-binary; fi; \
#             rm -f .rr.yaml; \
#         else \
#             mv .fly/octane-swoole /etc/services.d/octane; \
#             mv /etc/supervisor/octane-swoole.conf /etc/supervisor/conf.d/octane-swoole.conf; \
#         fi; \
#         rm /etc/nginx/sites-enabled/default; \
#         ln -sf /etc/nginx/sites-available/default-octane /etc/nginx/sites-enabled/default; \
#     fi

# # Multi-stage build: Build static assets
# # This allows us to not include Node within the final container
# FROM node:${NODE_VERSION} as node_modules_go_brrr

# RUN mkdir /app

# RUN mkdir -p  /app
# WORKDIR /app
# COPY . .
# COPY --from=base /var/www/html/vendor /app/vendor

# # Use yarn or npm depending on what type of
# # lock file we might find. Defaults to
# # NPM if no lock file is found.
# # Note: We run "production" for Mix and "build" for Vite
# RUN if [ -f "vite.config.js" ]; then \
#         ASSET_CMD="build"; \
#     else \
#         ASSET_CMD="production"; \
#     fi; \
#     if [ -f "yarn.lock" ]; then \
#         yarn install --frozen-lockfile; \
#         yarn $ASSET_CMD; \
#     elif [ -f "pnpm-lock.yaml" ]; then \
#         corepack enable && corepack prepare pnpm@latest-7 --activate; \
#         pnpm install --frozen-lockfile; \
#         pnpm run $ASSET_CMD; \
#     elif [ -f "package-lock.json" ]; then \
#         npm ci --no-audit; \
#         npm run $ASSET_CMD; \
#     else \
#         npm install; \
#         npm run $ASSET_CMD; \
#     fi;

# # From our base container created above, we
# # create our final image, adding in static
# # assets that we generated above
# FROM base

# # Packages like Laravel Nova may have added assets to the public directory
# # or maybe some custom assets were added manually! Either way, we merge
# # in the assets we generated above rather than overwrite them
# COPY --from=node_modules_go_brrr /app/public /var/www/html/public-npm
# RUN rsync -ar /var/www/html/public-npm/ /var/www/html/public/ \
#     && rm -rf /var/www/html/public-npm \
#     && chown -R www-data:www-data /var/www/html/public

# EXPOSE 8080

# ENTRYPOINT ["/entrypoint"]
# syntax = docker/dockerfile:experimental

# ARG variables for PHP and Node.js versions
# ARG PHP_VERSION=8.2
# ARG NODE_VERSION=18

# # First stage: Build the base image from the official PHP image and install OpenSSL and other dependencies
# FROM php:${PHP_VERSION}-fpm as base

# # Install OpenSSL and other required extensions
# RUN apt-get update && apt-get install -y libssl-dev \
#     && docker-php-ext-install openssl \
#     && apt-get clean

# # Set the working directory
# WORKDIR /var/www/html

# # Copy application code, skipping files based on .dockerignore
# COPY . /var/www/html

# # Install Composer dependencies
# RUN COMPOSER_MEMORY_LIMIT=-1 composer install --optimize-autoloader --no-dev \
#     && mkdir -p storage/logs \
#     && php artisan optimize:clear \
#     && chown -R www-data:www-data /var/www/html \
#     && sed -i 's/protected \$proxies/protected \$proxies = "*"/g' app/Http/Middleware/TrustProxies.php \
#     && echo "MAILTO=\"\"\n* * * * * www-data /usr/bin/php /var/www/html/artisan schedule:run" > /etc/cron.d/laravel \
#     && cp .fly/entrypoint.sh /entrypoint \
#     && chmod +x /entrypoint

# # If using Laravel Octane, you can add those configurations here...

# # Second stage: Handle node modules and static assets with Node.js
# FROM node:${NODE_VERSION} as node_modules_go_brrr

# WORKDIR /app

# # Copy the project files and the vendor directory from the base stage
# COPY . .
# COPY --from=base /var/www/html/vendor /app/vendor

# # Install and build the frontend assets
# RUN if [ -f "vite.config.js" ]; then \
#         ASSET_CMD="build"; \
#     else \
#         ASSET_CMD="production"; \
#     fi; \
#     if [ -f "yarn.lock" ]; then \
#         yarn install --frozen-lockfile; \
#         yarn $ASSET_CMD; \
#     elif [ -f "pnpm-lock.yaml" ]; then \
#         corepack enable && corepack prepare pnpm@latest-7 --activate; \
#         pnpm install --frozen-lockfile; \
#         pnpm run $ASSET_CMD; \
#     elif [ -f "package-lock.json" ]; then \
#         npm ci --no-audit; \
#         npm run $ASSET_CMD; \
#     else \
#         npm install; \
#         npm run $ASSET_CMD; \
#     fi;

# # Final stage: Build the final image based on the base stage
# FROM base

# # Copy the public assets generated by the node_modules_go_brrr stage
# COPY --from=node_modules_go_brrr /app/public /var/www/html/public-npm

# # Sync the generated public assets with the Laravel public directory
# RUN rsync -ar /var/www/html/public-npm/ /var/www/html/public/ \
#     && rm -rf /var/www/html/public-npm \
#     && chown -R www-data:www-data /var/www/html/public

# # Expose the port for the application
# EXPOSE 8080

# # Entrypoint for the container
# ENTRYPOINT ["/entrypoint"]
# syntax = docker/dockerfile:experimental

ARG PHP_VERSION=8.2
ARG NODE_VERSION=18

# First stage: Build the base image from the official PHP image and install required extensions
FROM php:${PHP_VERSION}-fpm as base

# Install system packages and required PHP extensions
# RUN apt-get update && apt-get install -y \
#     # libssl-dev zip unzip git curl libpng-dev libjpeg-dev libfreetype6-dev \
#     # && docker-php-ext-configure gd --with-freetype --with-jpeg \
#     && docker-php-ext-install gd pdo_mysql openssl \
#     && apt-get clean \
#     && rm -rf /var/lib/apt/lists/*

# Set the working directory
WORKDIR /var/www/html

# Copy application code, skipping files based on .dockerignore
COPY . /var/www/html

# Install Composer and Laravel dependencies
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    && COMPOSER_MEMORY_LIMIT=-1 composer install --optimize-autoloader --no-dev \
    && mkdir -p storage/logs \
    && php artisan optimize:clear \
    && chown -R www-data:www-data /var/www/html \
    && sed -i 's/protected \$proxies/protected \$proxies = "*"/g' app/Http/Middleware/TrustProxies.php \
    && echo "MAILTO=\"\"\n* * * * * www-data /usr/bin/php /var/www/html/artisan schedule:run" > /etc/cron.d/laravel \
    && cp .fly/entrypoint.sh /entrypoint \
    && chmod +x /entrypoint

# Second stage: Handle node modules and static assets with Node.js
FROM node:${NODE_VERSION} as node_modules_go_brrr

WORKDIR /app

# Copy the project files and the vendor directory from the base stage
COPY . .
COPY --from=base /var/www/html/vendor /app/vendor

# Install and build the frontend assets
RUN if [ -f "vite.config.js" ]; then \
        ASSET_CMD="build"; \
    else \
        ASSET_CMD="production"; \
    fi; \
    if [ -f "yarn.lock" ]; then \
        yarn install --frozen-lockfile; \
        yarn $ASSET_CMD; \
    elif [ -f "pnpm-lock.yaml" ]; then \
        corepack enable && corepack prepare pnpm@latest-7 --activate; \
        pnpm install --frozen-lockfile; \
        pnpm run $ASSET_CMD; \
    elif [ -f "package-lock.json" ]; then \
        npm ci --no-audit; \
        npm run $ASSET_CMD; \
    else \
        npm install; \
        npm run $ASSET_CMD; \
    fi;

# Final stage: Build the final image based on the base stage
FROM base

# Copy the public assets generated by the node_modules_go_brrr stage
COPY --from=node_modules_go_brrr /app/public /var/www/html/public-npm

# Sync the generated public assets with the Laravel public directory
RUN rsync -ar /var/www/html/public-npm/ /var/www/html/public/ \
    && rm -rf /var/www/html/public-npm \
    && chown -R www-data:www-data /var/www/html/public

# Expose the port for the application
EXPOSE 8080

# Entrypoint for the container
ENTRYPOINT ["/entrypoint"]
