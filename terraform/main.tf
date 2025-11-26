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

  tags = merge(var.common_tags, {
    Name = "${lower(var.project_name)}-security-group"
  })
}

# AWS key pair for SSH access
resource "aws_key_pair" "gmedeiros_key" {
  key_name   = "aws-gmedeiros-key"
  public_key = "ssh-rsa AAAAB3NzaC1yc2EAAAADAQABAAACAQDADbJOQmChqxmmgCTQF2v8gUGhiINSS0n16/3N5vUSvk3/NJ8E5oqa+eZCwzN1j7JV1VHZwFuyKRBaXEu9e08Dm6a9CAALDrEl/q0HscD7UcnfIA4py4LWx1Km27vJMyJm2BKNpVd7BKuxNUvw7TQLQlhWiZ58V7tlvDSCU3jpZ+B5UNR8UtoiYLB0picyWWvJOK32dZqOLDNntIjbMOJLRbJy6DsIu0Ti43ltk/CPqfqbvEOXCQY6InRN4lDwEEhGsaXXyKw16GkDfSrqmcOymTW2wlJjOHRJtCwNBxW0Sr7aVzzGJ7sd67txrOArXInwzbyis+jg8R3LjdTI6lN7UntZ4pFELHv/MfXcT/ArNVCwkqc603L4ba6Azor5xzORLYH2CQ0x6X0fmRAzC8D7heftSh/Cvh83DlIQTu1k1t2ahTQ7x9zF1xgFq++U0luUMhilNCAcYSXj4x/vZIrX8kFmGrpsvSIA7FHKD+5PbBVi1i5uLU66G8QEvvOwPd/cjtKuTFC2M7JvdI6urPR5A6rLlUkfbRPtUqPpVqkOzdp8b8AxSBRK3RC/bGmdMlMGWKiSNd193P4iwQGwM15gFzA5ioOapmxPOc++3f+J3GopuTA43C9yIbMY88fJefPw6ljuEHZobL2EffUlq8DcRz8VCTRQSkgjz5Y/1uSyxQ== aws@gmedeiros.net"
}

# EC2 instance
resource "aws_instance" "siproquim_server" {
  ami                         = var.ec2_ami
  instance_type               = var.instance_type
  subnet_id                   = aws_subnet.public_subnet_a.id
  vpc_security_group_ids      = [aws_security_group.siproquim_sg.id]
  key_name                    = aws_key_pair.gmedeiros_key.key_name
  associate_public_ip_address = true

  # Root volume
  root_block_device {
    volume_size           = var.volume_size
    volume_type           = "gp3"
    delete_on_termination = true
    tags = merge(var.common_tags, {
      Name = "${lower(var.project_name)}-root-volume"
    })
  }

  provisioner "file" {
    source      = "../scripts/docker.sh"
    destination = "/tmp/docker.sh"
  }

  provisioner "file" {
    source      = "../scripts/psql_client.sh"
    destination = "/tmp/psql_client.sh"
  }

  provisioner "remote-exec" {
    inline = [
      "chmod +x /tmp/docker.sh",
      "chmod +x /tmp/psql_client.sh",
      "/tmp/docker.sh",
      "/tmp/psql_client.sh"
    ]
  }

  connection {
    type        = "ssh"
    user        = "ubuntu"
    private_key = file("~/.ssh/siproquim")
    host        = self.public_ip
  }

  tags = merge(var.common_tags, {
    Name = "${lower(var.project_name)}-server"
  })

  user_data = <<-EOF
    #!/bin/bash
    apt update
    apt upgrade -y
    apt install vim curl zsh git -y
  EOF

}

# EIP Association using existing Elastic IP
resource "aws_eip_association" "siproquim_eip_assoc" {
  instance_id   = aws_instance.siproquim_server.id
  allocation_id = var.ec2_eip_allocation
}
