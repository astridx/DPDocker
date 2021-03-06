#!/bin/bash
# @package   DPDocker
# @copyright Copyright (C) 2020 Digital Peak GmbH. <https://www.digital-peak.com>
# @license   http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL

if [[ ! $(command -v curl) ]]; then
  echo "Error: curl is not installed, can't run the testsc!"
  exit 1
fi

# Clear mysql and www data when running all testsc
if [ -z $t ]; then
  docker container rm -f $(docker container ls -q --filter name=testsc_*) > /dev/null 2>&1
  sudo rm -rf $(dirname $0)/mysql_data
  if [ ! -d $(dirname $0)/www ]; then
    mkdir $(dirname $0)/www
  fi
  if [ -d $(dirname $0)/www/joomla3 ]; then
    sudo rm -rf $(dirname $0)/www/joomla3
  fi
  if [ -d $(dirname $0)/www/joomla4 ]; then
    sudo rm -rf $(dirname $0)/www/joomla4
  fi
  docker-compose --env-file ../.env -f $(dirname $0)/docker-compose.yml up -d mysql-test
  sleep 15
fi

# Run containers in detached mode so when the system testsc command ends, we can stop them afterwards
docker-compose --env-file ../.env -f $(dirname $0)/docker-compose.yml up -d phpmyadmin-test
docker-compose --env-file ../.env -f $(dirname $0)/docker-compose.yml up -d mailcatcher-test
docker-compose --env-file ../.env -f $(dirname $0)/docker-compose.yml up -d selenium-test
docker-compose --env-file ../.env -f $(dirname $0)/docker-compose.yml up -d web-test

# Waiting for web server
while ! curl http://localhost:8080 > /dev/null 2>&1; do
  echo "$(date) - waiting for web server"
  sleep 4
done

# Waiting for selenium server
while ! curl http://localhost:4444 > /dev/null 2>&1; do
  echo "$(date) - waiting for selenium server"
  sleep 4
done

# Run VNC viewer
#if [[ $(command -v vinagre) ]]; then
#  vinagre localhost > /dev/null 2>&1 &
#fi

docker-compose --env-file ../.env -f $(dirname $0)/docker-compose.yml run system-tests

# Stop the containers
if [ -z $t ]; then
  docker container stop $(docker container ls -q --filter name=testsc_*) > /dev/null 2>&1
fi
