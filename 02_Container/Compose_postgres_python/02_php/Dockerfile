# -----------------------------------------------------------------------
# Dockerfile for building image bactmentha_php for the BactMentha Project
# -----------------------------------------------------------------------

# ----------
# Base image
# ----------

FROM php:8.5.12-fpm-alpine

# Copy custom php.ini file
COPY php.ini /usr/local/etc/php/php.ini

# ----------------------------------------
# Installing PostgreSQL dependencies for PHP
# ----------------------------------------

RUN apk add --no-cache postgresql-dev \
    && docker-php-ext-install -j$(nproc) pgsql pdo_pgsql
