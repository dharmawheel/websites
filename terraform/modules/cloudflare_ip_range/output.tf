output "ipv4s" {
  value = split("\n", trimspace(data.http.ipv4_list_content.response_body))
}

output "ipv6s" {
  value = split("\n", trimspace(data.http.ipv6_list_content.response_body))
}
