
variable environment                        { }
variable project                            { }
variable az                                 { }
variable vpc_cidr                           { default = "10.0.0.0/16" }
variable public_cidr                        { default = "10.0.1.0/24" }
variable private_cidr                       { default = "10.0.2.0/24" }

#------------------------------------------------------------------------------

resource "aws_vpc" "the-aws-vpc" {
    cidr_block = "${var.vpc_cidr}"

    tags {
        Name = "${var.project}-${var.environment}-vpc"
    }
}

resource "aws_subnet" "the-public-subnet" {
    vpc_id              = "${aws_vpc.the-aws-vpc.id}"
    cidr_block          = "${var.public_cidr}"
    availability_zone   = "${var.az}"

    tags {
        Name = "${var.project}-${var.environment}-public-subnet"
    }
}

resource "aws_subnet" "the-private-subnet" {
    vpc_id              = "${aws_vpc.the-aws-vpc.id}"
    cidr_block          = "${var.private_cidr}"
    availability_zone   = "${var.az}"

    tags {
        Name = "${var.project}-${var.environment}-private-subnet"
    }
}

resource "aws_internet_gateway" "the-aws-internet-gateway" {
    vpc_id = "${aws_vpc.the-aws-vpc.id}"

    tags {
        Name = "${var.project}-${var.environment}-internet-gateway"
    }
}

# Define the route table
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
