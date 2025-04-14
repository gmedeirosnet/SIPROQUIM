#!/bin/bash

set -e

# Variables
SSH_DIR="$HOME/.ssh"
KEY_FILE="$SSH_DIR/siproquim"
SSH_REPO_URL="git@github.com:gmedeirosnet/SIPROQUIM.git"
CLONE_DIR="$HOME/repo"

# Create SSH directory if it doesn't exist
mkdir -p "$SSH_DIR"
chmod 700 "$SSH_DIR"

# Generate SSH key if it doesn't exist
if [ ! -f "$KEY_FILE" ]; then
    ssh-keygen -t rsa -b 4096 -C "ec2-github-access" -f "$KEY_FILE" -N ""
    echo "SSH key generated. Please add this public key to your GitHub account:"
    cat "$KEY_FILE.pub"
    echo ""
    echo "After adding the key to GitHub, run this script again."
    exit 0
fi

# Create SSH config entry for GitHub
cat <<EOF > "$SSH_DIR/config"
Host github.com
  HostName github.com
  User git
  IdentityFile $KEY_FILE
  IdentitiesOnly yes
EOF

chmod 600 "$SSH_DIR/config"

# Trust GitHub (prevent host authenticity prompt)
ssh-keyscan -t rsa github.com > "$SSH_DIR/known_hosts" 2>/dev/null
chmod 600 "$SSH_DIR/known_hosts"

# Test SSH connection to GitHub
echo "Testing SSH connection to GitHub..."
ssh -T -o StrictHostKeyChecking=no git@github.com || true

# Clone the private repo
if [ ! -d "$CLONE_DIR" ]; then
    echo "Cloning repository from GitHub..."
    GIT_SSH_COMMAND="ssh -i $KEY_FILE -o StrictHostKeyChecking=no" git clone "$SSH_REPO_URL" "$CLONE_DIR"
    echo "Repository cloned successfully to $CLONE_DIR"
else
    echo "Repository already exists at $CLONE_DIR"
    cd "$CLONE_DIR"
    echo "Pulling latest changes..."
    GIT_SSH_COMMAND="ssh -i $KEY_FILE -o StrictHostKeyChecking=no" git pull
fi
