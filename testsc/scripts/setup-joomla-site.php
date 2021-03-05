<?php
/**
 * @package   DPDocker
 * @copyright Copyright (C) 2020 Digital Peak GmbH. <https://www.digital-peak.com>
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */

$hasInternet = true;
$joomlaFolder = $argv[1];
$wwwRoot = '/var/www/html/' . $joomlaFolder;
$extension = $argv[2];
$db = $argv[3];
$joomlaVersion = $argv[4];
$rebuild = (bool)$argv[5];
$extensionbasedir = $argv[6];

if (is_dir($wwwRoot) && !$rebuild) {
	return;
}

if (!is_dir($wwwRoot) || $rebuild) {

	if ($joomlaVersion == 'j4db8') {
		echo 'installing j4db8' . PHP_EOL;
		$path = '/zips/Joomla_4.0.0-beta8-dev-Development-Full_Package.zip';

		$zip = new ZipArchive;
		if ($zip->open($path) === TRUE) {
			$zip->extractTo($wwwRoot);
			$zip->close();
			echo 'ok';
		} else {
			echo 'Fehler';
		}

		echo 'installing j4db8 (19.02) ready zip' . PHP_EOL;				
	}

	if ($joomlaVersion == 'j4b7') {
		echo 'installing j4b7' . PHP_EOL;
		$path = '/zips/Joomla_4.0.0-beta7-Beta-Full_Package.zip';

		$zip = new ZipArchive;
		if ($zip->open($path) === TRUE) {
			$zip->extractTo($wwwRoot);
			$zip->close();
			echo 'ok';
		} else {
			echo 'Fehler';
		}

		echo 'installing j4b7 ready zip' . PHP_EOL;				
	}

	if ($joomlaVersion == 'j3') {
		echo 'installing j3' . PHP_EOL;
		$path = '/zips/Joomla_3.9.24-Stable-Full_Package.zip';

		$zip = new ZipArchive;
		if ($zip->open($path) === TRUE) {
			$zip->extractTo($wwwRoot);
			$zip->close();
			echo 'ok';
		} else {
			echo 'Fehler';
		}

		echo 'installing j3 ready zip' . PHP_EOL;				
	}
}
//echo shell_exec('/var/www/html/Projects/DPDocker/webserver/scripts/install-joomla.sh ' . $wwwRoot . ' ' . $db . ' sites_' . $argv[1] . ' "Joomla ' . $argv[1] . '" mailcatcher');

// Check if extensions are needed to be installed
if (!$extension) {
	return;
}

$folders = explode(',', $extension);

echo 'dirname(dirname(dirname(__DIR__)))!!!!  ' . dirname(dirname(dirname(__DIR__))) . PHP_EOL;
echo '$extensionbasedir!!!!                   ' . $extensionbasedir . PHP_EOL;


if ($extension == 'all') {
	$folders = array_diff(scandir($extensionbasedir), ['..', '.', 'DPDocker']);
}
