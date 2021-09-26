#!/bin/bash

optTemplate="$1"
optFileName="$2"

DIR=$(dirname "$(readlink -f "$0")")
DIR=$(echo "$DIR" | sed -r 's#/scripts$##')


if [ "$optTemplate" = "" ] ; then

  echo

  if ! (ls -1qA "$DIR/wp-plugin/templates" | grep -q .) 2>/dev/null; then
    echo -e "Error: This Plugin Doesn't Have Any File Templates\n"
    read -n1 -p "Press any key to continue..." input ; echo
    exit
  fi

  index=1
  files=$(find "$DIR/wp-plugin/templates" -maxdepth 1 -name '*')
  while read -r file; do
    if ! [ "$file" = "$DIR/wp-plugin/templates" ] ; then
      echo "[$index]" $(echo "$file" | sed -r 's#^.*?/(.*)$#\1#')
      index=$(($index + 1))
    fi
  done <<< "$files"

  echo

  read -p "What template would you like to use? " -e optTemplate

  if [ -n "$optTemplate" ] && [ "$optTemplate" -eq "$optTemplate" ] 2>/dev/null; then
    ind=1
    while read -r file; do
      if ! [ "$file" = "$DIR/wp-plugin/templates" ] ; then
        if [ "$ind" -eq "$optTemplate" ] ; then
          optTemplate=$(echo "$file" | sed -r 's#^.*?/(.*)$#\1#')
          break
        fi
        ind=$(($ind + 1))
      fi
    done <<< "$files"
  fi

  echo -e "Using: $optTemplate\n"

  unset index
  unset ind
  unset template
fi

if [ "$optFileName" = "" ] ; then
  read -p "File Name: " -e optFileName
fi


fileExt=$(echo "${optTemplate^}" | sed -r 's/.*?\.([a-zA-Z0-9]+)/\1/g')

if [[ "$optFileName" != *.$fileExt ]] ; then
  optFileName="$optFileName.$fileExt"
fi

DEST=$(dirname "$DIR/wp-plugin/trunk/src/$optFileName")

mkdir -p "$DIR/tmp" && cp -n "$DIR/wp-plugin/templates/$optTemplate" "$DIR/tmp"

if [[ "$optTemplate" == *.php ]] ; then
  FileClass=$(echo "${optFileName^}" | sed -r 's/[_ -]([a-zA-Z0-9])/\u\1/g')
  FileClass=$(echo "$FileClass" | sed -r 's/\.[a-zA-Z0-9]+$//g')
  sed -r -i "s#X_FILECLASS_X#${FileClass}#g" "$DIR/tmp/$optTemplate"
fi

optFileName=$(echo "$optFileName" | sed -r 's/([A-Z])/-\1/g')
optFileName=$(echo "${optFileName,,}" | sed -r 's/[_ ]/-/g')
optFileName=$(echo "$optFileName" | sed -r 's/--+/-/g')
optFileName=$(echo "$optFileName" | sed -r 's/^-+|-+$//g')

mkdir -p "$DEST" && mv -n "$DIR/tmp/$optTemplate" "$DIR/wp-plugin/trunk/src/$optFileName"

rm -r "$DIR/tmp"
