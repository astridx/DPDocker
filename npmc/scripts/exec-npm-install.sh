#!/bin/bash
# @package   DPDocker
# @copyright Copyright (C) 2020 Digital Peak GmbH. <https://www.digital-peak.com>
# @license   http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL

echo "START-----------/home/astrid/DP/DPDocker/npmc/scripts/exec-npm-install.sh-------------START"

root=$3
if [ -z $root ]; then
  root=$(realpath $(dirname $0)/../../../)
fi

# Link host directory as npm dir for caching
rm -rf /home/docker/.npm

if [ ! -d /usr/src/Projects/DPDocker/npmc/tmp ]; then
  mkdir /usr/src/Projects/DPDocker/npmc/tmp
fi

ln -s /usr/src/Projects/DPDocker/composerc/tmp /home/docker/.npm

echo "Cleaning up in $root/$1/$2 the assets"
sudo find $root/$1/$2 -path "*/media/css" -type d -exec rm -rf {} \; &>/dev/null
sudo find $root/$1/$2 -path "*/media/js" -type d -exec rm -rf {} \; &>/dev/null

echo "Started to install and build the assets for $root/$1!"

# Loop over manifest files
for fname in $(find $root/$1/$2 -path ./node_modules -prune -o -name "package.json" 2>/dev/null); do
  # Exclude the files in node_modules directories
  if [[ $fname == *"node_modules"* ]]; then
    continue
  fi

  projectDirectory=$(dirname $fname)

  cd $projectDirectory

  echo "---Installing $(dirname ${fname#"$root/"})!"
  echo "-projektidr--"
  echo $projectDirectory
  echo "---"
  echo "--root1-"
  echo $root/$1
  echo "---"

  # Remove the vendors directory
  if [ -d "$projectDirectory/node_modules" ]; then
    sudo rm -rf "$projectDirectory/node_modules"
  fi

  echo "--npm cache verify-"
  echo "---"
  npm cache verify

  echo "--npm install ci-"
  echo "---"
  # Install the dependencies
  npm install ci

  if [ -z $3 ]; then
    echo "Outdated packages"
    npm outdated
  fi

  $(dirname $0)/exec-build.sh $root/$1 all

  echo "Finished installing $(dirname ${fname#"$root/"})!"
done

echo "ENDE-----------/home/astrid/DP/DPDocker/npmc/scripts/exec-npm-install.sh-------------ENDE"
