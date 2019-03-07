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
            {
                "name": "MONGODB_URL",
                "value": "${mongodb_url}"
            },
            {
                "name": "MONGODB_DB",
                "value": "${mongodb_db}"
            },
            {
                "name": "MAILER_URL",
                "value": "${mailer_url}"
            },
            {
                "name": "APP_SECRET",
                "value": "${app_secret}"
            },
            {
                "name": "DATABASE_URL",
                "value": "${database_url}"
            },
            {
                "name": "AWS_REGION",
                "value": "${aws_region}"
            },
            {
                "name": "AWS_ASSOCIATE_TAG",
                "value": "${amzn_affiliate}"
            },
            {
                "name": "FACEBOOK_APP_ID",
                "value": "${facebook_app_id}"
            },
            {
                "name": "FACEBOOK_SECRET",
                "value": "${facebook_secret}"
            },
            {
                "name": "GOOGLE_CLIENT_ID",
                "value": "${google_client_id}"
            },
            {
                "name": "GOOGLE_CLIENT_SECRET",
                "value": "${google_client_secret}"
            },
            {
                "name": "GAQ_ID",
                "value": "${gaq_id}"
            }
        ],
        "essential": true,
        "image": "${aws_account_id}.dkr.ecr.${region}.amazonaws.com/${project}-php-${environment}:${app_version}",
        "memoryReservation": 512,
        "name": "php",
        "logConfiguration": {
            "logDriver": "awslogs",
            "options": {
                "awslogs-group": "${php-log-group}",
                "awslogs-region": "${region}",
                "awslogs-stream-prefix": "php"
            }
        }
    },
    {
        "environment": [
            {
                "name": "RUNTIME",
                "value": "fargate"
            }
        ],
        "essential": true,
        "image": "${aws_account_id}.dkr.ecr.${region}.amazonaws.com/${project}-nginx-${environment}:${app_version}",
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
                "awslogs-group": "${nginx-log-group}",
                "awslogs-region": "us-east-1",
                "awslogs-stream-prefix": "nginx"
            }
        }
    }
]
