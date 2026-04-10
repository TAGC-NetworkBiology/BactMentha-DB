# -----------------------------------------------------------------------
# Dockerfile for building image bactmentha_php for the BactMentha Project
# -----------------------------------------------------------------------

# ----------
# Base image
# ----------

FROM php:7.4-fpm-alpine3.13

# Copy custom php.ini file
COPY php.ini /usr/local/etc/php/php.ini

# ----------------------------------------
# installing postgres dependencies for php
# ----------------------------------------

RUN apk update && apk add --no-cache \
        postgresql-dev \
    && docker-php-ext-install -j$(nproc) pgsql \
    && docker-php-ext-install -j$(nproc) pdo_pgsql
