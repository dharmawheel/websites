# IAM Instance Profile for Packer Builder
resource "aws_iam_role" "packer_builder" {
  name = "PackerBuilderRole"

  assume_role_policy = jsonencode({
    Version = "2012-10-17"
    Statement = [
      {
        Action = "sts:AssumeRole"
        Effect = "Allow"
        Principal = {
          Service = "ec2.amazonaws.com"
        }
      }
    ]
  })
}

resource "aws_iam_instance_profile" "packer_builder" {
  name = "PackerBuilderProfile"
  role = aws_iam_role.packer_builder.name
}

resource "aws_iam_role_policy" "packer_builder" {
  name = "PackerBuilderPolicy"
  role = aws_iam_role.packer_builder.id

  policy = jsonencode({
    Version = "2012-10-17"
    Statement = [
      {
        Effect = "Allow"
        Action = [
          "ssm:UpdateInstanceInformation",
          "ssmmessages:CreateControlChannel",
          "ssmmessages:CreateDataChannel",
          "ssmmessages:OpenControlChannel",
          "ssmmessages:OpenDataChannel"
        ]
        Resource = "*"
      }
    ]
  })
}
