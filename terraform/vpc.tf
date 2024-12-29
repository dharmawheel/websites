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
