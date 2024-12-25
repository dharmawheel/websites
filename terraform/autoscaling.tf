resource "aws_launch_template" "dw_template" {
  name = "AppServerTemplate"

  image_id = var.ami_id

  instance_type = "t4g.small"

  key_name = "DW2023"

  iam_instance_profile {
    name = aws_iam_instance_profile.ssm_instance_profile.name
  }

  capacity_reservation_specification {
    capacity_reservation_preference = "open"
  }

  disable_api_stop        = "false"
  disable_api_termination = "false"
  ebs_optimized           = "false"

  enclave_options {
    enabled = "false"
  }

  maintenance_options {
    auto_recovery = "default"
  }

  metadata_options {
    http_endpoint               = "enabled"
    http_protocol_ipv6          = "enabled"
    http_put_response_hop_limit = "2"
    http_tokens                 = "required"
    instance_metadata_tags      = "disabled"
  }

  monitoring {
    enabled = "false"
  }


  network_interfaces {
    # Only IPv6 because AWS charges more for public IPv4 addresses.
    associate_public_ip_address = "false"
    delete_on_termination       = "true"
    device_index                = "0"
    ipv4_address_count          = "0"
    ipv4_prefix_count           = "0"
    ipv6_address_count          = "1"
    ipv6_prefix_count           = "0"
    network_card_index          = "0"
    primary_ipv6                = "true"
    security_groups             = [aws_security_group.dw_forums_sg.id]
    subnet_id                   = data.aws_subnet.main.id
  }

  placement {
    partition_number = "0"
    tenancy          = "default"
  }

  private_dns_name_options {
    enable_resource_name_dns_a_record    = "true"
    enable_resource_name_dns_aaaa_record = "true"
    hostname_type                        = "ip-name"
  }

  tag_specifications {
    resource_type = "instance"

    tags = {
      Name = "AppServer"
    }
  }
}


resource "aws_autoscaling_group" "dw_asg" {
  name = "AppServerAutoscalingGroup"

  min_size              = 1
  max_size              = 15
  metrics_granularity   = "1Minute"
  max_instance_lifetime = 2592000

  availability_zone_distribution {
    capacity_distribution_strategy = "balanced-best-effort"
  }

  availability_zones        = ["us-west-1b"]
  capacity_rebalance        = false
  default_cooldown          = 300
  default_instance_warmup   = 60
  desired_capacity          = 1
  enabled_metrics           = ["GroupAndWarmPoolDesiredCapacity", "GroupAndWarmPoolTotalCapacity", "GroupDesiredCapacity", "GroupInServiceCapacity", "GroupInServiceInstances", "GroupMaxSize", "GroupMinSize", "GroupPendingCapacity", "GroupPendingInstances", "GroupStandbyCapacity", "GroupStandbyInstances", "GroupTerminatingCapacity", "GroupTerminatingInstances", "GroupTotalCapacity", "GroupTotalInstances", "WarmPoolDesiredCapacity", "WarmPoolMinSize", "WarmPoolPendingCapacity", "WarmPoolTerminatingCapacity", "WarmPoolTotalCapacity", "WarmPoolWarmedCapacity"]
  force_delete              = false
  health_check_grace_period = 120
  health_check_type         = "ELB"

  instance_maintenance_policy {
    max_healthy_percentage = "100"
    min_healthy_percentage = "90"
  }

  launch_template {
    id      = aws_launch_template.dw_template.id
    version = "$Latest"
  }

  instance_refresh {
    strategy = "Rolling"
    preferences {
      min_healthy_percentage = 90
    }
  }

  protect_from_scale_in   = "false"
  service_linked_role_arn = aws_iam_role.autoscaling_service_role.arn
  target_group_arns       = [aws_lb_target_group.dw_nlb_target_group.arn]

  wait_for_capacity_timeout = "10m"
}

resource "aws_autoscaling_policy" "cpu_policy" {
  policy_type            = "TargetTrackingScaling"
  name                   = "CPUTrackingPolicy"
  autoscaling_group_name = aws_autoscaling_group.dw_asg.name
  target_tracking_configuration {
    predefined_metric_specification {
      predefined_metric_type = "ASGAverageCPUUtilization"
    }
    target_value = 65.0
  }
}

resource "aws_iam_role" "ssm_role" {
  name = "SSMRole"

  assume_role_policy = jsonencode({
    Version = "2012-10-17",
    Statement = [
      {
        Effect = "Allow",
        Principal = {
          Service = "ec2.amazonaws.com"
        },
        Action = "sts:AssumeRole"
      }
    ]
  })
}

resource "aws_iam_policy_attachment" "ssm_policy_attachment" {
  name       = "SSMPolicyAttachment"
  policy_arn = "arn:aws:iam::aws:policy/AmazonSSMManagedInstanceCore"
  roles      = [aws_iam_role.ssm_role.name]
}

resource "aws_iam_instance_profile" "ssm_instance_profile" {
  name = "SSMInstanceProfile"
  role = aws_iam_role.ssm_role.name
}

resource "aws_iam_role" "autoscaling_service_role" {
  assume_role_policy = <<POLICY
{
  "Statement": [
    {
      "Action": "sts:AssumeRole",
      "Effect": "Allow",
      "Principal": {
        "Service": "autoscaling.amazonaws.com"
      }
    }
  ],
  "Version": "2012-10-17"
}
POLICY

  description          = "Default Service-Linked Role enables access to AWS Services and Resources used or managed by Auto Scaling"
  max_session_duration = 3600
  name                 = "AWSServiceRoleForAutoScaling"
  path                 = "/aws-service-role/autoscaling.amazonaws.com/"
}
