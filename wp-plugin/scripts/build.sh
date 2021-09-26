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
verStrict=$(echo "$verStrict" | sed -r 's/^.*?([0-9]+)(\.[0-9]+|)(\.[0-9]+|).*$/\1\2\3/')
verStrict=$(echo "$verStrict" | sed -r 's/^([0-9]+\.[0-9]+)$/\1.0/')
verStrict=$(echo "$verStrict" | sed -r 's/^([0-9]+)$/\1.0.0/')


files=$(find "$DIR/wp-plugin/trunk" -name '*')
while read -r file; do
  if [[ ("$file" == *.php) || ("$file" == */readme.txt) ]] ; then
    [ -f "$file" ] || continue
    sed -r -i "s#X_PLUGIN_VERSION_X#${optVer}#g" "$file"
    sed -r -i "s#X_PLUGIN_VERSION_STRICT_X#${verStrict}#g" "$file"

    fileName=$(echo "$file" | sed -r "s#^${DIR}/wp-plugin/trunk/##g")

    if [[ ("$fileName" != */*) && ("$fileName" == *.php) ]] ; then
      if [ "$fileName" != "index.php" ] && [ "$fileName" != "functions.php" ] ; then
        pluginName=$fileName
      fi
    fi

    if [ "$fileName" = "readme.txt" ] && [ "$optChanges" != "" ] && [ "$optChanges" != " " ] && [ "$optChanges" != "-" ] && [ "$optChanges" != "." ] && [ "$optChanges" != "*" ] ; then
      sed -r -i "s#^(== Changelog ==)\$#\1\n\n= ${optVer} =\n${optChanges}\n#m" "$file"
      sed -r -i "s#^(== Upgrade Notice ==)\$#\1\n\n= ${optVer} =\n${optChanges}\n#m" "$file"
    fi
  fi
done <<< "$files"

files=$(find "$DIR/wp-plugin/templates" -name '*')
while read -r file; do
  if [[ ("$file" == *.php) || ("$file" == */readme.txt) ]] ; then
    [ -f "$file" ] || continue
    sed -r -i "s#X_PLUGIN_VERSION_X#${optVer}#g" "$file"
    sed -r -i "s#X_PLUGIN_VERSION_STRICT_X#${verStrict}#g" "$file"
  fi
done <<< "$files"


cd "$DIR/wp-plugin/trunk"
zip -r -D "../$pluginName.zip" . &>/dev/null
cd "$DIR"
