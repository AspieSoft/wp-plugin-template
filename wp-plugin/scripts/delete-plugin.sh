#!/bin/bash

DIR=$(dirname "$(readlink -f "$0")")
DIR=$(echo "$DIR" | sed -r 's#/scripts$##')

echo
echo "WARNING! You Are About To Delete The Entire Plugin!"
echo
echo "This option is intended for template developers for deleting the plugin after testing!"
echo "If you do not want to Delete Your Entire Plugin, Leave Now!"
echo

read -p "Type 'YES' To Confirm and DELETE the entire plugin: " -e input

if ! [ "$input" = "YES" ] ; then
  exit
fi

echo
echo "Are You Sure You Want To Delete This Plugin?"
echo
echo "This Cannot Be Undone!"
echo
read -p "Type 'DELETE' To Continue: " -e input

if ! [ "$input" = "DELETE" ] ; then
  exit
fi

# delete plugin
rm -r "$DIR/tmp"
rm -r "$DIR/templates"
rm -r "$DIR/wp-plugin"
