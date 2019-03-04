
variable environment                        { }
variable project                            { }

variable s3_domain_name                     { }
variable s3_origin_access_identity          { }

variable ssl_cert_arn                       { }
variable hostname                           { }

variable logging_bucket                     { }
variable logging_prefix                     { }


locals {
    s3_origin_id = "the-s3-origin"
}

#------------------------------------------------------------------------------

resource "aws_cloudfront_distribution" "the-cloudfront-distribution" {

    logging_config {
        include_cookies = true
        bucket          = "${var.logging_bucket}.s3.amazonaws.com"
        prefix          = "${var.logging_prefix}"
    }

    enabled = true

    aliases = ["${var.hostname}"]

    viewer_certificate {
        acm_certificate_arn             = "${var.ssl_cert_arn}"
        ssl_support_method              = "sni-only"
        minimum_protocol_version        = "TLSv1"
    }

    restrictions {
        geo_restriction {
            restriction_type = "none"
            locations        = []
        }
    }

    origin {
        domain_name = "${var.s3_domain_name}"
        origin_id   = "${local.s3_origin_id}"

        s3_origin_config {
            origin_access_identity = "${var.s3_origin_access_identity}"
        }
    }


    default_cache_behavior {
        target_origin_id        = "${local.s3_origin_id}"
        allowed_methods         = ["GET", "HEAD", "OPTIONS"],
        cached_methods          = ["GET", "HEAD", "OPTIONS"]
        viewer_protocol_policy  = "redirect-to-https"

        compress                = true

        forwarded_values {
            query_string = false
            headers      = ["Access-Control-Request-Headers", "Access-Control-Request-Method", "Origin"]

            cookies {
                forward = "none"
            }
        }
    }
}

#------------------------------------------------------------------------------

output "cloudfront_distribution_domain_name" {
    value = "${aws_cloudfront_distribution.the-cloudfront-distribution.domain_name}"
}
output "cloudfront_distribution_hosted_zone_id" {
    value = "${aws_cloudfront_distribution.the-cloudfront-distribution.hosted_zone_id}"
}
