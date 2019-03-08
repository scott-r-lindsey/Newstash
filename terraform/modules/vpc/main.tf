
variable environment                        { }
variable project                            { }

variable public_azs                         { type = "list" }
variable public_cidrs                       { type = "list" }

variable vpc_cidr                           { }

#------------------------------------------------------------------------------

resource "aws_vpc" "the-aws-vpc" {
    cidr_block = "${var.vpc_cidr}"

    tags {
        Name = "${var.project}-${var.environment}-vpc"
    }
}
resource "aws_internet_gateway" "the-aws-internet-gateway" {
    vpc_id = "${aws_vpc.the-aws-vpc.id}"

    tags {
        Name = "${var.project}-${var.environment}-internet-gateway"
    }
}


// public subnets --------------------------------------------------------------
resource "aws_subnet" "the-public-subnet" {
    count               = "${length(var.public_cidrs)}"

    vpc_id              = "${aws_vpc.the-aws-vpc.id}"

    cidr_block          = "${element(var.public_cidrs, count.index)}"
    availability_zone   = "${element(var.public_azs, count.index)}"

    map_public_ip_on_launch = true

    tags {
        Name = "${var.project}-${var.environment}-public-subnet-${count.index}"
    }
}

resource "aws_route_table" "the-public-route-table" {
    count               = "${length(var.public_cidrs)}"

    vpc_id = "${aws_vpc.the-aws-vpc.id}"

    route {
        cidr_block = "0.0.0.0/0"
        gateway_id = "${aws_internet_gateway.the-aws-internet-gateway.id}"
    }

    tags {
        Name = "${var.project}-${var.environment}-route-table"
    }
}

resource "aws_route_table_association" "the-public-route-table-assoc" {
    count               = "${length(var.public_cidrs)}"

    subnet_id = "${element(aws_subnet.the-public-subnet.*.id, count.index)}"
    route_table_id = "${element(aws_route_table.the-public-route-table.*.id, count.index)}"
}


#------------------------------------------------------------------------------

output "vpc_id" {
    value = "${aws_vpc.the-aws-vpc.id}"
}
output "public_subnet_ids" {
    value = "${aws_subnet.the-public-subnet.*.id}"
}
