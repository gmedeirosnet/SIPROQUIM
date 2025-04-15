output "instance_id" {
  description = "ID of the EC2 instance"
  value       = aws_instance.siproquim_server.id
}

output "instance_public_ip" {
  description = "Public IP address of the EC2 instance"
  value       = aws_eip.siproquim_eip.public_ip
}

output "instance_public_dns" {
  description = "Public DNS name of the EC2 instance"
  value       = aws_instance.siproquim_server.public_dns
}

output "elastic_ip" {
  description = "Elastic IP associated with the instance"
  value       = aws_eip.siproquim_eip.public_ip
}

output "elastic_ip_allocation_id" {
  description = "Allocation ID of the Elastic IP"
  value       = aws_eip.siproquim_eip.id
}

output "security_group_id" {
  description = "ID of the security group"
  value       = aws_security_group.siproquim_sg.id
}

output "web_url" {
  description = "URL to access the web application"
  value       = "http://${aws_eip.siproquim_eip.public_ip}"
}