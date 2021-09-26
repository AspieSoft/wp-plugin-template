#!/bin/bash

echo "Wordpress Plugin Template by AspieSoft"

DIR=$(dirname "$(readlink -f "$0")")
DIR=$(echo "$DIR" | sed -r 's#/scripts$##')


function cleanup() {
  unset input
  unset -f main
  unset DIR
}
trap cleanup EXIT


function main() {

  echo
  echo "[0] Exit"
  echo "[1] Init Template"
  echo "[2] Init Plugin"
  echo "[3] Build Plugin"
  echo "[4] Add File"
  echo "[5] Delete Plugin"
  echo
  read -p "What would you like to do? " -e input

  if [ -z "$input" ] ; then
    exit
  fi

  if [ "$input" -eq "0" ] ; then
    exit
  elif [ "$input" -eq "1" ] ; then
    bash "$DIR/scripts/init-author.sh"
  elif [ "$input" -eq "2" ] ; then
    bash "$DIR/scripts/init.sh"
  elif [ "$input" -eq "3" ] ; then
    bash "$DIR/scripts/build.sh"
  elif [ "$input" -eq "4" ] ; then
    bash "$DIR/scripts/add-file.sh"
  elif [ "$input" -eq "5" ] ; then
    bash "$DIR/scripts/delete-plugin.sh"
  else
    exit
  fi

  main
}

main
