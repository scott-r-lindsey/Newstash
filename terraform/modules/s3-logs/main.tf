
variable environment                        { }
variable project                            { }
variable region                             { }

#----------------------------------------------------------------------------

resource "aws_s3_bucket" "the-bucket" {
    bucket        = "${var.project}-${var.environment}-assets"
    region        = "${var.region}"

    acl           = "log-delivery-write"

    lifecycle_rule {
        id      = "alb"
        prefix  = "alb/"
        enabled = true

        transition {
            days = 30
            storage_class = "STANDARD_IA"
        }

        transition {
            days = 60
            storage_class = "GLACIER"
        }

        expiration {
            days = 90
        }
    }
}

#----------------------------------------------------------------------------

output "bucket" {
    value = "${aws_s3_bucket.the-bucket.bucket}"
}
output "id" {
    value = "${aws_s3_bucket.the-bucket.id}"
}
output "arn" {
    value = "${aws_s3_bucket.the-bucket.arn}"
}
output "bucket_domain_name" {
    value = "${aws_s3_bucket.the-bucket.bucket_domain_name}"
}
