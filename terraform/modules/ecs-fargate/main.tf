variable environment                        { }
variable project                            { }

# app
variable task_definition                    { }
variable logging_bucket                     { }
variable logging_prefix                     { }

# VPC
variable vpc_id                             { }
variable alb_security_groups                { type = "list" }
variable alb_subnets                        { type = "list" }
variable ecs_security_groups                { type = "list" }
variable ecs_subnets                        { type = "list" }

# IAM
variable ecs_autoscaling_role_arn           { }
variable ecs_task_role_arn                  { }
variable ecs_execution_role_arn             { }

# Fargate
variable fargate_cpu_units                  { }
variable fargate_memory                     { }
variable min_cluser_size                    { }
variable max_cluser_size                    { }
variable cpu_usage_down_trigger             { }
variable cpu_usage_up_trigger               { }

#------------------------------------------------------------------------------

resource "aws_ecs_cluster" "the-ecs-cluster" {
    name = "${var.project}-${var.environment}-ECSCluster"
}

/*
resource "aws_alb_target_group" "the-alb-target-group" {
    name                 = "${var.project}-${var.environment}-TG"
    port                 = 80
    protocol             = "HTTP"
    vpc_id               = "${var.vpc_id}"
    deregistration_delay = 30
    target_type          = "ip"

    health_check {
        path                    = "/healthcheck"
        matcher                 = 200
        interval                = 15
        timeout                 = 10
        port                    = 80
        healthy_threshold       = 5
        unhealthy_threshold     = 5
    }
}

resource "aws_alb" "the-alb" {
    name                    = "${var.project}-${var.environment}-ALB"
    subnets                 = ["${var.alb_subnets}"]
    security_groups         = ["${var.alb_security_groups}"]

    access_logs {
        bucket = "${var.s3_log_bucket}"
        prefix = "alb"
    }

}

*/
