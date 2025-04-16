#!/bin/bash

# Script to toggle between self-signed and Let's Encrypt SSL certificates
# Usage: ./toggle_ssl_config.sh [letsencrypt|self-signed]

DOMAIN="siproquim.gmedeiros.net"
NGINX_CONFIG="/Users/gutembergmedeiros/Work/SIPROQUIM/nginx/default.conf"
CONFIG_TYPE=$1

# Default to self-signed if no argument is provided
if [ -z "$CONFIG_TYPE" ]; then
    echo "Usage: ./toggle_ssl_config.sh [letsencrypt|self-signed]"
    echo "Specify which certificate type to use."
    exit 1
fi

backup_config() {
    echo "Creating backup of current Nginx configuration..."
    cp "$NGINX_CONFIG" "${NGINX_CONFIG}.bak"
    echo "Backup created at ${NGINX_CONFIG}.bak"
}

use_self_signed() {
    echo "Configuring Nginx to use self-signed certificates..."
    sed -i.tmp '/# SSL Certificate Configuration/,/# SSL Settings/ s|# ssl_certificate /etc/nginx/ssl/nginx.crt;|ssl_certificate /etc/nginx/ssl/nginx.crt;|' "$NGINX_CONFIG"
    sed -i.tmp '/# SSL Certificate Configuration/,/# SSL Settings/ s|# ssl_certificate_key /etc/nginx/ssl/nginx.key;|ssl_certificate_key /etc/nginx/ssl/nginx.key;|' "$NGINX_CONFIG"

    sed -i.tmp '/# SSL Certificate Configuration/,/# SSL Settings/ s|ssl_certificate /etc/letsencrypt/live/'"$DOMAIN"'/fullchain.pem;|# ssl_certificate /etc/letsencrypt/live/'"$DOMAIN"'/fullchain.pem;|' "$NGINX_CONFIG"
    sed -i.tmp '/# SSL Certificate Configuration/,/# SSL Settings/ s|ssl_certificate_key /etc/letsencrypt/live/'"$DOMAIN"'/privkey.pem;|# ssl_certificate_key /etc/letsencrypt/live/'"$DOMAIN"'/privkey.pem;|' "$NGINX_CONFIG"
    sed -i.tmp '/# SSL Certificate Configuration/,/# SSL Settings/ s|ssl_trusted_certificate /etc/letsencrypt/live/'"$DOMAIN"'/chain.pem;|# ssl_trusted_certificate /etc/letsencrypt/live/'"$DOMAIN"'/chain.pem;|' "$NGINX_CONFIG"

    # Disable OCSP stapling
    sed -i.tmp 's/ssl_stapling on;/# ssl_stapling on;/' "$NGINX_CONFIG"
    sed -i.tmp 's/ssl_stapling_verify on;/# ssl_stapling_verify on;/' "$NGINX_CONFIG"

    rm -f "${NGINX_CONFIG}.tmp"
    echo "Nginx configured to use self-signed certificates"
}

use_letsencrypt() {
    echo "Checking if Let's Encrypt certificates exist..."

    if docker-compose run --rm --entrypoint "ls -l /etc/letsencrypt/live/$DOMAIN/fullchain.pem" certbot > /dev/null 2>&1; then
        echo "Let's Encrypt certificates found. Configuring Nginx..."

        sed -i.tmp '/# SSL Certificate Configuration/,/# SSL Settings/ s|ssl_certificate /etc/nginx/ssl/nginx.crt;|# ssl_certificate /etc/nginx/ssl/nginx.crt;|' "$NGINX_CONFIG"
        sed -i.tmp '/# SSL Certificate Configuration/,/# SSL Settings/ s|ssl_certificate_key /etc/nginx/ssl/nginx.key;|# ssl_certificate_key /etc/nginx/ssl/nginx.key;|' "$NGINX_CONFIG"

        sed -i.tmp '/# SSL Certificate Configuration/,/# SSL Settings/ s|# ssl_certificate /etc/letsencrypt/live/'"$DOMAIN"'/fullchain.pem;|ssl_certificate /etc/letsencrypt/live/'"$DOMAIN"'/fullchain.pem;|' "$NGINX_CONFIG"
        sed -i.tmp '/# SSL Certificate Configuration/,/# SSL Settings/ s|# ssl_certificate_key /etc/letsencrypt/live/'"$DOMAIN"'/privkey.pem;|ssl_certificate_key /etc/letsencrypt/live/'"$DOMAIN"'/privkey.pem;|' "$NGINX_CONFIG"
        sed -i.tmp '/# SSL Certificate Configuration/,/# SSL Settings/ s|# ssl_trusted_certificate /etc/letsencrypt/live/'"$DOMAIN"'/chain.pem;|ssl_trusted_certificate /etc/letsencrypt/live/'"$DOMAIN"'/chain.pem;|' "$NGINX_CONFIG"

        # Enable OCSP stapling
        sed -i.tmp 's/# ssl_stapling on;/ssl_stapling on;/' "$NGINX_CONFIG"
        sed -i.tmp 's/# ssl_stapling_verify on;/ssl_stapling_verify on;/' "$NGINX_CONFIG"

        rm -f "${NGINX_CONFIG}.tmp"
        echo "Nginx configured to use Let's Encrypt certificates"
    else
        echo "Error: Let's Encrypt certificates not found!"
        echo "Run the init-letsencrypt.sh script on your production server first."
        exit 1
    fi
}

# Backup current config
backup_config

# Toggle based on parameter
case "$CONFIG_TYPE" in
    letsencrypt)
        use_letsencrypt
        ;;
    self-signed)
        use_self_signed
        ;;
    *)
        echo "Error: Invalid parameter. Use 'letsencrypt' or 'self-signed'"
        exit 1
        ;;
esac

echo "Restarting Nginx to apply changes..."
docker-compose restart nginx

echo "Configuration complete!"