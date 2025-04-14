#!/bin/bash

# Exit on error, undefined variables, and propagate pipe failures
set -euo pipefail

# Function for logging
log() {
    echo "[$(date +'%Y-%m-%d %H:%M:%S')] $1"
}

log "Starting PostgreSQL client installation"

# Check if PostgreSQL client is already installed
if command -v psql &> /dev/null; then
    log "PostgreSQL client is already installed. Version: $(psql --version)"
    exit 0
fi

log "Updating package lists..."
sudo apt update || { log "Error: Failed to update package lists"; exit 1; }

log "Installing prerequisites..."
sudo apt install -y curl gnupg lsb-release || { log "Error: Failed to install prerequisites"; exit 1; }

log "Adding PostgreSQL repository key..."
curl -fsSL https://www.postgresql.org/media/keys/ACCC4CF8.asc | sudo gpg --dearmor -o /usr/share/keyrings/postgresql-archive-keyring.gpg || {
    log "Error: Failed to add PostgreSQL repository key"
    exit 1
}

log "Adding PostgreSQL repository..."
echo "deb [signed-by=/usr/share/keyrings/postgresql-archive-keyring.gpg] http://apt.postgresql.org/pub/repos/apt $(lsb_release -cs)-pgdg main" | \
sudo tee /etc/apt/sources.list.d/pgdg.list || {
    log "Error: Failed to add PostgreSQL repository"
    exit 1
}

log "Updating package lists with new repository..."
sudo apt update || { log "Error: Failed to update package lists"; exit 1; }

log "Installing PostgreSQL client..."
sudo apt install -y postgresql-client || { log "Error: Failed to install PostgreSQL client"; exit 1; }

# Verify installation
if command -v psql &> /dev/null; then
    log "PostgreSQL client successfully installed: $(psql --version)"
else
    log "Error: PostgreSQL client installation verification failed"
    exit 1
fi

log "Installation complete!"