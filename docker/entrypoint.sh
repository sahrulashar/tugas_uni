#!/bin/bash
set -e

# Pastikan folder writable dan sub-direktori ada dengan permission yang benar
mkdir -p /var/www/html/writable/cache \
         /var/www/html/writable/logs \
         /var/www/html/writable/session \
         /var/www/html/writable/uploads \
         /var/www/html/writable/debugbar

chown -R www-data:www-data /var/www/html/writable
chmod -R 777 /var/www/html/writable

# Install dependency composer jika folder vendor belum ada (misal saat bind mount pertama kali)
if [ ! -f "/var/www/html/vendor/autoload.php" ]; then
    echo "=========================================================="
    echo "Composer vendor tidak ditemukan. Menjalankan composer install..."
    echo "=========================================================="
    composer install --no-interaction --prefer-dist --optimize-autoloader
fi

echo "=========================================================="
echo "Sistem ERP CI4 Container siap dijalankan!"
echo "=========================================================="

exec "$@"
