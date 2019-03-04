variable hostname                               { }
variable zone_id                                { }
variable aliased_hostname                       { }
variable aliased_zone_id                        { }

# -----------------------------------------------------------------------------

resource "aws_route53_record" "route" {
    zone_id                 = "${var.zone_id}"
    name                    = "${var.hostname}"
    type                    = "A"

    alias {
        name                   = "${var.aliased_hostname}"
        zone_id                = "${var.aliased_zone_id}"
        evaluate_target_health = true
    }
}
