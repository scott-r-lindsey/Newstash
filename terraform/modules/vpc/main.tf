
variable environment                        { }
variable project                            { }

variable public_az                          { }
variable private_azs                        { type = "list" }

variable vpc_cidr                           { }
variable public_cidr                        { }
variable private_cidrs                      { type = "list" }

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


// public subnet --------------------------------------------------------------
resource "aws_subnet" "the-public-subnet" {
    vpc_id              = "${aws_vpc.the-aws-vpc.id}"
    cidr_block          = "${var.public_cidr}"
    availability_zone   = "${var.public_az}"

    tags {
        Name = "${var.project}-${var.environment}-public-subnet"
    }
}

resource "aws_route_table" "the-public-route-table" {
    vpc_id = "${aws_vpc.the-aws-vpc.id}"

    route {
        cidr_block = "0.0.0.0/0"
        gateway_id = "${aws_internet_gateway.the-aws-internet-gateway.id}"
    }

    tags {
        Name = "${var.project}-${var.environment}-route-table"
    }
}

resource "aws_route_table_association" "the-route-table-assoc" {
    subnet_id = "${aws_subnet.the-public-subnet.id}"
    route_table_id = "${aws_route_table.the-public-route-table.id}"
}


// private subnets ------------------------------------------------------------
resource "aws_eip" "the-nat-elastic-ip" {
    vpc = true
}

resource "aws_nat_gateway" "the-nat-gw" {
    allocation_id = "${aws_eip.the-nat-elastic-ip.id}"
    subnet_id     = "${aws_subnet.the-public-subnet.id}"
}

resource "aws_subnet" "the-private-subnet" {
    count               = "${length(var.private_cidrs)}"

    vpc_id              = "${aws_vpc.the-aws-vpc.id}"

    cidr_block          = "${element(var.private_cidrs, count.index)}"
    availability_zone   = "${element(var.private_azs, count.index)}"

    tags {
        Name = "${var.project}-${var.environment}-private-subnet"
    }
}

resource "aws_route_table" "the-private-route-table" {
    count               = "${length(var.private_cidrs)}"

    vpc_id = "${aws_vpc.the-aws-vpc.id}"

    route {
        cidr_block = "0.0.0.0/0"
        nat_gateway_id = "${aws_nat_gateway.the-nat-gw.id}"
    }

    tags {
        Name = "${var.project}-${var.environment}-private-route-table-${count.index}"
    }
}

resource "aws_route_table_association" "the-private-route-table-assoc" {
    count               = "${length(var.private_cidrs)}"

    subnet_id = "${element(aws_subnet.the-private-subnet.*.id, count.index)}"
    route_table_id = "${element(aws_route_table.the-private-route-table.*.id, count.index)}"
}

#------------------------------------------------------------------------------

output "vpc_id" {
    value = "${aws_vpc.the-aws-vpc.id}"
}
output "public_subnet_id" {
    value = "${aws_subnet.the-public-subnet.id}"
}
output "public_subnet_arn" {
    value = "${aws_subnet.the-public-subnet.arn}"
}
output "private_subnet_ids" {
    value = "${aws_subnet.the-private-subnet.*.id}"
}
output "private_subnet_arns" {
    value = "${aws_subnet.the-private-subnet.*.arn}"
}
