#!/bin/bash
# @package   DPDocker
# @copyright Copyright (C) 2020 Digital Peak GmbH. <https://www.digital-peak.com>
# @license   http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL

# # load .env
SCRIPTPATH="$( cd "$(dirname "$0")" >/dev/null 2>&1 ; pwd -P )"
. $SCRIPTPATH/.env 

# echo $WEBBASEDIR1
# Create the www directory as the current user. So all subdirs will inherit the permissions.
if [ ! -d $WEBBASEDIR1 ]; then
  mkdir $WEBBASEDIR1
fi

# Start the dev server
docker-compose -f $(dirname $0)/docker-compose.yml up