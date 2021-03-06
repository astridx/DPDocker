// The needed libs
const fs = require('fs');
const path = require('path');
const uglifyJS = require('uglify-js');
const sass = require('node-sass');
const babel = require('@babel/core');
const rollup = require('rollup');

/**
 * Transpile function which can handle Javascript, SASS and CSS files.
 *
 * @param string source The full path of the source file
 * @param string destination The full path of the destination file
 * @param string isVendor If the file is a vendor file and doesn't need some extra bundling
 */
function transpile(source, destination, isVendor)
{
	// Ensure that the target directory exists
	if (!fs.existsSync(path.dirname(destination))) {
		fs.mkdirSync(path.dirname(destination), {recursive: true});
	}

	// Transpile the files
	switch (path.extname(source).replace('.', '')) {
		case 'js':
			const babelify = (file, full) => {
				if (destination.indexOf('.min.') > 0) {
					fs.copyFileSync(file, destination.replace('.min.js', '.js'));
					return;
				}

				// Transform the content to ensure we support the required browsers
				let result = babel.transformSync(fs.readFileSync(file, 'utf8'), {
					sourceMaps: true,
					compact: false,
					presets: [['@babel/preset-env', {'targets': {'browsers': ['> 0.25%, not dead', 'ie 11']}, 'modules': false}]]
				});

				if (full) {
					// Write the none minified content to the destination file
					fs.writeFileSync(destination, result.code);

					// Write the map content to the destination file
					fs.writeFileSync(destination + '.map', JSON.stringify(result.map));
				}

				// Write the minified content to the destination file
				fs.writeFileSync(destination.replace('.js', '.min.js'), uglifyJS.minify(result.code).code);
			};

			// Bundle only when it is an extension file
			if (isVendor) {
				babelify(source, true);
			} else {
				(async () => {
					const bundle = await rollup.rollup({input: source});

					// Generate code
					await bundle.write({file: destination, format: 'iife', sourcemap: true});

					babelify(destination, false);
				})();
			}
			break;
		case 'scss':
		case 'css':
			// Compile sass files
			let result = sass.renderSync({
				file: source,
				outFile: destination,
				outputStyle: 'expanded',
				indentType: 'tab',
				indentWidth: 1,
				sourceMap: true
			});

			// Write the none minified content to the destination file
			fs.writeFileSync(destination, result.css.toString());

			// Write the map content to the destination file
			fs.writeFileSync(destination + '.map', result.map);

			// Write the minified content to the destination file
			fs.writeFileSync(
				destination.replace('.css', '.min.css'),
				sass.renderSync({data: result.css.toString(), outputStyle: 'compressed'}).css.toString()
			);
	}
}

/**
 * Return all files recursively of the given directory.
 *
 * @param string dir The directory to traverse
 * @returns {Array}
 */
function getFiles(dir)
{
	// The results array
	let results = [];

	// Loope over the current directory files
	fs.readdirSync(dir).forEach(function (file) {
		// The file to work on
		file = dir + '/' + file;

		// The file stats
		const stat = fs.statSync(file);

		// If it is a directory, read it as well
		if (stat && stat.isDirectory()) {
			results = results.concat(getFiles(file));
		} else {
			results.push(file);
		}
	});
	return results;
}

/**
 * Deletes the given directory.
 *
 * @param string path The path to delete
 */
function deleteDirectory(path)
{
	// Check if the direcory exists
	if (!fs.existsSync(path)) {
		return;
	}

	// Loop over the files of the directory
	fs.readdirSync(path).forEach(function (file) {
		// The file to work on
		const currentPath = path + '/' + file;

		// If it is a directory, delete it first
		if (fs.lstatSync(currentPath).isDirectory()) {
			deleteDirectory(currentPath);
		} else {
			fs.unlinkSync(currentPath);
		}
	});

	// Delete the top level directory
	fs.rmdirSync(path);
}

module.exports = {
	transpile: transpile,
	getFiles: getFiles,
	deleteDirectory: deleteDirectory
}
