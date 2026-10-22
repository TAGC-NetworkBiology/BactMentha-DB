# -----------------------------------------------------------------------
# Dockerfile for building image bactmentha_php for the BactMentha Project
# -----------------------------------------------------------------------

# ----------
# Base image
# ----------

FROM php:8.6.0RC4-fpm-bookworm

# Copy custom php.ini file
COPY php.ini /usr/local/etc/php/php.ini

# ----------------------------------------
# installing postgres dependencies for php
# ----------------------------------------

RUN apt-get update && apt-get install -y --no-install-recommends \
        libpq-dev \
    && docker-php-ext-install -j$(nproc) pgsql pdo_pgsql \
    && rm -rf /var/lib/apt/lists/*
