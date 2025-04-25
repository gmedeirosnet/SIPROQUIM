.git
.gitignore
*.log
secrets/
config/local_secrets.php
.env
terraform/ # If not needed in the image
*.tfstate* # Definitely exclude terraform state
*.tfvars # Exclude terraform variables
# Add any other files/directories containing sensitive info