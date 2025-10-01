resource "aws_iam_role" "github_actions_role" {
  name               = "github-actions-role"
  assume_role_policy = <<EOF
{
  "Version": "2012-10-17",
  "Statement": [
    {
      "Effect": "Allow",
      "Principal": {
        "Service": "sts.amazonaws.com"
      },
      "Action": "sts:AssumeRole"
    }
  ]
}
EOF
}

resource "aws_iam_policy" "github_actions_policy" {
  name        = "github-actions-policy"
  description = "Policy for GitHub Actions CI/CD tasks"
  policy      = <<EOF
{
  "Version": "2012-10-17",
  "Statement": [
    {
      "Effect": "Allow",
      "Action": [
        "ssm:SendCommand",
        "ssm:ListCommandInvocations",
        "ssm:GetCommandInvocation",
        "ssm:DescribeInstanceInformation",
        "ssm:StartSession",
        "ssm:TerminateSession",
        "ssm:DescribeSessions",
        "ssm:ListCommands",
        "ssm:DescribeInstanceAssociationsStatus",
        "ssm:ListAssociations",
        "ssm:ListInstanceAssociations",
        "ssm:UpdateInstanceInformation",
        "ssm:PutParameter",
        "ssm:GetParameter",
        "ssm:GetParameters",
        "ssm:GetParameterHistory",
        "ssm:DescribeParameters"
      ],
      "Resource": "*"
    },
    {
      "Effect": "Allow",
      "Action": [
        "ec2:RunInstances",
        "ec2:TerminateInstances",
        "ec2:StopInstances",
        "ec2:StartInstances",
        "ec2:DescribeInstances",
        "ec2:DescribeVolumes",

        "ec2:CreateImage",
        "ec2:DescribeImages",
        "ec2:DescribeImageAttribute",
        "ec2:ModifyImageAttribute",
        "ec2:RegisterImage",
        "ec2:DeregisterImage",

        "ec2:CreateSnapshot",
        "ec2:DeleteSnapshot",
        "ec2:DescribeSnapshots",

        "ec2:CreateTags",
        "ec2:DeleteTags",
        "ec2:DescribeTags",

        "ec2:CreateKeyPair",
        "ec2:DeleteKeyPair",
        "ec2:DescribeKeyPairs",

        "ec2:CreateSecurityGroup",
        "ec2:AuthorizeSecurityGroupIngress",
        "ec2:AuthorizeSecurityGroupEgress",
        "ec2:RevokeSecurityGroupIngress",
        "ec2:RevokeSecurityGroupEgress",
        "ec2:DeleteSecurityGroup",
        "ec2:DescribeSecurityGroups",

        "ec2:DescribeRegions",
        "ec2:DescribeVpcs",
        "ec2:DescribeSubnets",
        "ec2:DescribeInstanceTypeOfferings",
        "ec2:DescribeAvailabilityZones",
        "ec2:DescribeVpcAttribute",
        "ec2:DescribeVpcEndpoints",

        "ec2:DescribeLaunchTemplates",
        "ec2:DescribePrefixLists",
        "ec2:DescribeNetworkInterfaces",
        "ec2:DescribeLaunchTemplateVersions",
        "ec2:CreateLaunchTemplateVersion"
      ],
      "Resource": "*"
    },
    {
      "Effect": "Allow",
      "Action": [
        "iam:PassRole",
        "iam:GetRole",
        "iam:GetInstanceProfile",
        "iam:CreateInstanceProfile",
        "iam:DeleteInstanceProfile",
        "iam:AddRoleToInstanceProfile",
        "iam:RemoveRoleFromInstanceProfile",
        "iam:ListRolePolicies",
        "iam:ListAttachedRolePolicies",
        "iam:GetUser",
        "iam:GetRolePolicy",
        "iam:ListAccessKeys",
        "iam:GetPolicy",
        "iam:ListEntitiesForPolicy",
        "iam:GetPolicyVersion",
        "iam:ListAttachedUserPolicies"
      ],
      "Resource": "*"
    },
    {
      "Effect": "Allow",
      "Action": [
        "autoscaling:StartInstanceRefresh",
        "autoscaling:DescribeAutoScalingGroups",
        "autoscaling:DescribeAutoScalingInstances",
        "autoscaling:DescribeInstanceRefreshes",
        "elasticloadbalancing:DescribeLoadBalancers",
        "elasticloadbalancing:DescribeTargetGroups",
        "elasticloadbalancing:DescribeLoadBalancerAttributes",
        "elasticloadbalancing:DescribeTargetGroupAttributes",
        "elasticloadbalancing:DescribeListeners",
        "elasticloadbalancing:DescribeRules",
        "elasticloadbalancing:DescribeTags",
        "elasticloadbalancing:DescribeListenerAttributes",
        "autoscaling:DescribePolicies"
      ],
      "Resource": "*"
    },
    {
      "Effect": "Allow",
      "Action": [
        "s3:*"
      ],
      "Resource": [
        "arn:aws:s3:::${aws_s3_bucket.tfstate_bucket.bucket}",
        "arn:aws:s3:::${aws_s3_bucket.tfstate_bucket.bucket}/*"
      ]
    },
    {
      "Effect": "Allow",
      "Action": [
        "dynamodb:PutItem",
        "dynamodb:GetItem",
        "dynamodb:DeleteItem",
        "dynamodb:UpdateItem",
        "dynamodb:DescribeTable",
        "dynamodb:DescribeContinuousBackups",
        "dynamodb:DescribeTimeToLive",
        "dynamodb:ListTagsOfResource"
      ],
      "Resource": "${aws_dynamodb_table.tfstate_lock_table.arn}"
    },
    {
      "Effect": "Allow",
      "Action": [
        "sts:GetCallerIdentity"
      ],
      "Resource": "*"
    },
    {
        "Effect": "Allow",
        "Action": [
            "elasticloadbalancing:CreateLoadBalancer",
            "elasticloadbalancing:DeleteLoadBalancer",
            "elasticloadbalancing:ModifyLoadBalancerAttributes",
            "elasticloadbalancing:CreateTargetGroup",
            "elasticloadbalancing:DeleteTargetGroup",
            "elasticloadbalancing:ModifyTargetGroup",
            "elasticloadbalancing:ModifyTargetGroupAttributes",
            "elasticloadbalancing:RegisterTargets",
            "elasticloadbalancing:DeregisterTargets",
            "elasticloadbalancing:CreateListener",
            "elasticloadbalancing:DeleteListener",
            "elasticloadbalancing:ModifyListener",
            "elasticloadbalancing:CreateRule",
            "elasticloadbalancing:DeleteRule",
            "elasticloadbalancing:ModifyRule"
        ],
        "Resource": "*"
    }
  ]
}
EOF
}

resource "aws_iam_role_policy_attachment" "attach_github_actions_policy" {
  role       = aws_iam_role.github_actions_role.name
  policy_arn = aws_iam_policy.github_actions_policy.arn
}

resource "aws_iam_user_policy_attachment" "github_actions_user_policy_attachment" {
  user       = aws_iam_user.github_actions_user.name
  policy_arn = aws_iam_policy.github_actions_policy.arn
}

resource "aws_iam_access_key" "github_actions_access_key" {
  user = aws_iam_user.github_actions_user.name
}

resource "aws_iam_user" "github_actions_user" {
  name = "github-actions-user"
}
