<?php
/**
 * @package   DPDocker
 * @copyright Copyright (C) 2020 Digital Peak GmbH. <https://www.digital-peak.com>
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */
?>
<html>
<head>
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/normalize/8.0.1/normalize.min.css">
	<style>
		body {
			max-width: 1000px;
			margin-right: auto;
			margin-left: auto;
		}
	</style>
</head>
<body>
<h1>Welcome to the Joomla extension development web server</h1>
<p>More information can be found on <a href="https://github.com/Digital-Peak/DPDocker/tree/initial/webserver">Github</a>.</p>
<p>Here is the list of available sites:</p>
<ul>
	<li><a href="j4b7_ag">j4b7_ag</a> <a href="j4b7_ag/administrator">(admin)</a>.</li>
	<li><a href="j4b7_boilerplate">j4b7_boilerplate</a> <a href="j4b7_boilerplate/administrator">(admin)</a>.</li>
	<li><a href="j3_testuptodatepackage">j3_testuptodatepackage</a> <a href="j3_testuptodatepackage/administrator">(admin)</a>.</li>
	<li><a href="j3_pkg_agosms">j3_pkg_agosms</a> <a href="j3_pkg_agosms/administrator">(admin)</a>.</li>
	<li><a href="j3_pkg_agadvents">j3_pkg_agadvents</a> <a href="j3_pkg_agadvents/administrator">(admin)</a>.</li>
	<li><a href="//<?php echo $_SERVER['SERVER_NAME']; ?>:81">PHPMyAdmin installation</a></li>
	<li><a href="//<?php echo $_SERVER['SERVER_NAME']; ?>:82">Mailcatcher installation</a></li>
</ul>
</body>
</html>
