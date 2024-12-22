terraform {
  required_providers {
    aws = {
      source  = "hashicorp/aws"
      version = "~> 5.0"
    }
  }
}

module "cloudflare_ip_range" {
  source = "./modules/cloudflare_ip_range"
}
