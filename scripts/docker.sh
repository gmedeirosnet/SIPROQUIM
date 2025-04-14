#!/bin/bash

# Docker Setup Script
# This script installs Docker and Docker Compose on Ubuntu systems
# and configures the current user to use Docker without sudo
set -euo pipefail

# Colors for output
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# Log functions
log_info() { echo -e "${BLUE}[INFO]${NC} $1"; }
log_success() { echo -e "${GREEN}[SUCCESS]${NC} $1"; }
log_warning() { echo -e "${YELLOW}[WARNING]${NC} $1"; }
log_error() { echo -e "${RED}[ERROR]${NC} $1"; }

# Remove any existing Docker sources
sudo rm -rf /etc/apt/sources.list.d/*

# Check if Docker is already installed
check_docker() {
  if command -v docker &>/dev/null && docker --version &>/dev/null; then
    log_warning "Docker is already installed: $(docker --version)"
    return 1
  fi
  return 0
}

# Install Docker prerequisites
install_prerequisites() {
  log_info "Installing Docker prerequisites..."
  sudo apt-get update
  sudo apt-get install -y ca-certificates curl gnupg
  log_success "Prerequisites installed."
}

# Add Docker repository
add_docker_repo() {
  log_info "Adding Docker repository..."

  # Create directory for Docker's GPG key if it doesn't exist
  sudo install -m 0755 -d /etc/apt/keyrings

  # Download the Docker GPG key
  if [ ! -f /etc/apt/keyrings/docker.asc ]; then
    sudo curl -fsSL https://download.docker.com/linux/ubuntu/gpg -o /etc/apt/keyrings/docker.asc
    sudo chmod a+r /etc/apt/keyrings/docker.asc
    log_success "Docker GPG key added."
  else
    log_warning "Docker GPG key already exists."
  fi

  # Add the Docker repository
  echo \
    "deb [arch=$(dpkg --print-architecture) signed-by=/etc/apt/keyrings/docker.asc] https://download.docker.com/linux/ubuntu \
    $(. /etc/os-release && echo "${UBUNTU_CODENAME:-$VERSION_CODENAME}") stable" | \
    sudo tee /etc/apt/sources.list.d/docker.list > /dev/null

  sudo apt-get update
  log_success "Docker repository added."
}

# Install Docker packages
install_docker() {
  log_info "Installing Docker packages..."
  sudo apt install -y docker-ce docker-ce-cli containerd.io docker-buildx-plugin docker-compose-plugin -y
  sudo apt install docker-compose -y
  log_success "Docker installed successfully."
}

# Configure user permissions
configure_user_permissions() {
  local current_user=ubuntu

  log_info "Configuring user permissions for $current_user..."

  # Create docker group if it doesn't exist
  if ! getent group docker &>/dev/null; then
    sudo groupadd docker
    log_success "Created Docker group."
  else
    log_warning "Docker group already exists."
  fi

  # Add the user to the docker group
  if ! groups $current_user | grep -q '\bdocker\b'; then
    sudo usermod -aG docker $current_user
    log_success "Added $current_user to the Docker group."
    log_warning "You'll need to log out and back in for the group changes to take effect."
  else
    log_warning "User $current_user is already in the Docker group."
  fi
}

# Test Docker installation
test_docker() {
  log_info "Testing Docker installation..."

  # Need to run as the intended user to test group membership
  if [ "$EUID" -eq 0 ] && [ -n "${SUDO_USER:-}" ]; then
    log_warning "Running as root, can't properly test Docker permissions for $SUDO_USER."
    log_warning "Please run 'docker run hello-world' after logging out and back in."
  else
    if docker run --rm hello-world &>/dev/null; then
      log_success "Docker is working correctly!"
    else
      log_warning "Docker test failed. You may need to log out and back in first."
    fi
  fi
}

# Main function
main() {
  log_info "Starting Docker installation process..."

  if check_docker; then
    install_prerequisites
    add_docker_repo
    install_docker
  fi

  configure_user_permissions

  log_success "Docker installation completed!"
  log_info "You can verify the installation by running: docker run hello-world"
}

# Run the main function
main "$@"
