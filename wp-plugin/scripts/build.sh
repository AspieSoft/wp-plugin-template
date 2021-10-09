#!/bin/bash

optVer="$1"
optChanges="$2"

DIR=$(dirname "$(readlink -f "$0")")
DIR=$(echo "$DIR" | sed -r 's#/scripts$##')

FolderName=$(echo "$DIR" | sed -r 's#/wp-plugin$##')
FolderName=$(echo "$FolderName" | sed -r 's#.*/##')


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


rm -r "$DIR/../dist" &>/dev/null
mkdir -p "$DIR/../dist/wp-plugin" && cp -Rf "$DIR/wp-plugin/trunk" "$DIR/../dist/wp-plugin" &>/dev/null
cp -Rf "$DIR/wp-plugin/assets" "$DIR/../dist/wp-plugin" &>/dev/null


files=$(find "$DIR/../dist/wp-plugin/trunk" -name '*')
while read -r file; do
  if [[ ("$file" == *.php) || ("$file" == */readme.txt) ]] ; then
    [ -f "$file" ] || continue
    sed -r -i "s#X_PLUGIN_VERSION_X#${optVer}#g" "$file"
    sed -r -i "s#X_PLUGIN_VERSION_STRICT_X#${verStrict}#g" "$file"
  fi
done <<< "$files"


cd "$DIR/../dist/wp-plugin/trunk"

mv "$pluginName.php" "$FolderName.php"

zip -r -D "../../$FolderName.zip" . &>/dev/null
cd "$DIR"
