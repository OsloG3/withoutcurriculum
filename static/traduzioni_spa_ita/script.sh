#!/bin/bash

# Loop through files in the current directory
for file in *; do
  # Skip if it's not a regular file
  [ -f "$file" ] || continue

  # Create new filename by removing spaces, ( and )
  newfile=$(echo "$file" | tr -d ' ()')

  # Only rename if the name actually changes
  if [[ "$file" != "$newfile" ]]; then
    echo "Renaming: '$file' -> '$newfile'"
    mv -- "$file" "$newfile"
  fi
done
