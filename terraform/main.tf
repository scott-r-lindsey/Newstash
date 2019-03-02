#------------------------------------------------------------------------------
# variables

variable project                        { default = "newstash" }
variable environment                    { }

variable aws_access_key                 { }
variable aws_secret_key                 { }
variable region                         { default = "us-east-1" }
variable az                             { default = "us-east-1a" }
#------------------------------------------------------------------------------

# data
data "aws_caller_identity" "current"            { }

#------------------------------------------------------------------------------
# services

provider "aws" {
    access_key                      = "${var.aws_access_key}"
    secret_key                      = "${var.aws_secret_key}"
    region                          = "us-east-1"
}

terraform {
  backend "s3" {
  }
}

#------------------------------------------------------------------------------

module "the-vpc" {
    source                          = "./modules/vpc"
    project                         = "${var.project}"
    environment                     = "${var.environment}"
    az                              = "${var.az}"
}

# security groups
# ecr
# s3 assets
# autoscaling ecs fargate
# cloudfront
# route 53 record
