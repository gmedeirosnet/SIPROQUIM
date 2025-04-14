terraform {
  required_providers {
    aws = {
      source  = "hashicorp/aws"
      version = "~> 5.0"
    }
  }
  required_version = ">= 1.5.0"
}

provider "aws" {
  region = var.aws_region
}

# Security group for the EC2 instance
resource "aws_security_group" "siproquim_sg" {
  name        = "${var.project_name}-security-group"
  description = "Security group for ${var.project_name} application"
  vpc_id      = aws_vpc.siproquim_vpc.id

  # SSH access
  ingress {
    from_port   = 22
    to_port     = 22
    protocol    = "tcp"
    cidr_blocks = ["0.0.0.0/0"]
    description = "SSH access"
  }

  # HTTP access
  ingress {
    from_port   = 80
    to_port     = 80
    protocol    = "tcp"
    cidr_blocks = ["0.0.0.0/0"]
    description = "HTTP access"
  }

  # HTTPS access
  ingress {
    from_port   = 443
    to_port     = 443
    protocol    = "tcp"
    cidr_blocks = ["0.0.0.0/0"]
    description = "HTTPS access"
  }

  # Outbound internet access
  egress {
    from_port   = 0
    to_port     = 0
    protocol    = "-1"
    cidr_blocks = ["0.0.0.0/0"]
    description = "Allow all outbound traffic"
  }

  tags = {
    Name        = "${lower(var.project_name)}-security-group"
    Environment = var.environment
    Project     = var.project_name
  }
}

# Get the latest Amazon Linux 2 AMI
data "aws_ami" "amazon_linux" {
  most_recent = true
  owners      = ["amazon"]

  filter {
    name   = "name"
    values = ["amzn2-ami-hvm-*-x86_64-gp2"]
  }

  filter {
    name   = "virtualization-type"
    values = ["hvm"]
  }
}

# EC2 instance
resource "aws_instance" "siproquim_server" {
  ami                    = data.aws_ami.amazon_linux.id
  instance_type          = var.instance_type
  subnet_id              = aws_subnet.public_subnet_a.id
  vpc_security_group_ids = [aws_security_group.siproquim_sg.id]

  # Root volume
  root_block_device {
    volume_size           = var.volume_size
    volume_type           = "gp3"
    delete_on_termination = true
    tags = {
      Name = "${lower(var.project_name)}-root-volume"
    }
  }

  tags = {
    Name        = "${lower(var.project_name)}-server"
    Environment = var.environment
    Project     = var.project_name
  }

  user_data = <<-EOF
    #!/bin/bash
    yum update -y
    yum install -y httpd
    yum install -y php
    amazon-linux-extras enable php8.0
    yum clean metadata
    yum install -y php-cli php-pdo php-fpm php-json php-mysqlnd php-pgsql

    # Install PostgreSQL 15
    amazon-linux-extras install -y postgresql15

    # Start and enable services
    systemctl start httpd
    systemctl enable httpd

    # Set up the web directory
    mkdir -p /var/www/html/siproquim
    echo "<html><body><h1>${var.project_name} System</h1><p>Server is running!</p></body></html>" > /var/www/html/index.html

    # Tag to indicate setup completion
    echo "Setup completed on $(date)" > /var/www/html/setup_complete.txt
  EOF

  depends_on = [aws_internet_gateway.siproquim_igw]
}