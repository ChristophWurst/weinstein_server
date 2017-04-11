#!/bin/bash

set -ev

# Generate secure key
rm bootstrap/cache/compiled.php
php artisan key:generate --no-interaction
cat .env

# Prepare DB - rollback once to catch potential migration/rollback errors
php artisan migrate --no-interaction
php artisan migrate:rollback --no-interaction
php artisan migrate --no-interaction

# Test PHP
make test

# Upload coverage results to scrutinizer
wget https://scrutinizer-ci.com/ocular.phar
php ocular.phar code-coverage:upload --format=php-clover tests/clover.xml

