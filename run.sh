#!/bin/bash
cd "$(dirname "$0")"

# Set environment variables and configuration
DOMAIN="siproquim.gmedeiros.net"
EMAIL="security@gmedeiros.net"
USE_STAGING=0
USE_PRODUCTION=0
USE_SSL=1
FORCE_RENEW=0

# Process command line arguments
while [[ $# -gt 0 ]]; do
  case $1 in
    --staging)
      USE_STAGING=1
      USE_PRODUCTION=0
      shift
      ;;
    --production)
      USE_PRODUCTION=1
      USE_STAGING=0
      shift
      ;;
    --no-ssl)
      USE_SSL=0
      shift
      ;;
    --force-renew)
      FORCE_RENEW=1
      shift
      ;;
    *)
      shift
      ;;
  esac
done

# Create necessary directories for Certbot
echo "Setting up directories for Certbot..."
mkdir -p ./certbot/conf
mkdir -p ./certbot/www

echo "Stopping any running containers and Removing volumes..."
docker compose down && docker volume rm estoque_postgres_data

# Create SSL certificates directory if it doesn't exist
mkdir -p ./nginx/ssl

# Check if SSL certificates exist, create self-signed if they don't
if [ ! -f ./nginx/ssl/nginx.crt ] || [ ! -f ./nginx/ssl/nginx.key ]; then
  echo "Generating self-signed SSL certificates..."
  openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
    -keyout ./nginx/ssl/nginx.key \
    -out ./nginx/ssl/nginx.crt \
    -subj "/CN=$DOMAIN/O=SIPROQUIM/C=BR"
fi

# Handle SSL and Certbot configuration if SSL is enabled
if [ $USE_SSL -eq 1 ]; then
  if [ $USE_PRODUCTION -eq 1 ]; then
    echo "Preparing for production Let's Encrypt certificates..."
    # Make sure the init-letsencrypt script is executable
    chmod +x ./scripts/init-letsencrypt.sh

    # Update the script with the correct domain and email
    sed -i.bak "s/domains=.*/domains=($DOMAIN)/" ./scripts/init-letsencrypt.sh
    sed -i.bak "s/email=.*/email=\"$EMAIL\"/" ./scripts/init-letsencrypt.sh
    sed -i.bak "s/staging=.*/staging=0/" ./scripts/init-letsencrypt.sh

    # Run the init-letsencrypt script if certificates need to be renewed or don't exist
    if [ $FORCE_RENEW -eq 1 ] || [ ! -d "./certbot/conf/live/$DOMAIN" ]; then
      echo "Running Let's Encrypt initialization script..."
      ./scripts/init-letsencrypt.sh

      # Configure Nginx to use Let's Encrypt certificates
      echo "Configuring Nginx to use Let's Encrypt certificates..."
      chmod +x ./scripts/toggle_ssl_config.sh
      ./scripts/toggle_ssl_config.sh letsencrypt
    fi
  elif [ $USE_STAGING -eq 1 ]; then
    echo "Using Let's Encrypt staging environment for testing..."
    # Update the script to use staging
    sed -i.bak "s/staging=.*/staging=1/" ./scripts/init-letsencrypt.sh
    chmod +x ./scripts/init-letsencrypt.sh

    if [ $FORCE_RENEW -eq 1 ] || [ ! -d "./certbot/conf/live/$DOMAIN" ]; then
      echo "Running Let's Encrypt initialization script in staging mode..."
      ./scripts/init-letsencrypt.sh
    fi
  else
    echo "Using self-signed certificates..."
    chmod +x ./scripts/toggle_ssl_config.sh
    ./scripts/toggle_ssl_config.sh self-signed
  fi
fi

echo "Starting containers with new configuration..."
docker compose up -d --build

echo "Waiting for services to initialize..."
sleep 5

echo "Done! Your application is available at:"
if [ $USE_SSL -eq 1 ]; then
  echo "- Web Application: https://$DOMAIN"
else
  echo "- Web Application: http://$DOMAIN"
fi
echo "- PgAdmin: http://localhost:5050 (admin@admin.com / admin)"
echo "- Connection test: http://localhost:8080/test_connection.php"

echo ""
echo "Script execution options:"
echo "  --staging     : Use Let's Encrypt staging environment (for testing)"
echo "  --production  : Use Let's Encrypt production environment (for live deployment)"
echo "  --no-ssl      : Disable SSL configuration"
echo "  --force-renew : Force renewal of certificates"
