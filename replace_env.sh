#!/bin/bash

while IFS='' read -r line || [[ -n "$line" ]]; do
    key=$( echo "$line" | sed 's/[A-Z0-9_]*={{\([A-Z0-9_]*\)}}.*$/\1/' )
    replaced=$( echo "$line" | sed "s/{{[A-Z0-9_]*}}/${!key}/g")
    echo "$replaced" >> "$2"
done < "$1"