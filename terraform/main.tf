#------------------------------------------------------------------------------
# variables

variable project                        { default = "newstash" }
variable environment                    { }
variable app_version                    { }

variable aws_access_key                 { }
variable aws_secret_key                 { }
variable region                         { }
variable public_azs                     { }
variable private_azs                    { }

variable ssl_cert_arn                   { }
variable hostname                       { }

variable zone_id                        { }

variable vpc_cidr                       { }
variable public_cidrs                   { }
variable private_cidrs                  { }

variable fargate_cpu_units              { }
variable fargate_memory                 { }
variable min_cluser_size                { }
variable max_cluser_size                { }
variable cpu_usage_down_trigger         { }
variable cpu_usage_up_trigger           { }

# app vars
variable mongodb_url                    { }
variable mongodb_db                     { }
variable mailer_url                     { }

variable app_secret                     { }
variable database_url                   { }

variable facebook_app_id                { }
variable facebook_secret                { }
variable google_client_id               { }
variable google_client_secret           { }
variable gaq_id                         { }
variable amzn_affiliate                 { }

variable log_retention_in_days          { default = "30" }

#------------------------------------------------------------------------------

# data
data "aws_caller_identity" "the-aws-caller-identity" { }

data "template_file" "the-task-definition" {
    template = "${file("./task.json.tpl")}"
    vars {
        project                     = "${var.project}"
        environment                 = "${var.environment}"
        aws_region                  = "${var.region}"

        app_version                 = "${var.app_version}"

        aws_account_id              = "${data.aws_caller_identity.the-aws-caller-identity.account_id}"
        region                      = "${var.region}"

        mongodb_url                 = "${var.mongodb_url}"
        mongodb_db                  = "${var.mongodb_db}"
        mailer_url                  = "${var.mailer_url}"

        app_secret                  = "${var.app_secret}"
        database_url                = "${var.database_url}"

        facebook_app_id             = "${var.facebook_app_id}"
        facebook_secret             = "${var.facebook_secret}"
        google_client_id            = "${var.google_client_id}"
        google_client_secret        = "${var.google_client_secret}"
        gaq_id                      = "${var.gaq_id}"
        amzn_affiliate              = "${var.amzn_affiliate}"

        php-log-group               = "${local.php-log-group}"
        nginx-log-group             = "${local.nginx-log-group}"
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

locals {
    php-log-group           = "${var.project}-${var.environment}-php"
    nginx-log-group         = "${var.project}-${var.environment}-nginx"
}

#------------------------------------------------------------------------------

resource "aws_cloudwatch_log_group" "nginx" {
    name              = "${local.nginx-log-group}"
    retention_in_days = "${var.log_retention_in_days}"
}

resource "aws_cloudwatch_log_group" "php" {
    name              = "${local.php-log-group}"
    retention_in_days = "${var.log_retention_in_days}"
}

module "the-vpc" {
    source                          = "./modules/vpc"
    project                         = "${var.project}"
    environment                     = "${var.environment}"

    public_azs                      = "${split(",",var.public_azs)}"
    //private_azs                     = "${split(",", var.private_azs)}"

    vpc_cidr                        = "${var.vpc_cidr}"
    public_cidrs                    = "${split(",",var.public_cidrs)}"
    //private_cidrs                   = "${split(",", var.private_cidrs)}"
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

    alb_domain_name                 = "${module.autoscaling_fargate.alb_dns_name}"
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

module "mysql-from-ecs-security-group" {
    source                          = "./modules/security_group"

    allowed_port                    = "3306"

    project                         = "${var.project}"
    environment                     = "${var.environment}"
    name                            = "mysql-from-ecs"
    vpc_id                          = "${module.the-vpc.vpc_id}"

    allowed_security_groups         = ["${module.ecs-cluster-security-group.id}"]
}

module "mongo-from-ecs-security-group" {
    source                          = "./modules/security_group"

    allowed_port                    = "27017"

    project                         = "${var.project}"
    environment                     = "${var.environment}"
    name                            = "mongo-from-ecs"
    vpc_id                          = "${module.the-vpc.vpc_id}"

    allowed_security_groups         = ["${module.ecs-cluster-security-group.id}"]
}

module "ecs-cluster-security-group" {
    source                          = "./modules/security_group"

    allowed_port                    = "80"

    project                         = "${var.project}"
    environment                     = "${var.environment}"
    name                            = "ecs-cluster"
    vpc_id                          = "${module.the-vpc.vpc_id}"
}

module "alb-security-group" {
    source                          = "./modules/security_group"

    allowed_port                    = "80"

    project                         = "${var.project}"
    environment                     = "${var.environment}"
    name                            = "alb"
    vpc_id                          = "${module.the-vpc.vpc_id}"
}

module "ssh-security-group" {
    source                          = "./modules/security_group"

    allowed_port                    = "22"

    project                         = "${var.project}"
    environment                     = "${var.environment}"
    name                            = "ssh-from-world"
    vpc_id                          = "${module.the-vpc.vpc_id}"
}

module "autoscaling_fargate" {
    source                          = "./modules/ecs-fargate"

    project                         = "${var.project}"
    environment                     = "${var.environment}"

    # IAM
    ecs_autoscaling_role_arn        = "${module.the-ecs-autoscaling-role.role_arn}"
    ecs_execution_role_arn          = "${module.the-ecs-execution-role.role_arn}"
    ecs_task_role_arn               = "${module.the-ecs-task-role.role_arn}"

    # App
    task_definition                 = "${data.template_file.the-task-definition.rendered}"
    logging_bucket                  = "${module.the-s3-log-bucket.bucket}"
    logging_prefix                  = "alb"

    # VPC
    vpc_id                          = "${module.the-vpc.vpc_id}"
    alb_security_groups             = ["${module.alb-security-group.id}"]
    alb_subnets                     = ["${module.the-vpc.public_subnet_ids}"]
    ecs_security_groups             = ["${module.ecs-cluster-security-group.id}"]
    ecs_subnets                     = ["${module.the-vpc.public_subnet_ids}"]

    # Fargate
    fargate_cpu_units               = "${var.fargate_cpu_units}"
    fargate_memory                  = "${var.fargate_memory}"
    min_cluser_size                 = "${var.min_cluser_size}"
    max_cluser_size                 = "${var.max_cluser_size}"
    cpu_usage_down_trigger          = "${var.cpu_usage_down_trigger}"
    cpu_usage_up_trigger            = "${var.cpu_usage_up_trigger}"
}

