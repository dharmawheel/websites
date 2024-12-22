variable "aws_region" {
  type    = string
  default = "us-west-1"
}

variable "ami_id" {
  type        = string
  description = "The AMI ID built by Packer"
}
