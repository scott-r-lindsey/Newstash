
variable environment                        { }
variable project                            { }
variable image                              { }

#------------------------------------------------------------------------------

resource "aws_ecr_repository" "the-ecr-repository" {
    name = "${var.project}-${var.image}-${var.environment}"
}

#------------------------------------------------------------------------------

output "arn" {
    value = "aws_ecr_repository.the-ecr-repository.arn"
}
output "name" {
    value = "aws_ecr_repository.the-ecr-repository.name"
}
output "registry_id" {
    value = "aws_ecr_repository.the-ecr-repository.registry_id"
}
output "repository_url" {
    value = "aws_ecr_repository.the-ecr-repository.repository_url"
}
