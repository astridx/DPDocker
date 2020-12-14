<?php
/**
 * @package   DPDocker
 * @copyright Copyright (C) 2020 Digital Peak GmbH. <https://www.digital-peak.com>
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */

echo 'a-------------------dirname(__DIR__)' . dirname(__DIR__) . PHP_EOL;
$hasInternet = true;
$wwwRoot     = '/var/www/html/' . $argv[1];
$db          = array_key_exists(3, $argv) ? $argv[3] : 'mysql';
$force       = array_key_exists(4, $argv) ? (bool)$argv[4] : false;
$binary      = '/home/docker/vendor/bin/joomla';

if (is_dir($wwwRoot) && !$force) {
	return;
}

if (!is_dir($wwwRoot) || $force) {
	if (!is_dir('/var/www/html/cache') && $hasInternet) {
		shell_exec('git clone https://github.com/joomla/joomla-cms.git /var/www/html/cache 2>&1 > /dev/null');
	} else if ($hasInternet) {
		shell_exec('git --work-tree=/var/www/html/cache --git-dir=/var/www/html/cache/.git fetch origin 2>&1 > /dev/null');
	} else if (is_dir('/var/www/html/Projects/DPDocker/webserver/www/cache')) {
		shell_exec('cp -r /var/www/html/Projects/DPDocker/webserver/www/cache /var/www/html/cache');
	} else {
		echo 'Can not setup Joomla!!!!';

		return;
	}

	shell_exec('rm -rf ' . $wwwRoot);
	shell_exec('cp -r /var/www/html/cache ' . $wwwRoot);

	// Checkout latest stable release
	shell_exec('git --work-tree=' . $wwwRoot . ' --git-dir=' . $wwwRoot . '/.git checkout tags/4.0.0-beta5 2>&1 > /dev/null');
	echo 'Using version 4.0.0-beta5 on ' . $wwwRoot . PHP_EOL;
}
echo shell_exec('/var/www/html/Projects/DPDocker/webserver/scripts/install-joomla.sh ' . $wwwRoot . ' ' . $db . ' sites_' . $argv[1] . ' "Joomla ' . $argv[1] . '" mailcatcher');

// Check if extensions are needed to be installed
if (!$argv[2]) {
	return;
}

$folders = explode(',', $argv[2]);
if ($argv[2] == 'all') {
	$folders = array_diff(scandir(dirname(dirname(dirname(__DIR__)))), ['..', '.', 'DPDocker']);
}

foreach ($folders as $project) {
	// Ignore projects with a dash when not a dev site is built and we use all extensions
	if ($argv[2] == 'all' && strpos($argv[1], 'dev') === false && strpos($project, '-') > 0) {
		continue;
	}

	// Ignore all non dev projects when we have a dev site and we use all extensions
	if ($argv[2] == 'all' && strpos($argv[1], 'dev') !== false && strpos($project, '-Dev') === false) {
		continue;
	}

	// Check if it is a Joomla installation
	if (file_exists('/var/www/html/Projects/' . $project . '/includes')) {
		continue;
	}

	if ($hasInternet && $argv[2] != 'all') {
		echo 'Building extension ' . $project . PHP_EOL;
		shell_exec('/var/www/html/Projects/DPDocker/composer/scripts/exec-install.sh ' . $project);
		shell_exec('/var/www/html/Projects/DPDocker/npm/scripts/exec-npm-install.sh ' . $project . ' 2>/dev/null');
	}
}
