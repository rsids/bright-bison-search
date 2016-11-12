<?php
// Include bright autoloader
require_once(dirname(__FILE__) . '/../../library/Bright/Bright.php');

class BrightSearch {
	function __construct() {
		spl_autoload_register(array($this, '_loader'));
	}

	private function _loader($classname) {
		$classpath = explode('\\', $classname);
		if(count($classpath) == 1)
			return false;

		if($classpath[1] == 'StdClass')
			return false;

		if($classpath[0] == 'BrightSearch') {
			//echo 'Autoloading ' . $classname;
			include(dirname(__FILE__) . "/BrightSearch_{$classpath[1]}.php");
		}
		return false;

	}
}
$bs = new BrightSearch();