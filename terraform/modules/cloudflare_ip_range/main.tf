data "http" "ipv4_list_content" {
  url = var.ipv4_list_url
}

data "http" "ipv6_list_content" {
  url = var.ipv6_list_url
}
