variable environment                        { }
variable project                            { }

# IAM
variable ecs_autoscaling_role_arn           { }
variable ecs_task_role_arn                  { }
variable ecs_execution_role_arn             { }

#------------------------------------------------------------------------------

resource "aws_ecs_cluster" "the-ecs-cluster" {
    name = "${var.project}-${var.environment}-ECSCluster"
}

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

    tags {
        CostCenter      = "${var.costcenter_id}"
        ProjectName     = "${var.project}"
        Environment     = "${var.environment}"
        Platform        = "${var.turbot_account}"
    }
}

