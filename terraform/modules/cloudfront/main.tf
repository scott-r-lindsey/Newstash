
variable environment                        { }
variable project                            { }

variable alb_domain_name                    { }
variable s3_domain_name                     { }
variable s3_origin_access_identity          { }

variable ssl_cert_arn                       { }
variable hostname                           { }

variable logging_bucket                     { }
variable logging_prefix                     { }

locals {
    s3_origin_id = "the-s3-origin"
    alb_origin_id = "the-alb-origin"
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

    origin {
        domain_name = "${var.alb_domain_name}"
        origin_id   = "${local.alb_origin_id}"

        custom_origin_config {
            http_port                   = 80
            https_port                  = 443
            origin_ssl_protocols        = ["TLSv1.2"]
            origin_protocol_policy      = "http-only"
            origin_read_timeout         = 30
            origin_keepalive_timeout    = 5
        }
    }

    default_cache_behavior {
        target_origin_id        = "${local.alb_origin_id}"
        allowed_methods         = ["DELETE", "GET", "HEAD", "OPTIONS", "PATCH", "POST", "PUT"]
        cached_methods          = ["GET", "HEAD", "OPTIONS"]

        viewer_protocol_policy  = "redirect-to-https"

        compress                = true

        forwarded_values {
            query_string = true
            headers      = ["Access-Control-Request-Headers", "Access-Control-Request-Method", "Host", "Origin"]

            cookies {
                forward = "whitelist"
                whitelisted_names  = ["PHPSESSID"]
            }
        }
    }

    ordered_cache_behavior {
        target_origin_id        = "${local.s3_origin_id}"
        path_pattern            = "/sitemap*"

        allowed_methods         = ["GET", "HEAD"]
        cached_methods          = ["GET", "HEAD"]
        viewer_protocol_policy  = "https-only"
        min_ttl                 = 0
        default_ttl             = 86400
        max_ttl                 = 31536000
        compress                = "true"

        forwarded_values {
            query_string            = "false"
            headers                 = []
            query_string_cache_keys = []

            cookies {
                forward = "none"
            }
        }
    }

    ordered_cache_behavior {
        target_origin_id        = "${local.s3_origin_id}"
        path_pattern            = "/robots.txt"

        allowed_methods         = ["GET", "HEAD"]
        cached_methods          = ["GET", "HEAD"]
        viewer_protocol_policy  = "https-only"
        min_ttl                 = 0
        default_ttl             = 86400
        max_ttl                 = 31536000
        compress                = "true"

        forwarded_values {
            query_string            = "false"
            headers                 = []
            query_string_cache_keys = []

            cookies {
                forward = "none"
            }
        }
    }

    ordered_cache_behavior {
        target_origin_id        = "${local.s3_origin_id}"
        path_pattern            = "/img/blog/*"

        allowed_methods         = ["GET", "HEAD"]
        cached_methods          = ["GET", "HEAD"]
        viewer_protocol_policy  = "https-only"
        min_ttl                 = 0
        default_ttl             = 86400
        max_ttl                 = 31536000
        compress                = "true"

        forwarded_values {
            query_string            = "false"
            headers                 = []
            query_string_cache_keys = []

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
