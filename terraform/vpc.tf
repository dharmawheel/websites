data "aws_vpc" "dw_vpc" {
  id = "vpc-318dd655"
}

data "aws_region" "current" {
  name = var.aws_region
}

data "aws_subnet" "main" {
  id = "subnet-6d1fdb0a"
}

data "aws_subnet" "secondary" {
  id = "subnet-9a7560c2"
}

# This is needed to enable SSM in the EC2 instances when the EC2
# instances are in a private subnet and do not have publicly
# addressable IPv4 addresses. Instead of SSHing into the instances by
# IP, you should use SSM to connect to the instances.

# Create a security group for SSM endpoints
resource "aws_security_group" "ssm_endpoints_sg" {
  vpc_id = data.aws_vpc.dw_vpc.id

  ingress {
    from_port        = 443
    to_port          = 443
    protocol         = "tcp"
    cidr_blocks      = [data.aws_vpc.dw_vpc.cidr_block]
    ipv6_cidr_blocks = [data.aws_vpc.dw_vpc.ipv6_cidr_block]
  }

  egress {
    from_port        = 0
    to_port          = 0
    protocol         = "-1"
    cidr_blocks      = ["0.0.0.0/0"]
    ipv6_cidr_blocks = ["::/0"]
  }
}

# Create VPC Endpoint for SSM
resource "aws_vpc_endpoint" "ssm" {
  vpc_id             = data.aws_vpc.dw_vpc.id
  service_name       = "com.amazonaws.${data.aws_region.current.name}.ssm"
  vpc_endpoint_type  = "Interface"
  security_group_ids = [aws_security_group.ssm_endpoints_sg.id]
  subnet_ids         = [data.aws_subnet.main.id]

  private_dns_enabled = true
  tags = {
    Name = "SSM Endpoint"
  }
}

# Create VPC Endpoint for SSM Messages
resource "aws_vpc_endpoint" "ssmmessages" {
  vpc_id             = data.aws_vpc.dw_vpc.id
  service_name       = "com.amazonaws.${data.aws_region.current.name}.ssmmessages"
  vpc_endpoint_type  = "Interface"
  security_group_ids = [aws_security_group.ssm_endpoints_sg.id]
  subnet_ids         = [data.aws_subnet.main.id]

  private_dns_enabled = true
  tags = {
    Name = "SSM Messages Endpoint"
  }
}

# Create VPC Endpoint for EC2 Messages
resource "aws_vpc_endpoint" "ec2messages" {
  vpc_id             = data.aws_vpc.dw_vpc.id
  service_name       = "com.amazonaws.${data.aws_region.current.name}.ec2messages"
  vpc_endpoint_type  = "Interface"
  security_group_ids = [aws_security_group.ssm_endpoints_sg.id]
  subnet_ids         = [data.aws_subnet.main.id]

  private_dns_enabled = true
  tags = {
    Name = "EC2 Messages Endpoint"
  }
}
