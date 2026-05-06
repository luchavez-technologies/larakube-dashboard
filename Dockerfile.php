############################################
# Base Image
############################################

# Learn more about the Server Side Up PHP Docker Images at:
# https://serversideup.net/open-source/docker-php/
FROM serversideup/php:8.5-fpm-nginx-alpine AS base

USER root
RUN install-php-extensions intl
USER www-data

############################################
# PHP Dependencies
############################################
FROM base AS vendor

# Switch to root to install dependencies
USER root

# Copy composer from official image
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --no-interaction \
    --no-plugins \
    --no-scripts \
    --prefer-dist

############################################
# Frontend Assets
############################################
FROM node:22-alpine AS assets
WORKDIR /app
COPY package*.json ./
RUN npm ci
COPY . .
RUN npm run build

############################################
# Development Image
############################################
FROM base AS development

# We can pass USER_ID and GROUP_ID as build arguments
# to ensure the www-data user has the same UID and GID
# as the user running Docker.
ARG USER_ID
ARG GROUP_ID

# Switch to root so we can set the user ID and group ID
USER root

# Set the user ID and group ID for www-data
# Also install node/chokidar for Octane watch support
RUN apk add --no-cache nodejs npm && \
    npm install -g chokidar && \
    docker-php-serversideup-set-id www-data $USER_ID:$GROUP_ID  && \
    docker-php-serversideup-set-file-permissions --owner $USER_ID:$GROUP_ID

# Drop privileges back to www-data
USER www-data

############################################
# Production Image
############################################
FROM base AS deploy

# Switch to root to fix permissions
USER root

# Copy application files
COPY --chown=www-data:www-data . /var/www/html

# Copy pre-built dependencies and assets from previous stages
COPY --from=vendor --chown=www-data:www-data /app/vendor /var/www/html/vendor
COPY --from=assets --chown=www-data:www-data /app/public/build /var/www/html/public/build

# Final system preparation
RUN rm -rf /var/www/html/node_modules && \
    mkdir -p storage bootstrap/cache && \
    mkdir -p .infrastructure/volume_data/sqlite && \
    chown -R www-data:www-data storage bootstrap/cache .infrastructure/volume_data/sqlite && \
    chmod -R 775 storage bootstrap/cache

# Drop privileges back to www-data
USER www-data
