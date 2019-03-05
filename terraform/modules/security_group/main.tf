
variable environment                        { }
variable project                            { }
variable name                               { }
variable vpc_id                             { }
variable allowed_port                       { }
variable allowed_cidrs                      { type = "list", default = ["0.0.0.0/0"] }
variable allowed_security_groups            { type = "list", default = [] }

# -----------------------------------------------------------------------------

resource "aws_security_group" "the-sg" {
    name                            = "${var.project}-${var.environment}-allow-${var.name}"
    description                     = "Allow web from world"
    vpc_id                          = "${var.vpc_id}"

    ingress {
        from_port                   = "${var.allowed_port}"
        to_port                     = "${var.allowed_port}"
        protocol                    = "tcp"
        cidr_blocks                 = ["${var.allowed_cidrs}"]
        security_groups             = ["${var.allowed_security_groups}"]
    }

    egress {
        from_port   = 0
        to_port     = 0
        protocol    = "-1"
        cidr_blocks = ["0.0.0.0/0"]
    }

    ingress {
        from_port   = -1
        to_port     = -1
        protocol    = "icmp"
        cidr_blocks = ["0.0.0.0/0"]
    }

    tags = {
        name                        = "${var.project}-${var.environment}-allow-${var.name}"
    }
}


# -----------------------------------------------------------------------------

output "id" {
    value = "${aws_security_group.the-sg.id}"
}

