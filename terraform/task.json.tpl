[
    {
        "environment": [
            {
                "name": "APP_ENV",
                "value": "${environment}"
            },
            {
                "name": "APP_VERSION",
                "value": "${app_version}"
            },
        ],
        "essential": true,
        "image": "${aws_account_id}.dkr.ecr.${region}.amazonaws.com/${project}-${environment}-php:${app_version}",
        "memoryReservation": 512,
        "name": "php",
        "logConfiguration": {
            "logDriver": "awslogs",
            "options": {
                "awslogs-group": "${project}-${environment}-php",
                "awslogs-region": "${region}",
                "awslogs-stream-prefix": "php/"
            }
        }
    },
    {
        "environment": [
        ],
        "essential": true,
        "image": "${aws_account_id}.dkr.ecr.${region}.amazonaws.com/${project}-${environment}-nginx:${app_version}",
        "memoryReservation": 512,
        "name": "nginx",
        "portMappings": [
            {
                "containerPort": 80
            }
        ],
        "logConfiguration": {
            "logDriver": "awslogs",
            "options": {
                "awslogs-group": "${project}-${environment}-nginx",
                "awslogs-region": "us-east-1",
                "awslogs-stream-prefix": "nginx/"
            }
        }
    }
]

