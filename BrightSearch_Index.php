<?php
namespace BrightSearch;

class Index {
	private $_urls;
	private $_baseurl;

	private $_robots;

	private $_prefix;
	private $_conn;

	private $_microdata;
	
	private $_maxdepth = -1;
	
	private $_durls = array();

	function __construct() {
		$this -> _conn = \Connection::getInstance();
		// Force utf-8
		header('Content-Type: text/html; charset=utf-8');
		$config = new Config();
		$config -> setup();

		$this -> _prefix = Config::getInstance() -> TABLE_PREFIX;

		if(Config::getInstance() -> CRAWLMICRODATA === true) {
			$this -> _microdata = new Microdata();
		}
	}


    /**
     * @param $url
     * @param int $depth
     * @param string $robots
     */
    public function indexSite($url, $depth = -1, $robots = 'robots.txt') {
		
		$this -> _urls = array();
		$this -> _baseurl = rtrim($url, '/');
		$sql = "UPDATE `{$this -> _prefix}search` SET `deleted`=1 WHERE `url` LIKE '{$this -> _baseurl}%'";
		$this -> _conn -> updateRow($sql);


		$this -> _durls[0] = array(array($this -> _baseurl,0)); 
		$this -> _indexUrls(0);
		$sql = "DELETE FROM `{$this -> _prefix}search` WHERE `deleted`=1";
		$this -> _conn -> deleteRow($sql);
	
		$sm = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\r\n<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">";
		foreach($this -> _urls as $url) {
			if($url['url'] != '') {
				$sm .= "<url>
	<loc><![CDATA[{$url['url']}]]></loc>
	<lastmod>{$url['modified']}</lastmod>
	<changefreq>daily</changefreq>
	<priority>1</priority>
</url>\r\n";
			}
		} 
		$sm .= "</urlset>";
		file_put_contents(BASEPATH . 'sitemap.xml', $sm);
	}

    /**
     * Removes all the tags which are not indexed
     * @param \DOMDocument $dd The document to clean
     * @return \DomDocument The cleaned document
     */
	private function _cleanDocument(\DOMDocument $dd) {
		$remove = array('script','nav','footer');
		foreach($remove as $toremove) {
			$tags = $dd -> getElementsByTagName($toremove);
			$nt = $tags -> length;
			while(--$nt > -1) {
				$tags->item($nt) -> parentNode -> removeChild($tags -> item($nt));

			}
		}
		$removeclass = array('footer','navigation','nav');
		$xp = new \DOMXPath($dd);
		foreach($removeclass as $class) {
			// Also check for id
			$idtag = $dd -> getElementById($class);
			if($idtag) {
				$idtag -> parentNode -> removeChild($idtag);
			}
			$tags = $xp -> query("//*[contains(translate(@class, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', 'abcdefghijklmnopqrstuvwxyz'),'$class')]");
			if($tags) {
				$nt = $tags -> length;
				while(--$nt > -1) {
					//echo "{$tags -> item($nt) -> nodeName} contains $class\r\n";
					$tags->item($nt) -> parentNode -> removeChild($tags -> item($nt));

				}
			}
		}
		return $dd;
	}

    /**
     * @param Keyword $word
     * @param $word_in_path
     * @param $path_depth
     * @return float|int
     */
    private function _calc_weight (\BrightSearch\Keyword $word, $word_in_path, $path_depth) {
		$pweights = Config::getInstance() -> PWEIGHTS;
		while(count($pweights)< count($word -> inparagraphs)) {
			$pweights[] = $pweights[count($pweights)-1];
		}

    	$weight = ($word -> count
					+ $word -> intitle * Config::getInstance() -> TITLE_WEIGHT
					+ $word -> inh2 * Config::getInstance() -> H2_WEIGHT
					+ $word -> inh3 * Config::getInstance() -> H3_WEIGHT
					+ $word -> inh4 * Config::getInstance() -> H4_WEIGHT
					+ $word -> inh5 * Config::getInstance() -> H5_WEIGHT
					+ $word -> inh6 * Config::getInstance() -> H6_WEIGHT
					+ $word_in_path * Config::getInstance() -> PATH_WEIGHT
					+ $word -> inkeywords * Config::getInstance() -> META_WEIGHT);
		for($i = 0; $i < count($word -> inparagraphs); $i++) {
			$weight += $word -> inparagraphs[$i] * $pweights[$i];
		}
		$weight = $weight * 10	/ (0.2 + 0.8*$path_depth);

    	return $weight;
    }

    private function _checkSum($url, $md5sum) {
    	$url = $this -> _conn -> escape_string($url);
    	$md5sum = $this -> _conn -> escape_string($md5sum);
    	$sql = "SELECT `modificationdate` FROM `{$this -> _prefix}search` WHERE `url`='$url' AND `md5sum`='$md5sum'";
    	// If null, page has changed
    	return $this -> _conn -> getField($sql);;
    }
    
    private function _indexUrls($depth) {
    	if($depth == $this -> _maxdepth) {
    		return;
    	}
    	if(array_key_exists($depth, $this -> _durls)) {
	    	foreach($this -> _durls[$depth] as &$url) {
	    		$url[1] = 1;
	    		$this -> _indexUrl($url[0], $depth);
	    	}
	    	$this -> _indexUrls(++$depth);
    	} else {
    		echo $depth . ' not set';
    	}
    }

	private function _indexUrl($url, $curdepth = 0, $maxdepth = -1) {
		if($curdepth == $maxdepth) {
			//echo "Max depth of $maxdepth reached\r\n";
			return;
		}

		$status = Utils::url_status($url, parse_url($url));
		if($status['state'] != 'ok' || $status['content'] != 'text') {
			if($status['state'] != 'ok') {
				$str = $url . ' returned state ' . $status['state'] ;
				echo $str."\r\n";
			}
			return;
		}

		$key = md5($url);
		// Index it
		$index = true;
		$follow = true;

		$indexed = new IndexObject();
		$data =  Utils::file_get_contents_utf8($url);
		$md5sum = md5($data);

		$ischanged = $this -> _checkSum($url, $md5sum);

		$data = str_replace('&nbsp;', ' ', $data);
		$data = mb_convert_encoding($data, 'HTML-ENTITIES', "UTF-8");

		$index = new \DOMDocument();
    	$index -> encoding = 'UTF-8';
		@$index -> loadHTML($data);
		if(!$index) {
			return;
		}

		// Create another instance for crawling,
		// We want to crawl the whole document.
		$crawl = new \DOMDocument();
    	$crawl -> encoding = 'UTF-8';
		@$crawl -> loadHTML($data);

		$xp = new \DOMXPath($index);
		$robot = $xp -> query('//meta[@name="robots"]');
		$follow = true;
		$mindex = true;
		if($robot && $robot -> length > 0) {
			$robot = $robot -> item(0);
			$mindex = (strpos($robot -> getAttribute('content'), 'noindex') === false);
			$follow = (strpos($robot -> getAttribute('content'), 'nofollow') === false);

		}
		$robot = $xp -> query('//meta[@name="brightsearch"]');
		if($robot && $robot -> length > 0) {
			$robot = $robot -> item(0);
			$mindex = (strpos($robot -> getAttribute('content'), 'noindex') === false);
			$follow = (strpos($robot -> getAttribute('content'), 'nofollow') === false);

		}
		if(($ischanged === null && $mindex) || Config::getInstance() -> IGNORE_NOINDEX) {

			$index = $this -> _cleanDocument($index);
			$meta = $index -> getElementsByTagName('meta');
			foreach($meta as $metatag) {
				$name = '';
				if($metatag -> hasAttribute('name')) {
					$name = $metatag -> getAttribute('name');
				} else if($metatag -> hasAttribute('http-equiv')) {
					$name = $metatag -> getAttribute('http-equiv');
				} else if($metatag -> hasAttribute('charset')) {
					$indexed -> charset = $metatag -> getAttribute('charset');
				}

				if($name != '') {
					switch($name) {
						case 'keywords':
							$kw = $metatag -> getAttribute('content');
							$kw = explode(',', $kw);
							foreach($kw as &$k) {
								$k = trim($k);
							}
							$indexed -> keywords = $kw;
							break;
						case 'description':
							$indexed -> description = $metatag -> getAttribute('content');
							break;

						// We know these tags, we just don't use them
						case 'viewport':
						case 'apple-mobile-web-app-capable':
						case 'robots':
						case 'copyright':
						case 'googlebot':
							break;
						default:
							//echo "Unknown or unsupported meta tag " .$metatag -> getAttribute('name') ."\r\n";

					}
				}
			}

			if($index -> getElementsByTagName('title') -> length > 0) {
				// Set title
				$titles = $index -> getElementsByTagName('title');
				$indexed -> title = trim($titles -> item(0)  -> nodeValue);

			} else if($index -> getElementsByTagName('h1') -> length > 0) {
				// no Title, try h1
				$titles = $index -> getElementsByTagName('h1');
				$indexed -> title = $titles -> item(0) -> nodeValue;
			}
			$this -> _urls[$key] = array('url' => $url, 'data' => $index, 'crawl' => $crawl, 'modified' => date('Y-m-d'));
			echo "Indexing " . $url . " (depth $curdepth)\r\n";

			$content =  $index -> getElementsByTagName('body') -> item(0);
			if(!$content) {
				$indexed -> text = '';
			} else {
				
				$indexed -> text = $content -> textContent;
			}


        	$trash   = array("\\r\\n", "\\n", "\\r", "\r\n", "\n","\r","\\t","\t");       // kill 'LF' and the others
			$replace = ' ';
			$indexed -> text .= implode(' ', $indexed -> keywords);
			$indexed -> text = str_replace($trash, $replace, $indexed -> text);
			$indexed -> text = Utils::del_secchars($indexed -> text);
			$indexed -> text = mb_strtolower($indexed -> text, 'utf-8');
			$indexed -> title = substr($indexed -> title, 0,255);
			
			while(strpos($indexed -> text, '  ') !== false) {
				$indexed -> text = str_replace('  ', ' ', $indexed -> text);
			}
			$indexed -> path_depth = count(explode('/', $url))-2;
			$indexed -> url = $url;

			$words = explode(" ",$indexed -> text);
			$words  = Utils::unique_array($words);
			$sha1url = sha1($url);
			$kw = substr(implode(',',$indexed -> keywords), 0,255);
			$sql = "INSERT INTO `{$this -> _prefix}search` (`url`,`title`,`keywords`,`encoding`,`description`,`text`, `creationdate`,`md5sum`,`sha1url`,`deleted`) VALUES (";
			$sql .= "'" . $this -> _conn -> escape_string($url) . "',";
			$sql .= "'" . $this -> _conn -> escape_string($indexed -> title) . "',";
			$sql .= "'" . $this -> _conn -> escape_string($kw) . "',";
			$sql .= "'" . $this -> _conn -> escape_string($indexed -> charset) . "',";
			$sql .= "'" . $this -> _conn -> escape_string($indexed -> description) . "',";
			$sql .= "'" . $this -> _conn -> escape_string($indexed -> text) . "', NOW(),";
			$sql .= "'" . $this -> _conn -> escape_string($md5sum) ."',";
			$sql .= "'" . $this -> _conn -> escape_string($sha1url) ."', 0)
						ON DUPLICATE KEY UPDATE
						`title`=VALUES(`title`),
						`keywords`=VALUES(`keywords`),
						`encoding`=VALUES(`encoding`),
						`description`=VALUES(`description`),
						`text`=VALUES(`text`),
						`md5sum`=VALUES(`md5sum`),
						`sha1url`=VALUES(`sha1url`),
						`modificationdate`=NOW(),
						`deleted`=0,
						`id`=LAST_INSERT_ID(`id`)";
			$id = $this -> _conn -> insertRow($sql);

			if(Config::getInstance() -> CRAWLMICRODATA === true) {
				$this -> _microdata -> index($id, $index);
			}

			$lka = array();
			//Inserts the (new) keywords in the database, while we're looping over the keywords, check if they're in the title
			foreach($words as &$word) {
				$word -> intitle = strpos($indexed -> title, $word -> keyword) !== false;
				$word -> inkeywords = array_search($word -> keyword, $indexed -> keywords) !== false;
				$tags = array('h2','h3','h4','h5','h6','p');
				foreach($tags as $tag) {
					$nodes = $index -> getElementsByTagName($tag);
					$strcontent = '';
					switch($tag) {
						case 'p':
							foreach($nodes as $node) {
								$strcontent = Utils::lower_case(Utils::lower_ent($node -> textContent));
								$word -> inparagraphs[] = strpos($strcontent, $word -> keyword) !== false;
							}
							break;
						default:
							foreach($nodes as $node) {
								$strcontent .= $node -> textContent . " ";
							}
							$strcontent = Utils::lower_case(Utils::lower_ent($strcontent));
							$word -> {'in' . $tag} = strpos($strcontent, $word -> keyword) !== false;
					}
				}

				$esc = $this -> _conn -> escape_string($word -> keyword);
				$sql = "INSERT INTO {$this -> _prefix}keywords (`keyword`) VALUES ('$esc') ON DUPLICATE KEY UPDATE `keywordId`=LAST_INSERT_ID(`keywordId`)";
				$word -> id = $this -> _conn -> insertRow($sql);
				$word -> weight = $this -> _calc_weight($word,false,$indexed -> path_depth);

				$table = substr(md5($esc),0,1);
				if(!isset($lka[$table]))
					$lka[$table] = array();
				$lka[$table][] = "($id, {$word -> id}, {$word -> weight}, {$word -> count}, NOW(),0)";
			}
			foreach($lka as $table => $value) {
				if(count($value) > 0) {
					$this -> _conn -> updateRow("UPDATE {$this -> _prefix}link_keyword$table SET `deleted`=1 WHERE `linkId`=$id");
					$sql = "INSERT INTO {$this -> _prefix}link_keyword$table (`linkId`, `keywordId`, `weight`,`hits`,`indexdate`,`deleted`) VALUES";
					$sql .= implode(",\r\n", $value);
					$sql .= " ON DUPLICATE KEY UPDATE `weight`= VALUES(`weight`), `hits`=VALUES(`hits`), `deleted`=0, `indexdate`=NOW()";
					$this -> _conn -> insertRow($sql);
					$this -> _conn -> deleteRow("DELETE FROM {$this -> _prefix}link_keyword$table WHERE `deleted`=1");
				}
			}

		} else {
			echo "not changed: $url (depth: $curdepth, $ischanged)\r\n";
			//return;
			$sql = "UPDATE `{$this -> _prefix}search` SET `deleted`=0 WHERE `url`='$url' AND `md5sum`='$md5sum'";
    		$this -> _conn -> updateRow($sql);
    		// Add it for sitemap
    		if(!$ischanged) {
    			$ischanged =  date('Y-m-d'); 
    		} else {
    			$ischanged = substr($ischanged, 0,10);
    		}
			$this -> _urls[$key] = array('url' => $url, 'data' => null, 'crawl' => null, 'modified' => $ischanged);

		}

		if($follow || Config::getInstance() -> IGNORE_NOFOLLOW) {
			$basehref = $crawl -> getElementsByTagName('base');
			if($basehref -> length > 0) {
//				echo 'doc has a basehref: ';
//				print_r($basehref);
//				@todo implement!
			}
			$tags = $crawl -> getElementsByTagName('a');
			
			$newurls = array();
			foreach($tags as $atag) {
				// Skip anchor links
				if($atag -> hasAttribute('href')
					&& strpos($atag->getAttribute('href'), '#') !== 0
					&& strpos($atag->getAttribute('href'), 'javascript') !== 0
					&& strpos($atag->getAttribute('href'), 'mailto') !== 0) {
					$href = $atag->getAttribute('href');
					// Url is relative to root
					if(strpos($href, '/') === 0) {
						$href = $this -> _baseurl . $href;
					}

					// Url is relative to parent
					if(strpos($href, '..') === 0) {
						$href = $url . $href;
					}

					// If no http in front, put parent url in front
					if(strpos($href, 'http') !== 0) {
						$parts = parse_url($url);
						// Rebuild the url to loose existing query parameters
						if(!array_key_exists('path', $parts)) {
							echo "path not set in BrightSearch_Index.php 403\r\n{$url}\r\n". print_r($parts);
						}
							
						$href = "{$parts['scheme']}://{$parts['host']}{$parts['path']}{$href}";
					}

					// Don't leave domain
					if(strpos($href, $this -> _baseurl) === 0) {
						$href = str_replace($this -> _baseurl, '', $href);
						$href = str_replace('//', '/', $href);

						$href = rtrim($this -> _baseurl . $href, '/');

						$key = md5($href);
						if(!isset($this -> _urls[$key])) {
							$this -> _urls[$key] = 1;
							$newurls[] = array($href,0);
						}
					}
				}
			}

			if(count($newurls)) {
				echo 'Found ' .count($newurls). ' new urls' . "\r\n";
				$curdepth++;
				if(!array_key_exists($curdepth, $this -> _durls)) {
					$this -> _durls[$curdepth] = $newurls;
				} else {
					$this -> _durls[$curdepth] = array_merge($this -> _durls[$curdepth], $newurls);
				}
			}
		}


		$index = null;
		$crawl = null;
	}
}