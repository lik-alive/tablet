#!/bin/bash
### COMMANDS
# <service_name> - join to a container
# <service_name> --sh - join to a container with /bin/sh (default /bin/bash)
#
# <command> --prod - for a production assembly
###

name=tablet
args=("$@")

# Check key in arguments
has_key() {
  for arg in "${args[@]}"; do
    if [[ $arg == $1 ]]; then return 0; fi
  done
  return 1
}

# Set prod or dev mode
has_key "--prod" && mode='prod' || mode='dev'

# Set name prefix
prefix="$mode"_"$name"

# Load env variables
source server/.env.$mode

#--- Join to a container in $1 /bin/sh
if has_key "--sh"; then
  docker exec -it "$prefix"_$1 /bin/sh

#--- Join to a container in $1
else 
  docker exec -it "$prefix"_$1 /bin/bash
fi