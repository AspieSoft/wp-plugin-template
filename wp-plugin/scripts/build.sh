#!/bin/bash

optVer="$1"
optChanges="$2"

DIR=$(dirname "$(readlink -f "$0")")
DIR=$(echo "$DIR" | sed -r 's#/scripts$##')


if [ "$optVer" = "" ] ; then
  read -p "Version: " -e optVer
fi

if [ "$optChanges" = "" ] ; then
  function addChange() {
    read -p "Changes: " -e change
    if ! [ "$change" = "" ] ; then
      optChanges="$optChanges\n$change"
      addChange
    fi
  }
  addChange
fi


verStrict=$(echo "$optVer" | sed -r 's/[^0-9\.]//g')
verStrict=$(echo "$verStrict" | sed -r 's/^[^0-9]+([0-9]+)(\.[0-9]+|)(\.[0-9]+|).*$/\1\2\3/')
verStrict=$(echo "$verStrict" | sed -r 's/^([0-9]+\.[0-9]+)$/\1.0/')
verStrict=$(echo "$verStrict" | sed -r 's/^([0-9]+)$/\1.0.0/')


files=$(find "$DIR/wp-plugin/trunk" -name '*')
while read -r file; do
  if [[ ("$file" == *.php) || ("$file" == */readme.txt) ]] ; then
    [ -f "$file" ] || continue

    fileName=$(echo "$file" | sed -r "s#^${DIR}/wp-plugin/trunk/##g")

    if [[ ("$fileName" != */*) && ("$fileName" == *.php) ]] ; then
      if [ "$fileName" != "index.php" ] && [ "$fileName" != "functions.php" ] ; then
        pluginName=$(echo "$fileName" | sed -r 's#\.php$##')
      fi
    fi

    if [ "$fileName" = "readme.txt" ] && [ "$optChanges" != "" ] && [ "$optChanges" != " " ] && [ "$optChanges" != "-" ] && [ "$optChanges" != "." ] && [ "$optChanges" != "*" ] ; then
      sed -r -i "s#^(== Changelog ==)\$#\1\n\n= ${optVer} =${optChanges}#m" "$file"
      sed -r -i "s#^(== Upgrade Notice ==)\$#\1\n\n= ${optVer} =${optChanges}#m" "$file"
    fi
  fi
done <<< "$files"


mkdir -p "$DIR/tmp" && cp -Rf "$DIR/wp-plugin/trunk" "$DIR/tmp" &>/dev/null


files=$(find "$DIR/tmp/trunk" -name '*')
while read -r file; do
  if [[ ("$file" == *.php) || ("$file" == */readme.txt) ]] ; then
    [ -f "$file" ] || continue
    sed -r -i "s#X_PLUGIN_VERSION_X#${optVer}#g" "$file"
    sed -r -i "s#X_PLUGIN_VERSION_STRICT_X#${verStrict}#g" "$file"
  fi
done <<< "$files"


cd "$DIR/tmp/trunk"
zip -r -D "../../wp-plugin/$pluginName.zip" . &>/dev/null
cd "$DIR"


rm -r "$DIR/tmp"
