#!/bin/bash
DIR=$(dirname "$(readlink -f "$0")")
bash "$DIR/scripts/run.sh"
