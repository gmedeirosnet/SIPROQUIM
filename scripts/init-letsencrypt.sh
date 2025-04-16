#!/bin/bash

# Script to initialize Let's Encrypt certificates for SIPROQUIM
# This script should be run before starting the Docker containers for the first time

# Define the domain name
domains=(siproquim.gmedeiros.net)
rsa_key_size=4096
data_path="./certbot"
email="security@gmedeiros.net"  # Using the email from SECURITY.md
staging=0 # Set to 1 if you're testing your setup to avoid hitting request limits

# Check if we're running in staging or production mode
if [ $staging != "0" ]; then
  staging_arg="--staging"
else
  staging_arg=""
fi

echo "### Creating dummy certificate for $domains ..."
mkdir -p "$data_path/conf/live/$domains"

# Create a temporary self-signed certificate for initial setup
docker-compose run --rm --entrypoint "\
  openssl req -x509 -nodes -newkey rsa:$rsa_key_size -days 1\
    -keyout '/etc/letsencrypt/live/$domains/privkey.pem' \
    -out '/etc/letsencrypt/live/$domains/fullchain.pem' \
    -subj '/CN=localhost'" certbot

echo "### Starting nginx ..."
docker-compose up --force-recreate -d nginx

echo "### Deleting dummy certificate for $domains ..."
docker-compose run --rm --entrypoint "\
  rm -Rf /etc/letsencrypt/live/$domains && \
  rm -Rf /etc/letsencrypt/archive/$domains && \
  rm -Rf /etc/letsencrypt/renewal/$domains.conf" certbot

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
    --force-renewal" certbot

echo "### Reloading nginx ..."
docker-compose exec nginx nginx -s reload