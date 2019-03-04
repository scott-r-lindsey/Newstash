variable iam_role_name                          { }
variable iam_policy_description                 { }
variable policy                                 { }
variable assume_role_policy                     { }
variable policy_name                            { }
variable policy_attachment_name                 { }

#------------------------------------------------------------------------------

resource "aws_iam_role" "the-role" {
    name                    = "${var.iam_role_name}"
    assume_role_policy      = "${var.assume_role_policy}"
}
resource "aws_iam_policy" "the-policy" {
    name                    = "${var.policy_name}"
    description             = "${var.iam_policy_description}"
    policy                  = "${var.policy}"
}
resource "aws_iam_policy_attachment" "the-attachment" {
    name                    = "${var.policy_attachment_name}"
    roles                   = ["${aws_iam_role.the-role.name}"]
    policy_arn              = "${aws_iam_policy.the-policy.arn}"
}

#------------------------------------------------------------------------------

output "role_arn" {
    value = "${aws_iam_role.the-role.arn}"
}
output "role_name" {
    value = "${aws_iam_role.the-role.name}"
}
