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

// print_r( $folders);

foreach ($folders as $project) {
	// Ignore projects with a dash when not a dev site is built and we use all extensions
/*	if ($extension == 'all' && strpos($argv[1], 'dev') === false && strpos($project, '-') > 0) {
		continue;
	}*/

	// Ignore all non dev projects when we have a dev site and we use all extensions
/*	if ($extension == 'all' && strpos($argv[1], 'dev') !== false && strpos($project, '-Dev') === false) {
		continue;
	}*/

	// Check if it is a Joomla installation
/*	if (file_exists('/var/www/html/Projects/' . $project . '/includes')) {
		continue;
	}*/

/*	if ($hasInternet && $extension != 'all') {
		echo 'Building extension ' . $project . PHP_EOL;
		shell_exec('/var/www/html/Projects/DPDocker/composer/scripts/exec-install.sh ' . $project);
		shell_exec('/var/www/html/Projects/DPDocker/npm/scripts/exec-npm-install.sh ' . $project . ' 2>/dev/null');
	}*/

	createLinks($extensionbasedir . '/' . $project .  '/' , $wwwRoot);
}
// Discover

function createLinks($folderRoot, $wwwRoot)
{
	echo 'Starting to create the links for ' . $folderRoot . PHP_EOL;

	// Folder structure like https://github.com/joomla-extensions/weblinks
	if (file_exists($folderRoot . '/src/administrator/components/')) {
		foreach (new DirectoryIterator($folderRoot . '/src/administrator/components/') as $filename) {
			createLink($folderRoot . '/src/administrator/components/' . $filename, $wwwRoot . '/administrator/components/' . $filename);
		}
	}
	if (file_exists($folderRoot . '/src/administrator/manifests/packages/')) {
		foreach (new DirectoryIterator($folderRoot . '/src/administrator/manifests/packages/') as $filename) {
			createLink($folderRoot . '/src/administrator/manifests/packages/' . $filename,
				$wwwRoot . '/administrator//manifests/packages/' . $filename);
		}
	}
	if (file_exists($folderRoot . '/src/api/components/')) {
		foreach (new DirectoryIterator($folderRoot . '/src/api/components/') as $filename) {
			createLink($folderRoot . '/src/api/components/' . $filename, $wwwRoot . '/api/components/' . $filename);
		}
	}
	if (file_exists($folderRoot . '/src/components/')) {
		foreach (new DirectoryIterator($folderRoot . '/src/components/') as $filename) {
			createLink($folderRoot . '/src/components/' . $filename, $wwwRoot . '/components/' . $filename);
		}
	}
	if (file_exists($folderRoot . '/src/administrator/language')) {
		$languages = scandir($folderRoot . '/src/administrator/language');
		foreach ($languages as $language) {
			foreach (new DirectoryIterator($folderRoot . '/src/administrator/language/' . $language) as $filename) {
				createLink($folderRoot . '/src/administrator/language/' . $language . $filename,
					$wwwRoot . '/administrator/language/' . $language . $filename);
			}
		}
	}
	if (file_exists($folderRoot . '/src/language')) {
		$languages = scandir($folderRoot . '/src/language');
		foreach ($languages as $language) {
			foreach (new DirectoryIterator($folderRoot . '/src/language/' . $language) as $filename) {
				createLink($folderRoot . '/src/language/' . $language . $filename, $wwwRoot . '/language/' . $language . $filename);
			}
		}
	}
	if (file_exists($folderRoot . '/src/media/')) {
		foreach (new DirectoryIterator($folderRoot . '/src/media/') as $filename) {
			createLink($folderRoot . '/src/media/' . $filename, $wwwRoot . '/media/' . $filename);
		}
	}
	if (file_exists($folderRoot . '/src/modules/')) {
		foreach (new DirectoryIterator($folderRoot . '/src/modules/') as $filename) {
			createLink($folderRoot . '/src/modules/' . $filename, $wwwRoot . '/modules/' . $filename);
		}
	}
	if (file_exists($folderRoot . '/src/plugins')) {
		$groups = scandir($folderRoot . '/src/plugins');
		foreach ($groups as $group) {
			foreach (new DirectoryIterator($folderRoot . '/src/plugins/' . $group) as $filename) {
				createLink($folderRoot . '/src/plugins/' . $group . '/' . $filename, $wwwRoot . '/plugins/' . $group . '/' . $filename);
			}
		}
	}
	if (file_exists($folderRoot . '/src/templates/')) {
		foreach (new DirectoryIterator($folderRoot . '/src/templates/') as $filename) {
			createLink($folderRoot . '/src/templates/' . $filename, $wwwRoot . '/templates/' . $filename);
		}
	}

	foreach (new DirectoryIterator($folderRoot) as $filename) {
		if (strpos($filename, 'com_') === 0) {
			createLink($folderRoot . $filename . '/admin', $wwwRoot . '/administrator/components/' . $filename);
			createLink($folderRoot . $filename . '/site', $wwwRoot . '/components/' . $filename);
			createLink($folderRoot . $filename . '/media', $wwwRoot . '/media/' . $filename);
		}
		if (strpos($filename, 'mod_') === 0) {
			createLink($folderRoot . $filename, $wwwRoot . '/modules/' . $filename);

			if (file_exists($folderRoot . $filename . '/media')) {
				createLink($folderRoot . $filename . '/media', $wwwRoot . '/media/' . $filename);
			}
		}
		if (strpos($filename, 'plg_') === 0) {
			foreach (new RegexIterator(new DirectoryIterator($folderRoot . $filename), "/\\.xml\$/i") as $pluginFile) {
				$xml = new SimpleXMLElement(file_get_contents($folderRoot . $filename . '/' . $pluginFile));

				foreach ($xml->files->filename as $file) {
					$plugin = (string)$file->attributes()->plugin;
					if (!$plugin) {
						continue;
					}

					$group = (string)$xml->attributes()->group;
					if (!is_dir($wwwRoot . '/plugins/' . $group)) {
						@mkdir($wwwRoot . '/plugins/' . $group, '0777', true);
						exec('chmod 777 ' . $wwwRoot . '/plugins/' . $group);
					}

					createLink($folderRoot . $filename, $wwwRoot . '/plugins/' . $group . '/' . $plugin);

					if (file_exists($folderRoot . $filename . '/media')) {
						createLink($folderRoot . $filename . '/media', $wwwRoot . '/media/' . $filename);
					}
				}
			}
		}
		if (strpos($filename, 'tmpl_') === 0) {
			createLink($folderRoot . $filename, $wwwRoot . '/templates/' . str_replace('tmpl_', '', $filename));
		}
	}
	echo 'Finished to create the links for ' . $folderRoot . PHP_EOL;
}

function createLink($source, $target)
{
	$source = realpath($source);

	@mkdir(dirname($target), '777', true);
	shell_exec('ln -sfn ' . $source . ' ' . $target);
}
