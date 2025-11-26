variable "aws_region" {
  description = "The AWS region to deploy resources"
  type        = string
  default     = "us-east-1"
}

variable "ec2_ami" {
  description = "Ubuntu 24.04 LTS ARM64"
  type        = string
  default     = "ami-07041441b708acbd6"
}

variable "instance_type" {
  description = "EC2 instance type"
  type        = string
  default     = "t4g.micro"
}

variable "ec2_eip" {
  description = "EC2 Elastic IP address"
  type        = string
  default     = "174.129.195.18"
}

variable "ec2_eip_allocation" {
  description = "Elastic IP address allocation to EC2 instance"
  type        = string
  default     = "eipalloc-02b18da0adb5c6f37"
}

variable "volume_size" {
  description = "Size of the root volume in GB"
  type        = number
  default     = 30
}

variable "project_name" {
  description = "Name of the project for resource tagging"
  type        = string
  default     = "SIPROQUIM"
}

variable "environment" {
  description = "Deployment environment (e.g., production, staging, development)"
  type        = string
  default     = "production"
}

variable "common_tags" {
  description = "Common tags to be applied to all resources"
  type        = map(string)
  default = {
    project = "SIPROQUIM"
    env     = "production"
    owner   = "Gilmar Trindade"
    po      = "Gutemberg Medeiros"
  }
}

