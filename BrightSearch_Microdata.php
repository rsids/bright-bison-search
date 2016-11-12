<?php
namespace BrightSearch;

/**
 * Crawls the page for microdata. For more information, see http://schema.org
 * @author Ids
 * @version 1.0
 * @package BrightSearch
 */
class Microdata {

	private $_xp;
	private $_doc;
	private $_linkid;
	private $_prefix;

	function __construct() {
		$this -> _prefix = Config::getInstance() -> TABLE_PREFIX;
	}

	public function index($linkid, \DomDocument $doc) {
		$this -> _linkid = $linkid;
		$this -> _doc = $doc;
		$this -> _xp = new \DOMXPath($doc);

		$microdata = array();

		$obj = new \StdClass();
		$schemes = $this -> _xp -> query('//*[@itemscope]');
		if($schemes -> length > 0) {
			foreach($schemes as $item) {
				$new = $this -> _processItem($item, 0);
				if($new)
					$microdata[] = $new;
			}
		}
		$this -> _xp = null;
		$this -> _doc = null;
		$this -> _linkid = null;
		echo ' ' . count($microdata) . ' elements' . "\r\n";
	}

	public function getMicrodata($linkid) {
		$sql = "SELECT * FROM `{$this -> _prefix}microdata` WHERE linkId=$linkid ORDER BY `parent`";
		$result = \Connection::getInstance() -> getRows($sql);
		if(!$result)
			return;
		$parent = -1;

		$arr = array();
		$current = new \StdClass();

		foreach($result as $row) {
			$arr[$row -> id] = (object) array('parent' => $row -> parent, 'name' => $row -> name, 'value' => $row -> value);
		}
		foreach($arr as $key => $value) {
			$arr[$value -> parent] -> children[] = $value;
		}
		$res = new \StdClass();
		$this -> _createObjectMicrodataObject($res, $arr[0] -> children[0]);
		return $res;
	}

	private function _createObjectMicrodataObject(&$result, $obj) {
		$result -> type = $obj -> value;
		foreach($obj -> children as $val) {
			if(isset($val -> children)) {
				$result -> {$val -> value} = new \StdClass();
				$this -> _createObjectMicrodataObject($result -> {$val -> value}, $val);
			} else {
				$result -> {$val -> name} = $val -> value;

			}
		}

	}

	private function _processItem($item, $parentId = 0) {
		if(!$item -> hasAttribute('data-bright-processed')) {
			$item -> setAttribute('data-bright-processed', 1);

			$type = strtolower($item -> getAttribute('itemtype'));
			switch($type) {
					case 'http://schema.org/event':
					case 'http://schema.org/organisation':
					case 'http://schema.org/movie':
						$pid = \Connection::getInstance() -> insertRow("INSERT INTO `{$this -> _prefix}microdata` (`linkId`,`parent`,`name`,`value`) VALUES ('{$this -> _linkid}',$parentId, 'type', '$type')");
						$obj = new \StdClass();
						$obj -> type = $type;
						$obj -> parentId = $pid;
						$this -> _traverseChildren($item, $obj);
						return $obj;
						break;
			}
		}
		return null;
	}


	private function _traverseChildren($item, &$obj) {

		foreach($item -> childNodes as $child) {
			switch($child -> nodeType) {
				case 3: // Text;
				case 8: // Comment;
					break;
				default:

					if($child -> hasAttribute('itemscope')) {
						// Sub item
						$obj -> {strtolower($child -> getAttribute('itemtype'))}= $this -> _processItem($child, $obj -> parentId);

					} else if($child -> hasAttribute('itemprop')) {
						switch($child -> nodeName) {
							case 'meta':
								$val = $child -> getAttribute('content');
								break;
							case 'img':
								$val = $child -> getAttribute('src');
								break;
							default:
								$val = trim($child -> nodeValue);
						}
						if(isset($obj -> {$child -> getAttribute('itemprop')})) {
							if(!is_array($obj -> {$child -> getAttribute('itemprop')}))
								$obj -> {$child -> getAttribute('itemprop')} = array($obj -> {$child -> getAttribute('itemprop')});

							$obj -> {$child -> getAttribute('itemprop')}[] = $val;
						} else {
							$obj -> {$child -> getAttribute('itemprop')} = $val;
						}
						$val = \Connection::getInstance() -> escape_string($val);
						\Connection::getInstance() -> insertRow("INSERT INTO `{$this -> _prefix}microdata` (`linkId`,`parent`,`name`,`value`) VALUES ('{$this -> _linkid}',{$obj -> parentId}, '{$child -> getAttribute('itemprop')}', '$val')");
					} else {
						$this -> _traverseChildren($child, $obj);
					}
			}

		}
	}
}