#!/bin/bash

optTemplate="$1"
optAuthor="$2"
optWebsiteUrl="$3"
optDonationUrl="$4"

DIR=$(dirname "$(readlink -f "$0")")
DIR=$(echo "$DIR" | sed -r 's#/scripts$##')


if [ "$optTemplate" = "" ] ; then

  echo

  if ! (ls -1qA "$DIR/plugin-templates" | grep -q .) 2>/dev/null; then
    echo -e "Error: Plugin Templates Are Missing\n"
    read -n1 -p "Press any key to continue..." input ; echo
    exit
  fi

  index=1
  files=$(find "$DIR/plugin-templates" -maxdepth 1 -name '*')
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

if [ "$optAuthor" = "" ] ; then
  read -p "Author: " -e optAuthor
fi

if [ "$optWebsiteUrl" = "" ] ; then
  read -p "Website: https://" -e optWebsiteUrl
  optWebsiteUrl="https://$optWebsiteUrl"
fi

if [ "$optDonationUrl" = "" ] ; then
  read -p "Donation Link: https://" -e optDonationUrl
  optDonationUrl="https://$optDonationUrl"
fi


mkdir -p "$DIR/templates"
unzip -o "$DIR/plugin-templates/$optTemplate.zip" -d "$DIR/templates/$optTemplate" &>/dev/null


AuthorName=$(echo "${optAuthor^}" | sed -r 's/[_ -]([a-zA-Z0-9])/\u\1/g')
authorName="${AuthorName,}"
AuthorSlug="${AuthorName,,}"


optWebsiteUrl=$(echo "$optWebsiteUrl" | sed -r 's#^https://!#http://#')
optWebsiteUrl=$(echo "$optWebsiteUrl" | sed -r 's#^(https?://)\.#\1www.#')

optDonationUrl=$(echo "$optDonationUrl" | sed -r 's#^https://!#http://#')

websiteDomain=$(echo "$optWebsiteUrl" | sed -r 's#^https?://(.*?\.|)(.*?\..*?)#\2#')
websiteUrl=$(echo "$optWebsiteUrl" | sed -r 's#^https?://(.*?)(/.*?|)#\1#')
optDonationUrl=$(echo "$optDonationUrl" | sed -r "s#\.\$#.${websiteDomain}#")
optDonationUrl=$(echo "$optDonationUrl" | sed -r "s#^(https?://)\.(/.*?|)#\1${websiteUrl}\2#")

optDonationUrl=$(echo "$optDonationUrl" | sed -r 's#(bmac|coffee)!(\..*?|)$#buymeacoffee\2#')


optWebsiteUrl=$(echo "$optWebsiteUrl" | sed -r "s#(!plugin!|p!)#X_PLUGIN_SLUG_X#")
optWebsiteUrl=$(echo "$optWebsiteUrl" | sed -r "s#(!author!|a!)#X_AUTHOR_SLUG_X#")

optDonationUrl=$(echo "$optDonationUrl" | sed -r "s#(!plugin!|p!)#X_PLUGIN_SLUG_X#")
optDonationUrl=$(echo "$optDonationUrl" | sed -r "s#(!author!|a!)#X_AUTHOR_SLUG_X#")


files=$(find "$DIR/templates/$optTemplate/trunk" -name '*')
while read -r file; do
  if [[ ("$file" == *.php) || ("$file" == */readme.txt) ]] ; then
    [ -f "$file" ] || continue
    sed -r -i "s#X_WEBSITE_URL_X#${optWebsiteUrl}#g" "$file"
    sed -r -i "s#X_DONATION_URL_X#${optDonationUrl}#g" "$file"

    sed -r -i "s#X_AUTHOR_NAME_X#${AuthorName}#g" "$file"
    sed -r -i "s#X_AUTHOR_NAME_VAR_X#${authorName}#g" "$file"
    sed -r -i "s#X_AUTHOR_SLUG_X#${AuthorSlug}#g" "$file"
  fi
done <<< "$files"

files=$(find "$DIR/templates/${optTemplate}/templates" -name '*')
while read -r file; do
  if [[ "$file" == *.php ]] ; then
    [ -f "$file" ] || continue
    sed -r -i "s#X_WEBSITE_URL_X#${optWebsiteUrl}#g" "$file"
    sed -r -i "s#X_DONATION_URL_X#${optDonationUrl}#g" "$file"

    sed -r -i "s#X_AUTHOR_NAME_X#${AuthorName}#g" "$file"
    sed -r -i "s#X_AUTHOR_NAME_VAR_X#${authorName}#g" "$file"
    sed -r -i "s#X_AUTHOR_SLUG_X#${AuthorSlug}#g" "$file"
  fi
done <<< "$files"


cd "$DIR/templates/$optTemplate"
zip -r -D "../$optTemplate.zip" . &>/dev/null
cd "$DIR"

rm -r "$DIR/templates/$optTemplate"
