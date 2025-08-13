output "instance_id" {
  description = "ID of the EC2 instance"
  value       = aws_instance.siproquim_server.id
}

output "instance_public_ip" {
  description = "Public IP address of the EC2 instance"
  value       = var.ec2_eip
}

output "instance_public_dns" {
  description = "Public DNS name of the EC2 instance"
  value       = aws_instance.siproquim_server.public_dns
}

output "elastic_ip" {
  description = "Elastic IP associated with the instance"
  value       = var.ec2_eip
}

output "elastic_ip_allocation_id" {
  description = "Allocation ID of the Elastic IP"
  value       = var.ec2_eip_allocation
}

output "security_group_id" {
  description = "ID of the security group"
  value       = aws_security_group.siproquim_sg.id
}

output "web_url" {
  description = "URL to access the web application"
  value       = "http://${var.ec2_eip}"
}