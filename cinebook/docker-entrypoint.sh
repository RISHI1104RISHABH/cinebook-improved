#!/bin/bash
# ─── CineBook docker entrypoint ──────────────────────────────────────────────
set -e

# Apache port — Railway injects PORT; capture it before any variable reuse
APACHE_PORT="${PORT:-8080}"

# ─── Read DB vars ─────────────────────────────────────────────────────────────
# Railway MySQL service injects MYSQLHOST, MYSQLPORT, MYSQLUSER, MYSQLPASSWORD,
# MYSQLDATABASE automatically when the service is linked. The DB_* fallbacks
# support local development; localhost:3306 is the last-resort default.
HOST="${MYSQLHOST:-${DB_HOST:-localhost}}"
DB_PORT_VAL="${MYSQLPORT:-${DB_PORT:-3306}}"
USER="${MYSQLUSER:-${DB_USER:-root}}"
PASS="${MYSQLPASSWORD:-${DB_PASS:-}}"
DB="${MYSQLDATABASE:-${DB_NAME:-movie_booking}}"

echo "🎬 CineBook starting on port $APACHE_PORT..."
echo "🗄  Database: $USER@$HOST:$DB_PORT_VAL/$DB"

# Warn loudly if we are still pointing at localhost — likely means the MySQL
# service variables were not injected, which will cause a connection failure.
if [ "$HOST" = "localhost" ] || [ "$HOST" = "127.0.0.1" ]; then
    echo "⚠️  WARNING: MYSQLHOST is not set — using localhost. If this is Railway,"
    echo "   make sure the MySQL service is linked and MYSQLHOST is injected."
fi

# ─── Wait for MySQL ───────────────────────────────────────────────────────────
echo "⏳ Waiting for MySQL at $HOST:$DB_PORT_VAL..."
for i in $(seq 1 30); do
    if mysqladmin ping -h"$HOST" -P"$DB_PORT_VAL" -u"$USER" ${PASS:+-p"$PASS"} --silent 2>/dev/null; then
        echo "✅ MySQL is ready!"
        break
    fi
    if [ "$i" -eq 30 ]; then
        echo "❌ MySQL not reachable after 60s. Continuing anyway..."
    fi
    echo "   attempt $i/30 — retrying in 2s..."
    sleep 2
done

# ─── Auto-create DB and run schema if needed ──────────────────────────────────
mysql -h"$HOST" -P"$DB_PORT_VAL" -u"$USER" ${PASS:+-p"$PASS"} -e \
    "CREATE DATABASE IF NOT EXISTS \`$DB\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" 2>/dev/null || true

TABLES=$(mysql -h"$HOST" -P"$DB_PORT_VAL" -u"$USER" ${PASS:+-p"$PASS"} "$DB" \
    -e "SHOW TABLES LIKE 'movies';" 2>/dev/null | grep -c movies || true)

if [ "$TABLES" -eq 0 ]; then
    echo "🏗  Running database schema setup..."
    mysql -h"$HOST" -P"$DB_PORT_VAL" -u"$USER" ${PASS:+-p"$PASS"} "$DB" \
        < /var/www/html/sql/database.sql
    echo "✅ Schema applied with sample data!"
else
    echo "✅ Database already set up, skipping schema."
fi

# ─── Set Apache port ──────────────────────────────────────────────────────────
echo "Listen $APACHE_PORT" > /etc/apache2/ports.conf
sed -i "s|\${PORT}|$APACHE_PORT|g" /etc/apache2/sites-enabled/000-default.conf 2>/dev/null || true
sed -i "s|<VirtualHost \*:[0-9]*>|<VirtualHost *:$APACHE_PORT>|g" \
    /etc/apache2/sites-enabled/000-default.conf 2>/dev/null || true

echo "🚀 Starting Apache..."
exec apache2-foreground
