variable "aws_region" {
  description = "The AWS region to deploy resources"
  type        = string
  default     = "us-east-1"
}

variable "instance_type" {
  description = "EC2 instance type"
  type        = string
  default     = "t2.micro"
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