<?php
/**
 * @package   DPDocker
 * @copyright Copyright (C) 2020 Digital Peak GmbH. <https://www.digital-peak.com>
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */

/**
 * Build release files.
 */
class DPDockerReleaseBuild
{
	public $extensionRoot;

	public function build()
	{
		// Normalize path
		$this->extensionRoot = realpath($this->extensionRoot);

		// Cleanup the dist folder
		$distFolder = dirname($this->extensionRoot) . '/dist';
		shell_exec('rm -rf ' . $distFolder);
		mkdir($distFolder);

		// Read the build config
		$config = json_decode(file_get_contents($this->extensionRoot . '/package/build.json'));
		foreach ($config->packages as $package) {
			// Prepare the temp folder
			$tmpFolder = dirname($this->extensionRoot) . '/build';
			shell_exec('rm -rf ' . $tmpFolder);
			mkdir($tmpFolder);

			// Read the manifest file
			$manifest  = new SimpleXMLElement(file_get_contents($this->extensionRoot . '/package/' . $package->originalManifestFileName . '.xml'));
			$dpVersion = (string)$manifest->version;

			echo ' Creating version ' . $dpVersion . ' of ' . $package->name . PHP_EOL;

			$dpVersion = str_replace('.', '_', $dpVersion);

			// Collect the extensions which do belong to the package
			$extensions = [];

			// Iterate over the files in the manifest
			foreach ($manifest->files->file as $file) {
				$extension = null;
				$id = "";

				// Look for an override in the build config
				foreach ($package->extensions as $ex) {
					$id = (string)$file->attributes()->id;
					if ($ex->name == $id) {
						$extension = $ex;
						break;
					}
				}

				// If none override is found, create an extension from the manifest
				if ($extension == null) {
					$extension = (object)['name' => $id];
				}
				$extensions[] = $extension;
			}

			// Loop over the extensions
			foreach ($extensions as $extension) {
				// Default the excludes
				$excludes = [];
				if (!empty($extension->excludes)) {
					foreach ($extension->excludes as $exclude) {
						$excludes[] = $exclude;
					}
				}

				// Default the substitutes
				$substitutes = [];
				if (!empty($extension->substitutes)) {
					foreach ($extension->substitutes as $substitute) {
						$substitutes[$substitute->original] = $substitute->replace;
					}
				}

				// Default the copy
				$copies = [];
				if (!empty($extension->copies)) {
					foreach ($extension->copies as $copy) {
						$copies[$copy->original] = $copy->replace;
					}
				}
				// echo  PHP_EOL . "extension root"  . PHP_EOL . $this->extensionRoot . '/src';
				// echo  PHP_EOL ."/ntempzip"  . PHP_EOL . $tmpFolder . '/' . $extension->name . '.zip';
				// Create the extension zip file
				$this->createZip($this->extensionRoot . '/src', $extension->name, $tmpFolder . '/' . $extension->name . '.zip', $excludes,
					$substitutes, $copies);
			}
			

			// Copy some package files to the tmp folder
			if (is_file($this->extensionRoot . '/package/script.php')) {
				copy($this->extensionRoot . '/package/script.php', $tmpFolder . '/script.php');
			}
			copy($this->extensionRoot . '/License.md', $tmpFolder . '/License.txt');
			copy($this->extensionRoot . '/package/' . $package->originalManifestFileName . '.xml',
				$tmpFolder . '/' . $package->substituteManifestFileName . '.xml');

			// Create the package zip file
			echo $tmpFolder . PHP_EOL;
			echo $distFolder . PHP_EOL;

			$this->createZip($tmpFolder, $package->name, $distFolder . '/' . $package->name . '_' . $dpVersion . '.zip',  [], [], []);
		}
	}

	private function createZip($folder, $extensionName, $zipFile, $excludes, $substitutes, $copies)
	{
		
		$extensionNameArray = explode ( '_', $extensionName);
		if (!isset($extensionNameArray[2])) {
			$extensionNameArray[2] = '';
		}
		
		//print_r($extensionNameArray);

		// Some predefined excludes
		$excludes[] = 'vendor-ignore.txt';
		$excludes[] = 'composer.json';
		$excludes[] = 'composer.lock';
		$excludes[] = 'css.map';
		$excludes[] = 'js.map';

		

		




		// The zip objects
		$zip = new ZipArchive();
		$zip->open($zipFile, ZIPARCHIVE::CREATE);

		if ($extensionNameArray[0] === 'com'
		|| $extensionNameArray[0] === 'mod'
		|| $extensionNameArray[0] === 'plg') {

			$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($folder), RecursiveIteratorIterator::LEAVES_ONLY);
			foreach ($files as $file) {
				// Get real path for current file
				$filePath = $file->getRealPath();
				$fileName = str_replace($this->extensionRoot . '/src/', '', $filePath);
				//echo $fileName . PHP_EOL;

				// Check if the file should be ignored
				$ignore = false;
				foreach ($excludes as $exclude) {
					if (stripos($fileName, $exclude) !== false) {
						$ignore = true;
						break;
					}
				}

				// Also ignore directories
				if ($ignore || is_dir($filePath) || !$fileName) {
					continue;
				}

				// Do the substitution
				if (key_exists($fileName, $substitutes)) {
					//print_r($substitutes);
					$fileName = $substitutes[$fileName];    
				}

				// Do the copy
				if (key_exists($fileName, $copies)) {
					$zip->addFile($filePath, trim($copies[$fileName], '/'));  
				}

				// Remove trailing slashes
				$fileName = trim($fileName, '/');

				// Add current file to archive str_starts_with
				if ((str_starts_with($filePath, $this->extensionRoot . '/src/components/' .  $extensionName)) 
				|| (str_starts_with($filePath , $this->extensionRoot . '/src/api/components/' .  $extensionName))
				|| (str_starts_with($filePath , $this->extensionRoot . '/src/media/' .  $extensionName))
				|| (str_starts_with($filePath , $this->extensionRoot . '/src/administrator/components/' .  $extensionName))
				) {
					$zip->addFile($filePath, $fileName);
				}
			}

	
		} 
		
		if ($extensionNameArray[0] === 'mod') {
	
				$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($folder . '/modules/' . $extensionName), RecursiveIteratorIterator::LEAVES_ONLY);
				foreach ($files as $file) {
					// Get real path for current file
					$filePath = $file->getRealPath();
					$fileName = str_replace($this->extensionRoot . '/src/modules/' . $extensionName, '', $filePath);
					//echo $fileName . PHP_EOL;
	
					// Check if the file should be ignored
					$ignore = false;
					foreach ($excludes as $exclude) {
						if (stripos($fileName, $exclude) !== false) {
							$ignore = true;
							break;
						}
					}
	
					// Also ignore directories
					if ($ignore || is_dir($filePath) || !$fileName) {
						continue;
					}
	
					// Doe the substitution
					if (key_exists($fileName, $substitutes)) {
						//print_r($substitutes);
						$fileName = $substitutes[$fileName];    
					}
	
					// Remove trailing slashes
					$fileName = trim($fileName, '/');
	
					// Add current file to archive str_starts_with
					if (str_starts_with($filePath , $this->extensionRoot . '/src/modules/' .  $extensionName)) {
						$zip->addFile($filePath, $fileName);
					}
				}
		
			} 
			
			if ($extensionNameArray[0] === 'plg') {
		
					$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($folder . '/plugins/' . $extensionNameArray[1] . '/' .  $extensionNameArray[2]), RecursiveIteratorIterator::LEAVES_ONLY);
					foreach ($files as $file) {
						// Get real path for current file
						$filePath = $file->getRealPath();
						$fileName = str_replace($this->extensionRoot . '/src/plugins/' . $extensionNameArray[1] . '/' . $extensionNameArray[2], '', $filePath);
						//echo $fileName . PHP_EOL;
		
						// Check if the file should be ignored
						$ignore = false;
						foreach ($excludes as $exclude) {
							if (stripos($fileName, $exclude) !== false) {
								$ignore = true;
								break;
							}
						}
		
						// Also ignore directories
						if ($ignore || is_dir($filePath) || !$fileName) {
							continue;
						}
		
						// Do the substitution
						if (key_exists($fileName, $substitutes)) {
							//print_r($substitutes);
							$fileName = $substitutes[$fileName];    
						}
		
						// Remove trailing slashes
						$fileName = trim($fileName, '/');
		
						// Add current file to archive str_starts_with
						if (str_starts_with($filePath , $this->extensionRoot . '/src/plugins/' . $extensionNameArray[1] . '/' .  $extensionNameArray[2])) {
							$zip->addFile($filePath, $fileName);
						}
					}
		
			
				} 
				
				if ($extensionNameArray[0] === 'tpl') {

			$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($folder . '/templates/' . $extensionNameArray[1]), RecursiveIteratorIterator::LEAVES_ONLY);
			foreach ($files as $file) {
				// Get real path for current file
				$filePath = $file->getRealPath();
				$fileName = str_replace($this->extensionRoot . '/src/templates/' . $extensionNameArray[1] . '/', '', $filePath);
				// echo $fileName . PHP_EOL;

				// Check if the file should be ignored
				$ignore = false;
				foreach ($excludes as $exclude) {
					if (stripos($fileName, $exclude) !== false) {
						$ignore = true;
						break;
					}
				}

				// Also ignore directories
				if ($ignore || is_dir($filePath) || !$fileName) {
					continue;
				}

				// Do the substitution
				if (key_exists($fileName, $substitutes)) {
					//print_r($substitutes);
					$fileName = $substitutes[$fileName];
				}

				// Remove trailing slashes
				$fileName = trim($fileName, '/');

				// Add current file to archive str_starts_with
				if (str_starts_with($filePath , $this->extensionRoot . '/src/templates/' . $extensionNameArray[1])) {
					$zip->addFile($filePath, $fileName);
				}
			}

	
		}

		if ($extensionNameArray[0] !== 'com'
		&& $extensionNameArray[0] !== 'mod'
		&& $extensionNameArray[0] !== 'plg'
		&& $extensionNameArray[0] !== 'tpl') {

		// The file iterator
		$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($folder), RecursiveIteratorIterator::LEAVES_ONLY);
		foreach ($files as $file) {
			// Get real path for current file
			$filePath = $file->getRealPath();
			$fileName = str_replace($this->extensionRoot . '/', '', $filePath);
			$fileName = str_replace(dirname($this->extensionRoot) . '/build', '', $fileName);

			// Handling top level resources directory special as it can be that vendor has resources too
			$segments = explode('/', $fileName);
			if (count($segments) > 1 && $segments[1] == 'resources') {
				continue;
			}

			// Check if the file should be ignored
			$ignore = false;
			foreach ($excludes as $exclude) {
				if (stripos($fileName, $exclude) !== false) {
					$ignore = true;
					break;
				}
			}

			// Also ignore directories
			if ($ignore || is_dir($filePath) || !$fileName) {
				continue;
			}

			// Doe the substitution
			if (key_exists($fileName, $substitutes)) {
				$fileName = $substitutes[$fileName];
			}

			// Remove trailing slashes
			$fileName = trim($fileName, '/');

			// Add current file to archive
			$zip->addFile($filePath, $fileName);
		}

		}
		// Close the zip file
		$zip->close(); 
	}
}

// Instantiate and run the app
$build                = new DPDockerReleaseBuild();
$build->extensionRoot = $argv[1];
$build->build();
