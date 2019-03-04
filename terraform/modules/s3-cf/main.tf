
variable environment                        { }
variable project                            { }
variable region                             { }

#------------------------------------------------------------------------------

resource "aws_cloudfront_origin_access_identity" "the-origin-access-identity" {
    comment = "access-identity-${var.project}-${var.environment}-storage.s3.amazonaws.com"
}

resource "aws_s3_bucket" "the-bucket" {
    bucket        = "${var.project}-${var.environment}-assets"
    region        = "${var.region}"
    acl           = "private"

    policy          = <<EOF
{
    "Version": "2012-10-17",
    "Statement": [
        {
            "Sid": "1",
            "Effect": "Allow",
            "Principal": {
                "AWS": "${aws_cloudfront_origin_access_identity.the-origin-access-identity.iam_arn}"
            },
            "Action": "s3:GetObject",
            "Resource": "arn:aws:s3:::${var.project}-${var.environment}-assets/*"
        }
    ]
}
EOF

}

#------------------------------------------------------------------------------

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
output "cloudfront_access_identity_path" {
    value = "${aws_cloudfront_origin_access_identity.the-origin-access-identity.cloudfront_access_identity_path}"
}
