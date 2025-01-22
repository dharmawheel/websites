resource "aws_lb" "dw_nlb" {
  load_balancer_type               = "network"
  enable_cross_zone_load_balancing = true
  idle_timeout                     = 60
  internal                         = false
  ip_address_type                  = "dualstack"

  subnets = [
    data.aws_subnet.main.id,
    data.aws_subnet.secondary.id
  ]
}

resource "aws_lb_listener" "dw_nlb_listener" {
  default_action {
    type             = "forward"
    target_group_arn = aws_lb_target_group.dw_nlb_target_group.arn
  }

  load_balancer_arn = aws_lb.dw_nlb.arn
  port              = "443" # Forwarding encrypted traffic to instances
  protocol          = "TCP"
}

resource "aws_lb_target_group" "dw_nlb_target_group" {
  target_type          = "instance"
  vpc_id               = data.aws_vpc.dw_vpc.id
  deregistration_delay = "300"
  ip_address_type      = "ipv6"

  health_check {
    enabled             = true
    healthy_threshold   = 3
    interval            = 10
    matcher             = 200
    port                = 80
    path                = "/health"
    protocol            = "HTTP"
    timeout             = 3
    unhealthy_threshold = 2
  }

  port     = "443"
  protocol = "TCP"

  stickiness {
    enabled = false
    type    = "source_ip"
  }
}

resource "aws_security_group" "cloudflare_only" {
  name        = "LBBehindCloudflare"
  description = "Load balancer that only allows egress to Cloudflare"
  vpc_id      = data.aws_vpc.dw_vpc.id

  egress {
    cidr_blocks      = ["0.0.0.0/0"]
    ipv6_cidr_blocks = ["::/0"]
    from_port        = 0
    to_port          = 0
    protocol         = -1
    self             = false
  }

  ingress {
    cidr_blocks      = module.cloudflare_ip_range.ipv4s
    ipv6_cidr_blocks = module.cloudflare_ip_range.ipv6s
    from_port        = 443
    to_port          = 443
    protocol         = "TCP"
    self             = false
  }
}

resource "aws_security_group" "dw_forums_sg" {
  name        = "dw-forums"
  description = "Allows inbound access only from Cloudflare"
  vpc_id      = data.aws_vpc.dw_vpc.id

  ingress {
    from_port       = 0
    to_port         = 0
    self            = false
    protocol        = -1
    security_groups = [aws_security_group.cloudflare_only.id]
  }

  egress {
    from_port        = 0
    to_port          = 0
    protocol         = -1
    ipv6_cidr_blocks = ["::/0"]
    cidr_blocks      = ["0.0.0.0/0"]
  }
}
