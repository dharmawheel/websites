terraform {
  backend "s3" {
    bucket         = "dhamma-tfstate"
    region         = "us-west-1"
    key            = "ami-builder/terraform.tfstate"
    dynamodb_table = "dhamma-terraform-lock-table"
    encrypt        = true
  }
}
