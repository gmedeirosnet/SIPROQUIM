# VPC for the Siproquim application
resource "aws_vpc" "siproquim_vpc" {
  cidr_block           = "10.0.0.0/16"
  enable_dns_hostnames = true
  enable_dns_support   = true

  tags = {
    Name        = "${lower(var.project_name)}-vpc"
    Environment = var.environment
    Project     = var.project_name
  }
}

# Public subnet in AZ a (for public-facing components)
resource "aws_subnet" "public_subnet_a" {
  vpc_id                  = aws_vpc.siproquim_vpc.id
  cidr_block              = "10.0.1.0/24"
  availability_zone       = "${var.aws_region}a"
  map_public_ip_on_launch = true

  tags = {
    Name        = "${lower(var.project_name)}-public-subnet-a"
    Environment = var.environment
    Project     = var.project_name
  }
}

# Public subnet in AZ b (for redundancy)
resource "aws_subnet" "public_subnet_b" {
  vpc_id                  = aws_vpc.siproquim_vpc.id
  cidr_block              = "10.0.2.0/24"
  availability_zone       = "${var.aws_region}b"
  map_public_ip_on_launch = true

  tags = {
    Name        = "${lower(var.project_name)}-public-subnet-b"
    Environment = var.environment
    Project     = var.project_name
  }
}

# Private subnet in AZ a (for database and internal components)
resource "aws_subnet" "private_subnet_a" {
  vpc_id                  = aws_vpc.siproquim_vpc.id
  cidr_block              = "10.0.3.0/24"
  availability_zone       = "${var.aws_region}a"
  map_public_ip_on_launch = false

  tags = {
    Name        = "${lower(var.project_name)}-private-subnet-a"
    Environment = var.environment
    Project     = var.project_name
  }
}

# Private subnet in AZ b (for redundancy)
resource "aws_subnet" "private_subnet_b" {
  vpc_id                  = aws_vpc.siproquim_vpc.id
  cidr_block              = "10.0.4.0/24"
  availability_zone       = "${var.aws_region}b"
  map_public_ip_on_launch = false

  tags = {
    Name        = "${lower(var.project_name)}-private-subnet-b"
    Environment = var.environment
    Project     = var.project_name
  }
}

# Internet Gateway to allow VPC to communicate with the internet
resource "aws_internet_gateway" "siproquim_igw" {
  vpc_id = aws_vpc.siproquim_vpc.id

  tags = {
    Name        = "${lower(var.project_name)}-igw"
    Environment = var.environment
    Project     = var.project_name
  }
}

# Elastic IP for NAT Gateway
resource "aws_eip" "nat_eip" {
  domain = "vpc"

  tags = {
    Name        = "${lower(var.project_name)}-nat-eip"
    Environment = var.environment
    Project     = var.project_name
  }

  depends_on = [aws_internet_gateway.siproquim_igw]
}

# NAT Gateway for private subnets to access internet
resource "aws_nat_gateway" "siproquim_nat_gateway" {
  allocation_id = aws_eip.nat_eip.id
  subnet_id     = aws_subnet.public_subnet_a.id

  tags = {
    Name        = "${lower(var.project_name)}-nat-gateway"
    Environment = var.environment
    Project     = var.project_name
  }

  depends_on = [aws_internet_gateway.siproquim_igw]
}

# Route table for public subnets
resource "aws_route_table" "public_route_table" {
  vpc_id = aws_vpc.siproquim_vpc.id

  route {
    cidr_block = "0.0.0.0/0"
    gateway_id = aws_internet_gateway.siproquim_igw.id
  }

  tags = {
    Name        = "${lower(var.project_name)}-public-route-table"
    Environment = var.environment
    Project     = var.project_name
  }
}

# Route table for private subnets
resource "aws_route_table" "private_route_table" {
  vpc_id = aws_vpc.siproquim_vpc.id

  route {
    cidr_block     = "0.0.0.0/0"
    nat_gateway_id = aws_nat_gateway.siproquim_nat_gateway.id
  }

  tags = {
    Name        = "${lower(var.project_name)}-private-route-table"
    Environment = var.environment
    Project     = var.project_name
  }
}

# Associate public subnet a with public route table
resource "aws_route_table_association" "public_subnet_a_association" {
  subnet_id      = aws_subnet.public_subnet_a.id
  route_table_id = aws_route_table.public_route_table.id
}

# Associate public subnet b with public route table
resource "aws_route_table_association" "public_subnet_b_association" {
  subnet_id      = aws_subnet.public_subnet_b.id
  route_table_id = aws_route_table.public_route_table.id
}

# Associate private subnet a with private route table
resource "aws_route_table_association" "private_subnet_a_association" {
  subnet_id      = aws_subnet.private_subnet_a.id
  route_table_id = aws_route_table.private_route_table.id
}

# Associate private subnet b with private route table
resource "aws_route_table_association" "private_subnet_b_association" {
  subnet_id      = aws_subnet.private_subnet_b.id
  route_table_id = aws_route_table.private_route_table.id
}