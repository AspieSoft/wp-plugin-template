#!/bin/bash
DIR=$(dirname "$(readlink -f "$0")")
bash "$DIR/wp-plugin/scripts/run.sh"
