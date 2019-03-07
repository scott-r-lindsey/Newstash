
variable environment                        { }
variable project                            { }
variable region                             { }

#----------------------------------------------------------------------------

resource "aws_s3_bucket" "the-bucket" {
    bucket        = "${var.project}-${var.environment}-logs"
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
// http://docs.aws.amazon.com/elasticloadbalancing/latest/application/load-balancer-access-logs.html
data "aws_iam_policy_document" "alb_bucket_policy" {
    statement {
        sid = "Stmt1485563687656"
        effect = "Allow"

        resources = [
            "${aws_s3_bucket.the-bucket.arn}",
            "${aws_s3_bucket.the-bucket.arn}/*",
        ]

        actions = [
            "s3:PutObject",
        ]

        principals {
            type = "AWS"
            identifiers = [
                "arn:aws:iam::127311923021:root",
            ]
        }
    }
}

resource "aws_s3_bucket_policy" "alb_bucket_policy" {
    bucket = "${aws_s3_bucket.the-bucket.id}"
    policy = "${data.aws_iam_policy_document.alb_bucket_policy.json}"
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
