#!/bin/bash

optTemplate="$1"
optPluginName="$2"

DIR=$(dirname "$(readlink -f "$0")")
DIR=$(echo "$DIR" | sed -r 's#/scripts$##')


if [ "$optTemplate" = "" ] ; then

  echo

  if ! (ls -1qA "$DIR/templates" | grep -q .) 2>/dev/null; then
    echo -e "Error: You Must Initialize A Template First\n"
    read -n1 -p "Press any key to continue..." input ; echo
    exit
  fi

  index=1
  files=$(find "$DIR/templates" -maxdepth 1 -name '*')
  while read -r file; do
    if [[ "$file" == *.zip ]] ; then
      fileName=$(echo "$file" | sed -r 's#^.*?/(.*)\.zip$#\1#')
      echo "[$index]" $(echo "${fileName^}" | sed -r 's/[_ -]([a-zA-Z0-9])/ \u\1/g')
      index=$(($index + 1))
    fi
  done <<< "$files"

  echo

  read -p "What template would you like to use? " -e optTemplate

  if [ -n "$optTemplate" ] && [ "$optTemplate" -eq "$optTemplate" ] 2>/dev/null; then
    ind=1
    while read -r file; do
      if [[ "$file" == *.zip ]] ; then
        if [ "$ind" -eq "$optTemplate" ] ; then
          optTemplate=$(echo "$file" | sed -r 's#^.*?/(.*)\.zip$#\1#')
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

if [ "$optPluginName" = "" ] ; then
  read -p "Plugin Name: " -e optPluginName
fi


mkdir -p "$DIR/tmp" && cp -Rf "$DIR/wp-plugin/trunk/src" "$DIR/tmp" &>/dev/null
unzip -o "$DIR/templates/$optTemplate.zip" -d "$DIR/wp-plugin" &>/dev/null
cp -Rf "$DIR/tmp/src/." "$DIR/wp-plugin/trunk/src" &>/dev/null


pluginClassName=$(echo "${optPluginName^}" | sed -r 's/[_ -]([a-zA-Z0-9])/\u\1/g')

pluginSlugName=$(echo "${optPluginName,,}" | sed -r 's/[^A-Za-z0-9\-_ ].*$//g')
pluginSlugName=$(echo "${pluginSlugName,,}" | sed -r 's/[_ ]/-/g')
pluginSlugName=$(echo "${pluginSlugName,,}" | sed -r 's/--+/-/g')
pluginSlugName=$(echo "${pluginSlugName,,}" | sed -r 's/^-|-$//g')


files=$(find "$DIR/wp-plugin/trunk" -name '*')
while read -r file; do
  if [[ ("$file" == *.php) || ("$file" == */readme.txt) ]] ; then
    [ -f "$file" ] || continue
    sed -r -i "s#X_PLUGIN_DISPLAY_NAME_X#${optPluginName}#g" "$file"
    sed -r -i "s#X_PLUGIN_NAME_X#${pluginClassName}#g" "$file"
    sed -r -i "s#X_PLUGIN_SLUG_X#${pluginSlugName}#g" "$file"
  fi
done <<< "$files"

files=$(find "$DIR/wp-plugin/templates" -name '*')
while read -r file; do
  if [[ ("$file" == *.php) || ("$file" == */readme.txt) ]] ; then
    [ -f "$file" ] || continue
    sed -r -i "s#X_PLUGIN_DISPLAY_NAME_X#${optPluginName}#g" "$file"
    sed -r -i "s#X_PLUGIN_NAME_X#${pluginClassName}#g" "$file"
    sed -r -i "s#X_PLUGIN_SLUG_X#${pluginSlugName}#g" "$file"
  fi
done <<< "$files"

mv "$DIR/wp-plugin/trunk/$optTemplate.php" "$DIR/wp-plugin/trunk/$pluginSlugName.php" 


rm -r "$DIR/tmp"
