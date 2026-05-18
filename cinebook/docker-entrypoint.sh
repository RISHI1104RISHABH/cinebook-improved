#!/bin/bash
# ─── CineBook docker entrypoint ──────────────────────────────────────────────
set -e

# Read DB vars — Railway MySQL plugin or manual overrides
HOST="${MYSQLHOST:-${DB_HOST:-localhost}}"
PORT="${MYSQLPORT:-${DB_PORT:-3306}}"
USER="${MYSQLUSER:-${DB_USER:-root}}"
PASS="${MYSQLPASSWORD:-${DB_PASS:-}}"
DB="${MYSQLDATABASE:-${DB_NAME:-movie_booking}}"
SITE_PORT="${PORT:-8080}"

echo "🎬 CineBook starting on port $SITE_PORT..."
echo "🗄  Database: $USER@$HOST:$PORT/$DB"

# ─── Wait for MySQL ───────────────────────────────────────────────────────────
echo "⏳ Waiting for MySQL..."
for i in $(seq 1 30); do
    if mysqladmin ping -h"$HOST" -P"$PORT" -u"$USER" ${PASS:+-p"$PASS"} --silent 2>/dev/null; then
        echo "✅ MySQL is ready!"
        break
    fi
    if [ "$i" -eq 30 ]; then
        echo "❌ MySQL not reachable after 60s. Continuing anyway..."
    fi
    sleep 2
done

# ─── Auto-create DB and run schema if needed ──────────────────────────────────
mysql -h"$HOST" -P"$PORT" -u"$USER" ${PASS:+-p"$PASS"} -e \
    "CREATE DATABASE IF NOT EXISTS \`$DB\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" 2>/dev/null || true

TABLES=$(mysql -h"$HOST" -P"$PORT" -u"$USER" ${PASS:+-p"$PASS"} "$DB" \
    -e "SHOW TABLES LIKE 'movies';" 2>/dev/null | grep -c movies || true)

if [ "$TABLES" -eq 0 ]; then
    echo "🏗  Running database schema setup..."
    mysql -h"$HOST" -P"$PORT" -u"$USER" ${PASS:+-p"$PASS"} "$DB" \
        < /var/www/html/sql/database.sql
    echo "✅ Schema applied with sample data!"
else
    echo "✅ Database already set up, skipping schema."
fi

# ─── Set Apache port ──────────────────────────────────────────────────────────
echo "Listen $SITE_PORT" > /etc/apache2/ports.conf
sed -i "s|\${PORT}|$SITE_PORT|g" /etc/apache2/sites-enabled/000-default.conf 2>/dev/null || true
sed -i "s|<VirtualHost \*:[0-9]*>|<VirtualHost *:$SITE_PORT>|g" \
    /etc/apache2/sites-enabled/000-default.conf 2>/dev/null || true

echo "🚀 Starting Apache..."
exec apache2-foreground
