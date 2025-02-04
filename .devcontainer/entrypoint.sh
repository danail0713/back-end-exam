#!/bin/bash
set -e

# Ensure the MariaDB service directory exists
mkdir -p /var/run/mysqld
chown -R mysql:mysql /var/run/mysqld

# Start MariaDB in the background
service mariadb start

# Wait for MariaDB to start
sleep 5

# Create the Drupal database if it doesn't exist
mysql -uroot -e "CREATE DATABASE IF NOT EXISTS drupal;"

# Continue with the default command (PHP-FPM)
exec "$@"
