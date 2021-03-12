#!/bin/bash
# @package   DPDocker
# @copyright Copyright (C) 2020 Digital Peak GmbH. <https://www.digital-peak.com>
# @license   http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL

## load .env
#ENVPATH="$( cd "$(dirname "$0")" >/dev/null 2>&1 ; pwd -P )"
#. $ENVPATH/../.env 

# Create the www directory as the current user. So all subdirs will inherit the permissions.
#if [ ! -d $WEBBASEDIR ]; then
# mkdir $WEBBASEDIR
#fi

# Create the www directory as the current user. So all subdirs will inherit the permissions.
if [ ! -d $(dirname $0)/www ]; then
  mkdir $(dirname $0)/www
fi

# Start the dev server
docker-compose --env-file ../.env -f $(dirname $0)/docker-compose.yml up
