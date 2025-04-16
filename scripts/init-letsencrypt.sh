#!/bin/bash

# Script to initialize Let's Encrypt certificates for SIPROQUIM
# This script should be run before starting the Docker containers for the first time

# Define the domain name
domains=(siproquim.gmedeiros.net)
rsa_key_size=4096
data_path="./certbot"
email="security@gmedeiros.net"  # Using the email from SECURITY.md
staging=1

# Check if root directories exist, create them if not
mkdir -p "$data_path/conf"
mkdir -p "$data_path/www"

# Check if we're running in staging or production mode
if [ $staging != "0" ]; then
  staging_arg="--staging"
else
  staging_arg=""
fi

echo "### Creating dummy certificate for $domains ..."
path="/etc/letsencrypt/live/$domains"
mkdir -p "$data_path/conf/live/$domains"

# Create a temporary self-signed certificate for initial setup
docker-compose run --rm --entrypoint "\
  openssl req -x509 -nodes -newkey rsa:$rsa_key_size -days 1\
    -keyout '/etc/letsencrypt/live/$domains/privkey.pem' \
    -out '/etc/letsencrypt/live/$domains/fullchain.pem' \
    -subj '/CN=localhost'" certbot || {
  echo "Failed to create dummy certificate. Exiting."
  exit 1
}

echo "### Starting nginx ..."
docker-compose up --force-recreate -d nginx || {
  echo "Failed to start Nginx container. Exiting."
  exit 1
}

echo "### Deleting dummy certificate for $domains ..."
docker-compose run --rm --entrypoint "\
  rm -Rf /etc/letsencrypt/live/$domains && \
  rm -Rf /etc/letsencrypt/archive/$domains && \
  rm -Rf /etc/letsencrypt/renewal/$domains.conf" certbot || {
  echo "Warning: Failed to delete dummy certificate. Continuing anyway."
}

echo "### Requesting Let's Encrypt certificate for $domains ..."
# Join domains to -d args
domain_args=""
for domain in "${domains[@]}"; do
  domain_args="$domain_args -d $domain"
done

# Select appropriate email arg
case "$email" in
  "") email_arg="--register-unsafely-without-email" ;;
  *) email_arg="--email $email" ;;
esac

# Enable staging mode if needed
docker-compose run --rm --entrypoint "\
  certbot certonly --webroot -w /var/www/certbot \
    $staging_arg \
    $email_arg \
    $domain_args \
    --rsa-key-size $rsa_key_size \
    --agree-tos \
    --force-renewal" certbot || {
  echo "Failed to obtain Let's Encrypt certificate. Using self-signed certificates for now."
  echo "Make sure your domain is pointed to this server and ports 80/443 are accessible."
  echo "You can run this script again later to retry."
}

echo "### Checking for certificates..."
docker-compose run --rm --entrypoint "\
  [ -d /etc/letsencrypt/live/$domains ] && echo 'Certificates exist. Updating Nginx configuration.' || echo 'No certificates found. Keeping self-signed configuration.'" certbot

echo "### Reloading nginx ..."
docker-compose exec nginx nginx -s reload || {
  echo "Failed to reload Nginx. Please restart the container manually."
}

echo "### Instructions for switching to Let's Encrypt certificates:"
echo "1. If certificates were successfully obtained, edit nginx/default.conf"
echo "2. Comment out the self-signed certificate lines"
echo "3. Uncomment the Let's Encrypt certificate lines"
echo "4. Run 'docker-compose restart nginx' to apply changes"