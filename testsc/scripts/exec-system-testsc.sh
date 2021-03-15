#!/bin/bash
# @package   DPDocker
# @copyright Copyright (C) 2020 Digital Peak GmbH. <https://www.digital-peak.com>
# @license   http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL

echo "Running testsc on Joomla $2 with $3"

# Setup download dir with correct permissions
sudo rm -rf /tmp/tests/*
sudo chmod 777 /tmp/tests

# Make sure the dependencies are correct
cd $(dirname $0)/../config/j3
composer install --quiet
cd $(dirname $0)/../config/j4
composer install --quiet

# Change to the testsc folder as working directory
rm -rf $(dirname $0)/../tmp
mkdir $(dirname $0)/../tmp
cd $(dirname $0)/../tmp
cp -r /extension/tests/* .
cp -r $(dirname $0)/../config/j$2/* .
sed -i "s/{BROWSER}/$3/" acceptance.suite.yml

# Build the actions class and copy it back
vendor/bin/codecept build
mkdir -p /extension/tests/_support/_generated
cp -f $(dirname $0)/../tmp/_support/_generated/AcceptanceTesterActions.php /extension/tests/_support/_generated/AcceptanceTesterActions.php


# todo if zip need to copy
cp -r $(dirname $0)/../../buildc/dist/pkg_system_agscsscompiler_1_0.zip $(dirname $0)/../www/joomla4/pkg_system_agscsscompiler_1_0.zip
cp -r $(dirname $0)/../../buildc/dist/pkg_system_agscsscompiler_1_0.zip $(dirname $0)/../www/joomla3/pkg_system_agscsscompiler_1_0.zip

# Check if there are multiple testsc to run
if [[ ! -z $4 && $4 != *".php:"* ]]; then
  vendor/bin/codecept run --env desktop ${4#"tests/"}
  exit 1
fi

# Check if there is a single test to run
if [ ! -z $4 ]; then
  vendor/bin/codecept run --debug --steps --env desktop ${4#"tests/"}
  exit 1
fi

if [ -d acceptance/install ]; then
  # Run the install task first
  vendor/bin/codecept run --env desktop acceptance/install

  # Remove the install testsc, so they wont be executed again
  rm -rf acceptance/install
fi

# Run the testsc
vendor/bin/codecept run --env desktop --ext "Codeception\ProgressReporter\ProgressReporter" acceptance