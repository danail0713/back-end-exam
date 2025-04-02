#!/bin/bash
set -e  # Exit on error

echo "🚀 Starting MariaDB and MongoDB..."

# ✅ Ensure MariaDB directories exist
mkdir -p /var/run/mysqld /var/lib/mysql
chown -R mysql:mysql /var/run/mysqld /var/lib/mysql

# ✅ Initialize MariaDB if not already initialized
if [ ! -d "/var/lib/mysql/mysql" ]; then
    echo "🔄 Initializing MariaDB data directory..."
    mysqld --initialize-insecure
fi

# ✅ Start MariaDB in the background
echo "🔄 Starting MariaDB..."
mysqld_safe --datadir=/var/lib/mysql --skip-networking=0 &
sleep 5  # Wait for MariaDB to start

# ✅ Check if MariaDB started successfully
if ! pgrep mysqld > /dev/null; then
    echo "❌ ERROR: MariaDB failed to start!"
    exit 1
fi
echo "✅ MariaDB started successfully!"

# ✅ Create Drupal Database (if not exists)
mysql -uroot -e "CREATE DATABASE IF NOT EXISTS drupal;"

# ✅ Ensure MongoDB directories exist
mkdir -p /data/db /var/lib/mongodb /var/run/mongodb
chown -R mongodb:mongodb /data/db /var/lib/mongodb /var/run/mongodb

# ✅ Start MongoDB
echo "🔄 Starting MongoDB..."
nohup mongod --dbpath /data/db --bind_ip_all --logpath /var/log/mongodb.log --fork &
sleep 5

# ✅ Check if MongoDB started successfully
if ! pgrep mongod > /dev/null; then
    echo "❌ ERROR: MongoDB failed to start!"
    exit 1
fi
echo "✅ MongoDB started successfully!"

# ✅ Keep container running with PHP-FPM
exec "$@"
