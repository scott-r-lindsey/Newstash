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
        bucket = "${var.logging_bucket}"
        prefix = "${var.logging_prefix}"
    }
}

resource "aws_ecs_task_definition" "the-ecs-task-definition" {
    family                  = "${var.project}-${var.environment}-task-def"
    container_definitions   = "${var.task_definition}"

    execution_role_arn      = "${var.ecs_execution_role_arn}"
    task_role_arn           = "${var.ecs_task_role_arn}"

    requires_compatibilities = ["FARGATE"]
    network_mode            = "awsvpc"
    cpu                     = "${var.fargate_cpu_units}"
    memory                  = "${var.fargate_memory}"
}

resource "aws_ecs_service" "the-ecs-service" {
    name                    = "${var.project}-${var.environment}-ECSService"
    cluster                 = "${aws_ecs_cluster.the-ecs-cluster.id}"
    task_definition         = "${aws_ecs_task_definition.the-ecs-task-definition.arn}"
    desired_count           = "1"

    depends_on      = [
        "aws_ecs_task_definition.the-ecs-task-definition",
        "aws_alb_listener.the-alb-listner",
    ]

    load_balancer {
        target_group_arn = "${aws_alb_target_group.the-alb-target-group.arn}"
        container_name   = "nginx"
        container_port   = 80
    }

    # FARGATE
    launch_type     = "FARGATE"

    network_configuration {
        security_groups = ["${var.ecs_security_groups}"]
        subnets         = ["${var.ecs_subnets}"]
    }
}

resource "aws_alb_listener" "the-alb-listner" {
    load_balancer_arn = "${aws_alb.the-alb.arn}"
    port              = "80"
    protocol          = "HTTP"

    default_action {
    target_group_arn = "${aws_alb_target_group.the-alb-target-group.arn}"
        type             = "forward"
    }
}

# -----------------------------------------------------------------------------
# autoscaling, tasks
# This scales the number of tasks running within a cluster

# ---------> Up and down alarms
resource "aws_cloudwatch_metric_alarm" "the-app-cpu-high-alarm" {
    alarm_name              = "${var.project}-${var.environment}-app-high-cpu-alarm"
    comparison_operator     = "GreaterThanOrEqualToThreshold"
    evaluation_periods      = "1"
    metric_name             = "CPUUtilization"
    namespace               = "AWS/ECS"
    period                  = "60"
    statistic               = "Average"
    threshold               = "${var.cpu_usage_up_trigger}"
    alarm_description       = "High CPU usage alarm"
    alarm_actions = [
        "${aws_appautoscaling_policy.the-app-scale-up-policy.arn}"
    ]
    dimensions {
        ServiceName         = "${var.project}-${var.environment}-ECSService"
        ClusterName         = "${var.project}-${var.environment}-ECSCluster"
    }
    depends_on              = ["aws_appautoscaling_policy.the-app-scale-up-policy"]
}
resource "aws_cloudwatch_metric_alarm" "the-app-cpu-low-alarm" {
    alarm_name              = "${var.project}-${var.environment}-app-low-cpu-alarm"
    comparison_operator     = "LessThanOrEqualToThreshold"
    evaluation_periods      = "2"
    metric_name             = "CPUUtilization"
    namespace               = "AWS/ECS"
    period                  = "120"
    statistic               = "Average"
    threshold               = "${var.cpu_usage_down_trigger}"
    alarm_description       = "Low CPU usage alarm"
    alarm_actions = [
        "${aws_appautoscaling_policy.the-app-scale-down-policy.arn}"
    ]
    dimensions {
        ServiceName         = "${var.project}-${var.environment}-ECSService"
        ClusterName         = "${var.project}-${var.environment}-ECSCluster"
    }
    depends_on              = ["aws_appautoscaling_policy.the-app-scale-down-policy"]
}

# ---------> This defines the pool of "instances"
resource "aws_appautoscaling_target" "ecs_target" {
    min_capacity            = "${var.min_cluser_size}"
    max_capacity            = "${var.max_cluser_size}"
    resource_id             = "service/${var.project}-${var.environment}-ECSCluster/${var.project}-${var.environment}-ECSService"
    role_arn                = "${var.ecs_autoscaling_role_arn}"
    scalable_dimension      = "ecs:service:DesiredCount"
    service_namespace       = "ecs"
    depends_on              = ["aws_ecs_service.the-ecs-service"]
}

# ---------> The up and down scaling policies
resource "aws_appautoscaling_policy" "the-app-scale-up-policy" {
    name                    = "${var.project}-(${var.environment})-app-autoscale-up-policy"

    resource_id             = "service/${var.project}-${var.environment}-ECSCluster/${var.project}-${var.environment}-ECSService"
    scalable_dimension      = "ecs:service:DesiredCount"
    service_namespace       = "ecs"

    step_scaling_policy_configuration {
        adjustment_type         = "ChangeInCapacity"
        cooldown                = 60
        metric_aggregation_type = "Maximum"

        step_adjustment {
            metric_interval_upper_bound = 0
            scaling_adjustment          = 1
        }
    }

    depends_on              = [
        "aws_appautoscaling_target.ecs_target",
        "aws_ecs_service.the-ecs-service",
        "aws_alb_target_group.the-alb-target-group"
    ]
}
resource "aws_appautoscaling_policy" "the-app-scale-down-policy" {
    name                    = "${var.project}-(${var.environment})-app-autoscale-down-policy"

    resource_id             = "service/${var.project}-${var.environment}-ECSCluster/${var.project}-${var.environment}-ECSService"
    scalable_dimension      = "ecs:service:DesiredCount"
    service_namespace       = "ecs"

    step_scaling_policy_configuration {
        adjustment_type         = "ChangeInCapacity"
        cooldown                = 60
        metric_aggregation_type = "Maximum"

        step_adjustment {
            metric_interval_upper_bound = 0
            scaling_adjustment          = -1
        }
    }

    depends_on              = [
        "aws_appautoscaling_target.ecs_target",
        "aws_ecs_service.the-ecs-service",
        "aws_alb_target_group.the-alb-target-group"
    ]
}

# -----------------------------------------------------------------------------

output "alb_dns_name" {
  value = "${aws_alb.the-alb.dns_name}"
}
