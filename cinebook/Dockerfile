# ─── CineBook — Railway Dockerfile ───────────────────────────────────────────
FROM php:8.2-apache

# Install MySQLi and required tools
RUN apt-get update && apt-get install -y \
    default-mysql-client \
    && docker-php-ext-install mysqli \
    && a2enmod rewrite \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Allow .htaccess overrides
RUN sed -i 's|AllowOverride None|AllowOverride All|g' /etc/apache2/apache2.conf

# Copy app into web root
COPY . /var/www/html/

# Ensure uploads directory is writable
RUN mkdir -p /var/www/html/uploads \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && chmod -R 775 /var/www/html/uploads

# Copy and enable entrypoint
COPY docker-entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Railway sets PORT env var — Apache listens on it
RUN echo "Listen \${PORT}" > /etc/apache2/ports.conf \
    && sed -i 's|<VirtualHost \*:80>|<VirtualHost *:${PORT}>|g' \
       /etc/apache2/sites-enabled/000-default.conf

EXPOSE 8080
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
