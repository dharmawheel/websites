packer {
  required_plugins {
    amazon = {
      version = ">= 1.2.8"
      source  = "github.com/hashicorp/amazon"
    }
    ansible = {
      version = "~> 1"
      source  = "github.com/hashicorp/ansible"
    }
  }
}

variables {
  instance_role = "PackerBuilderProfile"
}

variable "source_ami" {
  type        = string
  description = "AMI ID generated from the base build"
}

source "amazon-ebs" "dw-mono" {
  ami_name                    = "dw-mono-${formatdate("YYYYMMDD-hhmmss", timestamp())}"
  ami_description             = "All Dhamma sites"
  instance_type               = "t4g.small"
  region                      = "us-west-1"
  associate_public_ip_address = true
  vpc_id                      = "vpc-318dd655"
  security_group_id           = "sg-0610cb20061b41b71"
  source_ami                  = var.source_ami
  tags = {
    Name = "dw-mono"
  }
  communicator         = "ssh"
  ssh_username         = "ec2-user"
  ssh_interface        = "session_manager"
  iam_instance_profile = var.instance_role
}

build {
  sources = [
    "source.amazon-ebs.dw-mono"
  ]

  provisioner "ansible" {
    playbook_file = "./playbooks/dw_mono.yml"
    host_alias    = "dw-mono"
    extra_arguments = [
      "--vault-password-file={{ pwd }}/.vault_pass",
    ]
    use_proxy = true
  }

  post-processor "manifest" {
    output     = "dw_mono_manifest.json"
    strip_path = true
  }
}
