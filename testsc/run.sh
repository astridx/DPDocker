#!/bin/bash
# @package   DPDocker
# @copyright Copyright (C) 2020 Digital Peak GmbH. <https://www.digital-peak.com>
# @license   http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL

if [[ ! $(command -v curl) ]]; then
  echo "Error: curl is not installed, can't run the tests!"
  exit 1
fi

# load .env
SCRIPTPATH="$( cd "$(dirname "$0")" >/dev/null 2>&1 ; pwd -P )"
. $SCRIPTPATH/.env 

# Clear data when running all tests
if [ $REBUILD == "true" ]; then
  # Remove the containers
  docker container rm -f $(docker container ls -q --filter name=testsc_*) > /dev/null 2>&1

  # Cleanup data dirs
  sudo rm -rf $(dirname $0)/mysql_data
  sudo rm -rf $(dirname $0)/www
  mkdir $(dirname $0)/www

  # We start mysql early to rebuild the database
  docker-compose -f $(dirname $0)/docker-compose.yml up -d mysql-test
  sleep 5
fi

# Run containers in detached mode so when the system tests command ends, we can stop them afterwards
docker-compose -f $(dirname $0)/docker-compose.yml up -d phpmyadmin-test
docker-compose -f $(dirname $0)/docker-compose.yml up -d mailcatcher-test
docker-compose -f $(dirname $0)/docker-compose.yml up -d web-test
docker-compose -f $(dirname $0)/docker-compose.yml up -d selenium-test

# Waiting for web server
#while ! curl http://localhost:8080 > /dev/null 2>&1; do
  echo "$(date) - waiting for web server"
 # sleep 4
#done

# Waiting for selenium server
#while ! curl http://localhost:4444 > /dev/null 2>&1; do
#  echo "$(date) - waiting for selenium server"
#  sleep 4
# done

# Run VNC viewer
#if [[ $(command -v vinagre) ]]; then
 # vinagre localhost > /dev/null 2>&1 &
#fi

# Run the tests
docker-compose -f $(dirname $0)/docker-compose.yml run system-tests

# Stop the containers
# if [ -z $t ]; then
  docker container stop $(docker container ls -q --filter name=tests_*) > /dev/null 2>&1
# fi
