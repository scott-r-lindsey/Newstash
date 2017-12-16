#!/bin/bash

#------------------------------------------------------------------------------
set -o errexit
set -o nounset
set -o pipefail
#------------------------------------------------------------------------------

function start_red {
    tput setaf 1
}
function start_green {
    tput setaf 2
}
function start_yellow {
    tput setaf 3
}
function start_blue {
    tput setaf 4
}
function start_purple {
    tput setaf 5
}
function start_teal {
    tput setaf 6
}
function start_white {
    tput setaf 7
}
function start_amber {
    tput setaf 8
}

function red {
    start_red
    echo -e "$1"
    end_color
}
function green {
    start_green
    echo -e "$1"
    end_color
}
function yellow {
    start_yellow
    echo -e "$1"
    end_color
}
function blue {
    start_blue
    echo -e "$1"
    end_color
}
function purple {
    start_purple
    echo -e "$1"
    end_color
}
function teal {
    start_teal
    echo -e "$1"
    end_color
}
function white {
    start_white
    echo -e "$1"
    end_color
}
function amber {
    start_amber
    echo -e "$1"
    end_color
}

function end_color {
    tput sgr0
}

