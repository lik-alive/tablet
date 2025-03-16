#!/bin/bash
### COMMANDS
# <empty> [--prod] - start assembly
# restart <service_name> [--prod] - restart container for a specified service
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

#--- Restart single service
if has_key "restart"; then
  echo "Restart $2 $mode"
  docker-compose -f docker-compose."$mode".yml -p $prefix stop $2
  docker-compose -f docker-compose."$mode".yml --env-file server/.env."$mode" -p $prefix up -d --build $2

#--- Start assembly
else 
  echo "Start $mode"  
  if has_key "--prod"; then detached="-d"; fi
  docker-compose -f docker-compose."$mode".yml --env-file server/.env."$mode" -p $prefix up --remove-orphans --build $detached
fi