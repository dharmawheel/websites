resource "aws_dynamodb_table" "tfstate_lock_table" {
  name = "dhamma-terraform-lock-table"
  attribute {
    name = "LockID"
    type = "S"
  }

  billing_mode                = "PAY_PER_REQUEST"
  deletion_protection_enabled = false
  hash_key                    = "LockID"

  point_in_time_recovery {
    enabled = "false"
  }

  stream_enabled = "false"
  table_class    = "STANDARD"
}

resource "aws_s3_bucket" "tfstate_bucket" {
  bucket = "dhamma-tfstate"
}

resource "aws_s3_bucket_versioning" "tfstate_bucket" {
  bucket = aws_s3_bucket.tfstate_bucket.id
  versioning_configuration {
    status = "Enabled"
  }
}

resource "aws_s3_bucket_server_side_encryption_configuration" "tfstate_bucket" {
  bucket = aws_s3_bucket.tfstate_bucket.id
  rule {
    apply_server_side_encryption_by_default {
      sse_algorithm = "AES256"
    }
  }
}
