#!/bin/bash
# ─── CineBook docker entrypoint ──────────────────────────────────────────────
set +e

# Database variables
DB_HOST="${MYSQLHOST:-${DB_HOST:-localhost}}"
DB_PORT="${MYSQLPORT:-${DB_PORT:-3306}}"
DB_USER="${MYSQLUSER:-${DB_USER:-root}}"
DB_PASS="${MYSQLPASSWORD:-${DB_PASS:-}}"
DB_NAME="${MYSQLDATABASE:-${DB_NAME:-railway}}"

# App port from Railway
APP_PORT="${PORT:-8080}"

echo "🎬 CineBook starting on port $APP_PORT..."
echo "🗄 Database: $DB_USER@$DB_HOST:$DB_PORT/$DB_NAME"

# ─── Wait for MySQL ───────────────────────────────────────────────────────────
echo "⏳ Waiting for MySQL connection..."

CONNECTED=false

for i in $(seq 1 40); do
    if mysqladmin ping -h"$DB_HOST" -P"$DB_PORT" -u"$DB_USER" ${DB_PASS:+-p"$DB_PASS"} --silent >/dev/null 2>&1; then
        echo "✅ MySQL is ready!"
        CONNECTED=true
        break
    fi

    echo "⌛ Attempt $i/40 - MySQL not ready yet..."
    sleep 3
done

if [ "$CONNECTED" = true ]; then
    echo "🏗 Checking database setup..."

    mysql -h"$DB_HOST" -P"$DB_PORT" -u"$DB_USER" ${DB_PASS:+-p"$DB_PASS"} \
        -e "CREATE DATABASE IF NOT EXISTS \`$DB_NAME\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" \
        >/dev/null 2>&1

    TABLES=$(mysql -h"$DB_HOST" -P"$DB_PORT" -u"$DB_USER" ${DB_PASS:+-p"$DB_PASS"} "$DB_NAME" \
        -e "SHOW TABLES LIKE 'movies';" 2>/dev/null | grep -c movies)

    if [ "$TABLES" = "0" ]; then
        echo "📦 Importing database schema..."
        mysql -h"$DB_HOST" -P"$DB_PORT" -u"$DB_USER" ${DB_PASS:+-p"$DB_PASS"} "$DB_NAME" \
            < /var/www/html/sql/database.sql

        echo "✅ Database imported successfully!"
    else
        echo "✅ Existing database detected."
    fi
else
    echo "⚠ MySQL could not be reached. App will still start."
fi

# ─── Configure Apache Port ───────────────────────────────────────────────────
echo "Listen $APP_PORT" > /etc/apache2/ports.conf

sed -i "s|<VirtualHost \*:80>|<VirtualHost *:$APP_PORT>|g" \
    /etc/apache2/sites-enabled/000-default.conf

echo "🚀 Starting Apache on port $APP_PORT..."

exec apache2-foreground
