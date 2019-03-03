#------------------------------------------------------------------------------
# variables

variable project                        { default = "newstash" }
variable environment                    { }
variable app_version                    { }

variable aws_access_key                 { }
variable aws_secret_key                 { }
variable region                         { default = "us-east-1" }
variable az                             { default = "us-east-1a" }

variable ssl_cert_arn                   { default = "arn:aws:acm:us-east-1:685394013264:certificate/bcfc303a-a1b4-4c82-a29b-7bf95c16cab7"}
variable hostname                       { default = "booksto.love" }

variable zone_id                        { default = "Z1MH9O4CHWQAX8" }

variable vpc_cidr                       { default = "10.0.0.0/16" }
variable public_cidr                    { default = "10.0.1.0/24" }
variable private_cidr                   { default = "10.0.2.0/24" }

variable fargate_cpu_units              { default = "2048" }
variable fargate_memory                 { default = "4096" }
variable min_cluser_size                { default = "1" }
variable max_cluser_size                { default = "5" }
variable cpu_usage_down_trigger         { default = "40" }
variable cpu_usage_up_trigger           { default = "30" }

#------------------------------------------------------------------------------

# data
data "aws_caller_identity" "the-aws-caller-identity" { }

data "template_file" "the-task-definition" {
    template = "${file("./task.json.tpl")}"
    vars {
        project                     = "${var.project}"
        environment                 = "${var.environment}"

        app_version                 = "${var.app_version}"

        aws_account_id              = "${data.aws_caller_identity.the-aws-caller-identity.account_id}"
        region                      = "${var.region}"


    }
}

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
    vpc_cidr                        = "${var.vpc_cidr}"
    public_cidr                     = "${var.public_cidr}"
    private_cidr                    = "${var.private_cidr}"
}

module "the-nginx-ecr" {
    source                          = "./modules/ecr"
    project                         = "${var.project}"
    environment                     = "${var.environment}"
    image                           = "nginx"
}

module "the-php-ecr" {
    source                          = "./modules/ecr"
    project                         = "${var.project}"
    environment                     = "${var.environment}"
    image                           = "php"
}

module "the-s3-asset-bucket" {
    source                          = "./modules/s3-cf"
    project                         = "${var.project}"
    environment                     = "${var.environment}"
    region                          = "${var.region}"
}

module "the-s3-log-bucket" {
    source                          = "./modules/s3-logs"
    project                         = "${var.project}"
    environment                     = "${var.environment}"
    region                          = "${var.region}"
}

module "main_cloudfront_distribution" {
    source                          = "./modules/cloudfront"

    project                         = "${var.project}"
    environment                     = "${var.environment}"

    s3_domain_name                  = "${module.the-s3-asset-bucket.bucket_domain_name}"
    s3_origin_access_identity       = "${module.the-s3-asset-bucket.cloudfront_access_identity_path}"

    logging_bucket                  = "${module.the-s3-log-bucket.bucket}"
    logging_prefix                  = "cloudfront"

    ssl_cert_arn                    = "${var.ssl_cert_arn}"
    hostname                        = "${var.hostname}"

//    origin_alb_domain_name          = "${var.host_name}"
}

module "the-cname" {
    source                          = "./modules/aliased_route53"
    hostname                        = "${var.hostname}"
    zone_id                         = "${var.zone_id}"
    aliased_hostname                = "${module.main_cloudfront_distribution.cloudfront_distribution_domain_name}"
    aliased_zone_id                 = "${module.main_cloudfront_distribution.cloudfront_distribution_hosted_zone_id}"
}

// defines privileges for the autoscaling rules
module "the-ecs-autoscaling-role" {
    source                          = "./modules/iam_role_w_policy"
    iam_role_name                   = "${var.project}-${var.environment}-ECSAutoscalingRole"
    iam_policy_description          = "${var.project} ${var.environment} ECS Autoscaling Role"
    policy                          = "${file("./policy/ecs-autoscaling-policy.json")}"
    assume_role_policy              = "${file("./policy/ecs-autoscaling-role.json")}"
    policy_name                     = "${var.project}-${var.environment}-ECSAutoscalingPolicy"
    policy_attachment_name          = "${var.project}-${var.environment}-ECSAutoscalingPolicyAttachement"
}

// defines privileges for ECS "instance"
module "the-ecs-execution-role" {
    source                          = "./modules/iam_role_w_policy"
    iam_role_name                   = "${var.project}-${var.environment}-ECSExecutionRole"
    iam_policy_description          = "${var.project} ${var.environment} ECS Execution Role"
    policy                          = "${file("./policy/ecs-execution-policy.json")}"
    assume_role_policy              = "${file("./policy/ecs-execution-role.json")}"
    policy_name                     = "${var.project}-${var.environment}-ECSExecutionPolicy"
    policy_attachment_name          = "${var.project}-${var.environment}-ECSExecutionPolicyAttachement"
}

// defines privileges for the fargate task
module "the-ecs-task-role" {
    source                          = "./modules/iam_role_w_policy"
    iam_role_name                   = "${var.project}-${var.environment}-ECSTaskRole"
    iam_policy_description          = "${var.project} ${var.environment} ECS Task Role"
    policy                          = "${data.template_file.the-ecs-task-policy.rendered}"
    policy                          = "${file("./policy/ecs-task-policy.json")}"
    assume_role_policy              = "${file("./policy/ecs-task-role.json")}"
    policy_name                     = "${var.project}-${var.environment}-ECSTaskPolicy"
    policy_attachment_name          = "${var.project}-${var.environment}-ECSTaskPolicyAttachement"
}

resource "aws_security_group" "the-private-mongo-sg" {
    name                            = "${var.project}-${var.environment}-allow-mongo-from-private"
    description                     = "Allow web from world"
    vpc_id                          = "${module.the-vpc.vpc_id}"

    ingress {
        from_port                   = 27017
        to_port                     = 27017
        protocol                    = "tcp"
        cidr_blocks                 = ["0.0.0.0/0"]
    }

    egress {
        from_port   = 0
        to_port     = 0
        protocol    = "-1"
        cidr_blocks = ["${var.private_cidr}"]
    }

    tags = {
        name                        = "${var.project}-${var.environment}-allow-mongo-from-private"
    }
}

resource "aws_security_group" "the-private-mysql-sg" {
    name                            = "${var.project}-${var.environment}-allow-mysql-from-private"
    description                     = "Allow web from world"
    vpc_id                          = "${module.the-vpc.vpc_id}"

    ingress {
        from_port                   = 3306
        to_port                     = 3306
        protocol                    = "tcp"
        cidr_blocks                 = ["0.0.0.0/0"]
    }

    egress {
        from_port   = 0
        to_port     = 0
        protocol    = "-1"
        cidr_blocks = ["${var.private_cidr}"]
    }

    tags = {
        name                        = "${var.project}-${var.environment}-allow-mysql-from-private"
    }
}

resource "aws_security_group" "the-public-web-sg" {
    name                            = "${var.project}-${var.environment}-allow-web-from-world"
    description                     = "Allow web from world"
    vpc_id                          = "${module.the-vpc.vpc_id}"

    ingress {
        from_port                   = 80
        to_port                     = 80
        protocol                    = "tcp"
        cidr_blocks                 = ["0.0.0.0/0"]
    }

    egress {
        from_port   = 0
        to_port     = 0
        protocol    = "-1"
        cidr_blocks = ["0.0.0.0/0"]
    }

    tags = {
        name                        = "${var.project}-${var.environment}-allow-web-from-world"
    }
}

resource "aws_security_group" "the-public-ssh-sg" {
    name                            = "${var.project}-${var.environment}-allow-ssh-from-world"
    description                     = "Allow ssh from world"
    vpc_id                          = "${module.the-vpc.vpc_id}"

    ingress {
        from_port                   = 22
        to_port                     = 22
        protocol                    = "tcp"
        cidr_blocks                 = ["0.0.0.0/0"]
    }

    egress {
        from_port   = 0
        to_port     = 0
        protocol    = "-1"
        cidr_blocks = ["0.0.0.0/0"]
    }

    tags = {
        name                        = "${var.project}-${var.environment}-allow-ssh-from-world"
    }
}

/*
module "autoscaling_fargate" {
    source                          = "./modules/ecs-fargate"

    project                         = "${var.project}"
    environment                     = "${var.environment}"

    # App
    task_definition                 = "${data.template_file.the-task-definition.rendered}"
    logging_bucket                  = "${module.the-s3-log-bucket.bucket}"
    logging_prefix                  = "alb"

    # VPC
    vpc_id                          = "${module.the-vpc.vpc_id}"
    alb_security_groups             = []
    alb_subnets                     = ["${module.the-vpc.private_subnet
    ecs_security_groups             = []
    ecs_subnets                     = []

    # Fargate
    fargate_cpu_units               = "${var.fargate_cpu_units}"
    fargate_memory                  = "${var.fargate_memory}"
    min_cluser_size                 = "${var.min_cluser_size}"
    max_cluser_size                 = "${var.max_cluser_size}"
    cpu_usage_down_trigger          = "${var.cpu_usage_down_trigger}"
    cpu_usage_up_trigger            = "${var.cpu_usage_up_trigger}"
}
*/




# security groups
# s3 assets
# autoscaling ecs fargate
# cloudfront
# route 53 record
