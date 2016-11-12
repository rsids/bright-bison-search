<?php
namespace BrightSearch;

class Utils {

	/**
	 * Sorts an array by weight
	 * @param unknown_type $a
	 * @param unknown_type $b
	 */
	public static function cmp_weight($a, $b) {
		if ($a -> weight == $b -> weight)
			return 0;
		return ($a -> weight > $b -> weight) ? -1 : 1;
	}

	public static function getRobots($url, $robots) {
		return file_get_contents($url . '/' . $robots);
	}

	public static function ignoreword($word) {

		if (Config::getInstance() -> INDEX_NUMBERS == 1) {
			$pattern = "[a-z0-9]+";
		} else {
			$pattern = "[a-z]+";
		}
		if (strlen($word) < Config::getInstance() -> MIN_WORD_LENGTH) {// || ($common[$word] == 1) removed common words
			return 1;
		} else {
			return 0;
		}
	}

	/**
	 * convert ISO-8859-x entities into their lower case equivalents
	 * @param string $string The string to lower
	 */
	public static function lower_ent($string) {
		foreach(Entities::$uppertolowerent as $char => $rep) {
            $string = preg_replace("/".$char."/i", $rep, $string);
		}
        return ($string);
    }

	/**
	 * convert characters into lower case
	 * @param string $string The string to lower
	 */
	public static function lower_case($string) {

        //      if required, convert Greek charset into lower case
        if (Config::getInstance() -> IS_GREEK) {
			foreach(Entities::$uppertolowergreek as $char => $rep) {
           		$string = preg_replace("/".$char."/i", $rep, $string);
        	}
        }

        //      if required, convert Cyrillic charset into lower case
        if (Config::getInstance() -> IS_CYRILLIC) {
			foreach(Entities::$uppertolowercyrillic as $char => $rep) {
           		$string = preg_replace("/".$char."/i", $rep, $string);
        	}
        }

        return (strtr($string,  "ABCDEFGHIJKLMNOPQRSTUVWXYZ",
                                "abcdefghijklmnopqrstuvwxyz"));

	}

	public static function makeboollist($a, $type) {

		foreach(Entities::$namedentities as $char => $rep) {
           	$a = preg_replace("/".$char."/i", $rep, $a);
        }

		foreach(Entities::$numericentities as $char => $rep) {
           	$a = preg_replace("/".$char."/i", $rep, $a);
        }
        if ($type != "phrase") {    //  delete secondary characters from query
            $a = Utils::del_secchars($a);
        }

		$a = trim($a);
		$a = preg_replace("/&quot;/i", "\"", $a);
		$returnWords = array();

		//get all phrases
		$regs = Array();
		while (preg_match("/([-]?)\"([^\"]+?)\"/i", $a, $regs)) {
			print_r($regs);
			if ($regs[1] == '') {
				$returnWords['+s'][] = $regs[2];
				$returnWords['hilight'][] = $regs[2];
			} else {
				$returnWords['-s'][] = $regs[2];
			}
			$a = str_replace($regs[0], "", $a);
		}

        if (Config::getInstance() -> CASE_SENSITIVE) {
            $a = preg_replace("/[ ]+/i", " ", $a);
        } else {
            $a = preg_replace("/[ ]+/", " ", $a);
        }

        //  $a = remove_accents($a);
		$a = trim($a);
		$words = explode(' ', $a);
		if ($a=="") {
			$limit = 0;
		} else {
			$limit = count($words);
		}

		$k = 0;
		//get all words (both include and exlude)
		$includeWords = array();
		while ($k < $limit) {
			if (substr($words[$k], 0, 1) == '+') {
				$includeWords[] = substr($words[$k], 1);
				if (!Utils::ignoreWord(substr($words[$k], 1))) {
					$returnWords['hilight'][] = substr($words[$k], 1);

				}
			} else if (substr($words[$k], 0, 1) == '-') {
				$returnWords['-'][] = substr($words[$k], 1);
			} else {
				$includeWords[] = $words[$k];
				if (!Utils::ignoreWord($words[$k])) {
					$returnWords['hilight'][] = $words[$k];

				}
			}
			$k++;
		}

		//add words from phrases to includes
		if (isset($returnWords['+s'])) {
			foreach ($returnWords['+s'] as $phrase) {
                if (Config::getInstance() -> CASE_SENSITIVE == false) {
                    $phrase = Utils::lower_ent($phrase);
                    $phrase = Utils::lower_case(preg_replace("/[ ]+/i", " ", $phrase));
                } else {
                    $phrase = preg_replace("/[ ]+/i", " ", $phrase);
                }

				$phrase = trim($phrase);
				$temparr = explode(' ', $phrase);
				foreach ($temparr as $w)
					$includeWords[] = $w;
			}
		}

		foreach ($includeWords as $word) {
			if (!($word =='')) {
				if (Utils::ignoreWord($word)) {
					$returnWords['ignore'][] = $word;
				} else {
					$returnWords['+'][] = $word;
				}
			}

		}
		return $returnWords;
	}

	/**
	 * Converts all accent characters to ASCII characters.
	 *
	 * If there are no accent characters, then the string given is just returned.
	 *
	 *
	 * @param string $string Text that might have accent characters
	 * @return string Filtered string with replaced "nice" characters.
	 */
	public static function remove_accents($string) {
		if ( !preg_match('/[\x80-\xff]/', $string) )
			return $string;

		if ($this -> seems_utf8($string)) {
			$chars = array(
			// Decompositions for Latin-1 Supplement
			chr(195).chr(128) => 'A', chr(195).chr(129) => 'A',
			chr(195).chr(130) => 'A', chr(195).chr(131) => 'A',
			chr(195).chr(132) => 'A', chr(195).chr(133) => 'A',
			chr(195).chr(135) => 'C', chr(195).chr(136) => 'E',
			chr(195).chr(137) => 'E', chr(195).chr(138) => 'E',
			chr(195).chr(139) => 'E', chr(195).chr(140) => 'I',
			chr(195).chr(141) => 'I', chr(195).chr(142) => 'I',
			chr(195).chr(143) => 'I', chr(195).chr(145) => 'N',
			chr(195).chr(146) => 'O', chr(195).chr(147) => 'O',
			chr(195).chr(148) => 'O', chr(195).chr(149) => 'O',
			chr(195).chr(150) => 'O', chr(195).chr(153) => 'U',
			chr(195).chr(154) => 'U', chr(195).chr(155) => 'U',
			chr(195).chr(156) => 'U', chr(195).chr(157) => 'Y',
			chr(195).chr(159) => 's', chr(195).chr(160) => 'a',
			chr(195).chr(161) => 'a', chr(195).chr(162) => 'a',
			chr(195).chr(163) => 'a', chr(195).chr(164) => 'a',
			chr(195).chr(165) => 'a', chr(195).chr(167) => 'c',
			chr(195).chr(168) => 'e', chr(195).chr(169) => 'e',
			chr(195).chr(170) => 'e', chr(195).chr(171) => 'e',
			chr(195).chr(172) => 'i', chr(195).chr(173) => 'i',
			chr(195).chr(174) => 'i', chr(195).chr(175) => 'i',
			chr(195).chr(177) => 'n', chr(195).chr(178) => 'o',
			chr(195).chr(179) => 'o', chr(195).chr(180) => 'o',
			chr(195).chr(181) => 'o', chr(195).chr(182) => 'o',
			chr(195).chr(182) => 'o', chr(195).chr(185) => 'u',
			chr(195).chr(186) => 'u', chr(195).chr(187) => 'u',
			chr(195).chr(188) => 'u', chr(195).chr(189) => 'y',
			chr(195).chr(191) => 'y',
			// Decompositions for Latin Extended-A
			chr(196).chr(128) => 'A', chr(196).chr(129) => 'a',
			chr(196).chr(130) => 'A', chr(196).chr(131) => 'a',
			chr(196).chr(132) => 'A', chr(196).chr(133) => 'a',
			chr(196).chr(134) => 'C', chr(196).chr(135) => 'c',
			chr(196).chr(136) => 'C', chr(196).chr(137) => 'c',
			chr(196).chr(138) => 'C', chr(196).chr(139) => 'c',
			chr(196).chr(140) => 'C', chr(196).chr(141) => 'c',
			chr(196).chr(142) => 'D', chr(196).chr(143) => 'd',
			chr(196).chr(144) => 'D', chr(196).chr(145) => 'd',
			chr(196).chr(146) => 'E', chr(196).chr(147) => 'e',
			chr(196).chr(148) => 'E', chr(196).chr(149) => 'e',
			chr(196).chr(150) => 'E', chr(196).chr(151) => 'e',
			chr(196).chr(152) => 'E', chr(196).chr(153) => 'e',
			chr(196).chr(154) => 'E', chr(196).chr(155) => 'e',
			chr(196).chr(156) => 'G', chr(196).chr(157) => 'g',
			chr(196).chr(158) => 'G', chr(196).chr(159) => 'g',
			chr(196).chr(160) => 'G', chr(196).chr(161) => 'g',
			chr(196).chr(162) => 'G', chr(196).chr(163) => 'g',
			chr(196).chr(164) => 'H', chr(196).chr(165) => 'h',
			chr(196).chr(166) => 'H', chr(196).chr(167) => 'h',
			chr(196).chr(168) => 'I', chr(196).chr(169) => 'i',
			chr(196).chr(170) => 'I', chr(196).chr(171) => 'i',
			chr(196).chr(172) => 'I', chr(196).chr(173) => 'i',
			chr(196).chr(174) => 'I', chr(196).chr(175) => 'i',
			chr(196).chr(176) => 'I', chr(196).chr(177) => 'i',
			chr(196).chr(178) => 'IJ',chr(196).chr(179) => 'ij',
			chr(196).chr(180) => 'J', chr(196).chr(181) => 'j',
			chr(196).chr(182) => 'K', chr(196).chr(183) => 'k',
			chr(196).chr(184) => 'k', chr(196).chr(185) => 'L',
			chr(196).chr(186) => 'l', chr(196).chr(187) => 'L',
			chr(196).chr(188) => 'l', chr(196).chr(189) => 'L',
			chr(196).chr(190) => 'l', chr(196).chr(191) => 'L',
			chr(197).chr(128) => 'l', chr(197).chr(129) => 'L',
			chr(197).chr(130) => 'l', chr(197).chr(131) => 'N',
			chr(197).chr(132) => 'n', chr(197).chr(133) => 'N',
			chr(197).chr(134) => 'n', chr(197).chr(135) => 'N',
			chr(197).chr(136) => 'n', chr(197).chr(137) => 'N',
			chr(197).chr(138) => 'n', chr(197).chr(139) => 'N',
			chr(197).chr(140) => 'O', chr(197).chr(141) => 'o',
			chr(197).chr(142) => 'O', chr(197).chr(143) => 'o',
			chr(197).chr(144) => 'O', chr(197).chr(145) => 'o',
			chr(197).chr(146) => 'OE',chr(197).chr(147) => 'oe',
			chr(197).chr(148) => 'R',chr(197).chr(149) => 'r',
			chr(197).chr(150) => 'R',chr(197).chr(151) => 'r',
			chr(197).chr(152) => 'R',chr(197).chr(153) => 'r',
			chr(197).chr(154) => 'S',chr(197).chr(155) => 's',
			chr(197).chr(156) => 'S',chr(197).chr(157) => 's',
			chr(197).chr(158) => 'S',chr(197).chr(159) => 's',
			chr(197).chr(160) => 'S', chr(197).chr(161) => 's',
			chr(197).chr(162) => 'T', chr(197).chr(163) => 't',
			chr(197).chr(164) => 'T', chr(197).chr(165) => 't',
			chr(197).chr(166) => 'T', chr(197).chr(167) => 't',
			chr(197).chr(168) => 'U', chr(197).chr(169) => 'u',
			chr(197).chr(170) => 'U', chr(197).chr(171) => 'u',
			chr(197).chr(172) => 'U', chr(197).chr(173) => 'u',
			chr(197).chr(174) => 'U', chr(197).chr(175) => 'u',
			chr(197).chr(176) => 'U', chr(197).chr(177) => 'u',
			chr(197).chr(178) => 'U', chr(197).chr(179) => 'u',
			chr(197).chr(180) => 'W', chr(197).chr(181) => 'w',
			chr(197).chr(182) => 'Y', chr(197).chr(183) => 'y',
			chr(197).chr(184) => 'Y', chr(197).chr(185) => 'Z',
			chr(197).chr(186) => 'z', chr(197).chr(187) => 'Z',
			chr(197).chr(188) => 'z', chr(197).chr(189) => 'Z',
			chr(197).chr(190) => 'z', chr(197).chr(191) => 's',
			// Euro Sign
			chr(226).chr(130).chr(172) => 'E',
			// GBP (Pound) Sign
			chr(194).chr(163) => '');

			$string = strtr($string, $chars);
		} else {
			// Assume ISO-8859-1 if not UTF-8
			$chars['in'] = chr(128).chr(131).chr(138).chr(142).chr(154).chr(158)
				.chr(159).chr(162).chr(165).chr(181).chr(192).chr(193).chr(194)
				.chr(195).chr(196).chr(197).chr(199).chr(200).chr(201).chr(202)
				.chr(203).chr(204).chr(205).chr(206).chr(207).chr(209).chr(210)
				.chr(211).chr(212).chr(213).chr(214).chr(216).chr(217).chr(218)
				.chr(219).chr(220).chr(221).chr(224).chr(225).chr(226).chr(227)
				.chr(228).chr(229).chr(231).chr(232).chr(233).chr(234).chr(235)
				.chr(236).chr(237).chr(238).chr(239).chr(241).chr(242).chr(243)
				.chr(244).chr(245).chr(246).chr(248).chr(249).chr(250).chr(251)
				.chr(252).chr(253).chr(255);

			$chars['out'] = "EfSZszYcYuAAAAAACEEEEIIIINOOOOOOUUUUYaaaaaaceeeeiiiinoooooouuuuyy";

			$string = strtr($string, $chars['in'], $chars['out']);
			$double_chars['in'] = array(chr(140), chr(156), chr(198), chr(208), chr(222), chr(223), chr(230), chr(240), chr(254));
			$double_chars['out'] = array('OE', 'oe', 'AE', 'DH', 'TH', 'ss', 'ae', 'dh', 'th');
			$string = str_replace($double_chars['in'], $double_chars['out'], $string);
		}

		return $string;
	}

	/**
	 * Taken from spider search
	 * @param unknown_type $arr
	 */
	public static  function unique_array($arr) {
		$common = null;

		sort($arr);
    	reset($arr);
    	$newarr = array ();
    	$i = 0;
    	$counter = 1;

        $element = current($arr);

        if (Config::getInstance() -> INDEX_NUMBERS == false) {
            $pattern = "/[0-9]+/";
        } else {
            $pattern = "/[ ]+/";
        }

        $regs = Array ();
        for ($n = 0; $n < sizeof($arr); $n ++) {
            //check if word is long enough, does not contain characters as defined in $pattern and is not a common word
            //to eliminate/count multiple instance of words
            $next_in_arr = next($arr);

            if (strlen($next_in_arr) >= Config::getInstance() -> MIN_WORD_LENGTH || !$next_in_arr) {

                if (Config::getInstance() -> CASE_SENSITIVE == true) {   //  compare words by means of upper and lower case characters (e.g. for Chinese language)
                    if ($next_in_arr != $element) {
                        if (strlen($element) >= Config::getInstance() -> MIN_WORD_LENGTH && !preg_match($pattern, $element) && ($common[$element] != 1)) {
                            if (preg_match("/^(-|\\\')(.*)/", $element, $regs))
                                $element = $regs[2];

                            if (preg_match("/(.*)(\\\'|-)$/", $element, $regs))
                                $element = $regs[1];
 							$newarr[$i] = new \BrightSearch\Keyword($element, $counter);
                            $element = current($arr);
                            $i ++;
                            $counter = 1;
                        } else {
                            $element = $next_in_arr;
                            $counter = 1;   //  otherwise the count will be the amount of skipped words
                        }
                    } else {
                        if ($counter < Config::getInstance() -> WORD_UPPER_BOUND)
                            $counter ++;
                    }

                } else {        //  compare all words only using lower case characters

                    if ($next_in_arr != $element) {
                        if (strlen($element) >= Config::getInstance() -> MIN_WORD_LENGTH && !preg_match($pattern, $element) && ($common[strtolower($element)] != 1)) {
                            if (preg_match("/^(-|\\\')(.*)/", $element, $regs))
                                $element = $regs[2];

                            if (preg_match("/(.*)(\\\'|-)$/", $element, $regs))
                                $element = $regs[1];

 							$newarr[$i] = new \BrightSearch\Keyword($element, $counter);
                            $element = current($arr);
                            $i ++;
                            $counter = 1;
                        } else {
                            $element = $next_in_arr;
                            $counter = 1;   //  otherwise the count will be the amount of skipped words
                        }
                    } else {
                        if ($counter < Config::getInstance() -> WORD_UPPER_BOUND)
                            $counter ++;
                    }
                }
            }
        }

    	return $newarr;
    }

	/**
	 * From sphider
	 */
	public static function _check_robot_txt($url, $robots) {
       // global $user_agent, $clear;

        $urlparts = Utils::parse_addr($url);
        if ($urlparts['host'] == 'localhost') {     //  for 'localhost' applications add the path until last slash
            $loc_path = substr($urlparts['path'], 0, strrpos($urlparts['path'], '/'));
            $url = 'http://'.$urlparts['host']."".$loc_path."/$robots";
        } else {    //      www application
            $url = 'http://'.$urlparts['host']."/$robots";
        }

    	$url_status = url_status($url);
    	$omit = array ();

    	if ($url_status['state'] == "ok") {
    		$robot = @file($url);
    		if (!$robot) {
                $get_charset    = '';
    			$contents = getFileContents($url, $get_charset);    //  read the robots.txt file
    			$file = $contents['file'];
    			$robot = explode("\n", $file);
    		}

    		$regs = Array ();
    		$this_agent= "";
    		while (list ($id, $line) = each($robot)) {
    			if (preg_match("/^user-agent: *([^#]+) */i", $line, $regs)) {
    				$this_agent = trim($regs[1]);
    				if ($this_agent == '*' || $this_agent == $user_agent)
    					$check = 1;
    				else
    					$check = 0;
    			}

    			if (preg_match("/disallow: *([^#]+)/i", $line, $regs) && $check == 1) {
    				$disallow_str = preg_replace("/[\n ]+/i", "", $regs[1]);
    				if (trim($disallow_str) != "") {
                        if ($urlparts['host'] == 'localhost') {     //  for 'localhost' applications add the path until last slash
                            $omit[] = "".$loc_path."".$disallow_str."";
                        } else {        //      www application
                            $omit[] = $disallow_str;
                        }
    				} else {
    					if ($this_agent == '*' || $this_agent == $user_agent) {
                            if ($clear == 1) unset ($urlparts, $contents, $file, $robot, $regs);
                            return null;
    					}
    				}
    			}
    		}
    	}
        if ($clear == 1) unset ($urlparts, $contents, $file, $robot, $regs);
    	return $omit;       //     array that holds all forbidden links from robots.txt
    }

    /**
     * Delete additional characters (as word separator) like dots, question marks, colons etc. (characters 1-49 in original Chinese dictionary)
     * @var file The file to clean
     */
    public static function del_secchars($file){
	    $file = preg_replace ('/。|，|〿|；|：|？|＿|…|—|·|ˉ|ˇ|¨|‘|’|“|‿|々|～|‖|∶|＂|＇|｀|｜|〃|〔|〕|〈|〉|《|》|「|〿|『|〿|．|〖|〗|〿|】|（|）|［|］|｛|ｿ/', " ", $file);
		$file = preg_replace('/ï¼›|¡£|£¬|¡¢|£»|£º|£¿|£¡|¡­|¡ª|¡¤|¡¥|¡¦|¡§|¡®|¡¯|¡°|¡±|¡©|¡«|¡¬|¡Ã|£¢|£§|£à|£ü|¡¨|¡²|¡³|¡´|¡µ|¡¶|¡·|¡¸|¡¹|¡º|¡»|£®|¡¼|¡½|¡¾|¡¿|£¨|£©|£Û|£ÿ|£û|£ý|°¢/', " ", $file);
		$file = preg_replace('/＿|＆|，|<|：|；|・|\(|\)/', " ", $file);
		$file = preg_replace('/,|\. |\.\. |\.\.\. |!|\? |" |: |\) |\), |\). |】 |） |？,|？ |！ |！|。,|。 |„ |“ |” |”|”&nbsp;|» |.»|;»|:»|,»|.»|·»|«|« |», |». |.” |,”|;” |”. |”, |‿|、|）|·|;|\] |\} /', " ", $file);
		$file = preg_replace('/ \[| "| \(| „| “|（| «| 【| ‿| （/', " ", $file);     //    kill special characters in front of words
		$file = preg_replace('/・/', " ", $file);     //    kill separating characters inside of words


		return $file;

    }

    public static function file_get_contents_utf8($fn) {

    	$context = stream_context_create(array(
			    'http' => array(
			        'method' => 'POST',
			        'header' => implode("\r\n", array(
			                'Content-type: text/html',
			                'Accept-Language: nl-NL,nl;q=0.8,en-US;q=0.6,en;q=0.4', // optional
			                'Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7' // optional
			        ))
			    )
		));

    	$content = file_get_contents($fn, FILE_TEXT, $context);
    	
    	//Content empty? Try again...
    	if(trim($content) == '')
    		$content = file_get_contents($fn);
    	
		return mb_convert_encoding($content, 'UTF-8', mb_detect_encoding($content, 'UTF-8, ISO-8859-1', true));
	}

	public static function url_status($url, $urlparts) {


        $url0       = $url;
        $state      = array();

    	$path       = array_key_exists('path', $urlparts) ? $urlparts['path'] : '/';
    	$host       = $urlparts['host'];

    	if (isset($urlparts['query']))
    		$path .= "?".$urlparts['query'];

    	if (isset ($urlparts['port'])) {
    		$port = (int) $urlparts['port'];
    	} else
    		if ($urlparts['scheme'] == "http") {
    			$port = 80;
    		} else
    			if ($urlparts['scheme'] == "https") {
    				$port = 443;
    			}

    	if ($port == 80) {
    		$portq = "";
    	} else {
    		$portq = ":$port";
    	}
        //ini_set("user_agent", $user_agent);
        $user_agent = ini_get('user_agent');
        $all = "*/*"; //just to prevent "comment effect" in get accept
        //  request with first authorization
        $request1 = "GET $path HTTP/1.1\r\nHost: $host$portq\r\nAccept: $all\r\nUser-Agent: $user_agent\r\n\r\n";//$auth\r\n\r\n";

    	if (substr($url, 0, 5) == "https") {
    		$target = "ssl://".$host;
    	} else {
    		$target = $host;
    	}
    	$fp = self::_openSocket($target, $port);


    	$linkstate = "ok";
    	if (!$fp) {
    		$status['state'] = "NOHOST";
    	} else {
    		socket_set_timeout($fp, 60);
    		fputs($fp, $request1);
    		$answer = fgets($fp, 4096);

            if (strpos($answer, "500")) {  // try with standard browser http_user_agent (some servers do not like crawler)
                fclose($fp);    // close existing connection
                sleep(1);       //  might not be necessary to wait, but . . .

                $browser_agent      = "Mozilla/5.0 (Windows NT 6.1; rv:5.0) Gecko/20100101 Firefox/5.0";
                $browser_request    = "GET $path HTTP/1.1\r\nHost: $host$portq\r\nAccept: $all\r\nUser-Agent: $browser_agent\r\n\r\n";

                //try to re-connect
                $fp = self::_openSocket($target, $port);

                $linkstate = "ok";
                if (!$fp) {
                    $status['state'] = "NOHOST";
                } else {
                    fputs($fp, $browser_request);
                    $answer = fgets($fp, 4096);
                   // ini_set("user_agent", $browser_agent);  //      overwrite $user_agent with $browser_agent
                }
            }
            if (strpos($answer, "401")) {    //  try authorization
                fclose($fp);
                $call = '1';
                $fp = self::_openSocket($target, $port);
                $linkstate = "ok";
                if (!$fp) {
                    $status['state'] = "NOHOST";
                } else {
					$auth = sprintf("Authorization: Basic %s", base64_encode(Config::getInstance() -> AUTH_USER . ":" . Config::getInstance() -> AUTH_PASS));
        			$request1 = "GET $path HTTP/1.1\r\nHost: $host$portq\r\nAccept: $all\r\nUser-Agent: $user_agent\r\n$auth\r\n\r\n";
                    fputs($fp, $request1);
                    $answer = fgets($fp, 4096);
                }
            }

    		$regs = Array ();
    		if (preg_match("{HTTP/[0-9.]+ (([0-9])[0-9]{2})}i", $answer, $regs)) {
    			$httpcode = $regs[2];
    			$full_httpcode = $regs[1];

    			if ($httpcode <> 2 && $httpcode <> 3) {
    				//echo $answer;
    				$status['state'] = "Unreachable: http $full_httpcode";
    				$linkstate = "Unreachable";
    			}
    		}

    		if ($linkstate <> "Unreachable") {
    			while ($answer) {
    				$answer = fgets($fp, 4096);
    				if (preg_match("/Location: *([^\n\r ]+)/", $answer, $regs)) {;
    					$status['path'] = $regs[1];     //      URL redirected
    					$status['relocate'] = "Relocated by http $full_httpcode to ";
                    }

    				if (preg_match("/Last-Modified: *([a-z0-9,: ]+)/i", $answer, $regs)) {
    					$status['date'] = $regs[1];
    				}

    				if (preg_match("/Content-Type:/i", $answer)) {
    					$content = $answer;
    					$answer = '';
    					break;
    				}
    			}

    			//////////////////////////////////////////////////////////////////////////////
    			//////////////////////////////////////////////////////////////////////////////
    			//////////////////////////////////////////////////////////////////////////////
    			//////////////////////////////////////////////////////////////////////////////
    			////////////////////////							//////////////////////////
    			////////////////////////			TEMP			//////////////////////////
    			////////////////////////							//////////////////////////
    			//////////////////////////////////////////////////////////////////////////////
    			//////////////////////////////////////////////////////////////////////////////
    			//////////////////////////////////////////////////////////////////////////////
    			//////////////////////////////////////////////////////////////////////////////
    			//////////////////////////////////////////////////////////////////////////////
    			$index_doc = 0;
    			$index_rss = 0;
    			
    			$socket_status = socket_get_status($fp);
    			if (preg_match("{Content-Type: *([a-z/.-]*)}i", $content, $regs)) {

    				if ($regs[1] == 'text/html' || $regs[1] == 'text/' || $regs[1] == 'text/plain') {
    					$status['content'] = 'text';
    					$status['state'] = 'ok';

    				} else if ($regs[1] == 'application/pdf' && $index_pdf == 1) {
    					$status['content'] = 'pdf';
    					$status['state'] = 'ok';
    				} else if ($regs[1] == 'application/pdf' && $index_pdf == 0) {
    					$status['content'] = 'pdf';
    					$status['state'] = 'Indexing of PDF files is not activated in Admin Settings';

    				} else if (($regs[1] == 'application/msword' || $regs[1] == 'application/vnd.ms-word') && $index_doc == 1) {
    					$status['content'] = 'doc';
    					$status['state'] = 'ok';
    				} else if (($regs[1] == 'application/msword' || $regs[1] == 'application/vnd.ms-word') && $index_doc == 0) {
    					$status['content'] = 'doc';
    					$status['state'] = 'Indexing of DOC files is not activated in Admin Settings';

    				} else if (($regs[1] == 'text/rtf') && $index_rtf == 1) {
    					$status['content'] = 'rtf';
    					$status['state'] = 'ok';
    				} else if (($regs[1] == 'text/rtf') && $index_rtf == 0) {
    					$status['content'] = 'rtf';
    					$status['state'] = 'Indexing of RTF files is not activated in Admin Settings';

    				} else if (($regs[1] == 'application/excel' || $regs[1] == 'application/vnd.ms-excel') && $index_xls == 1) {
    					$status['content'] = 'xls';
    					$status['state'] = 'ok';
    				} else if (($regs[1] == 'application/excel' || $regs[1] == 'application/vnd.ms-excel') && $index_xls == 0) {
    					$status['content'] = 'xls';
    					$status['state'] = 'Indexing of XLS files is not activated in Admin Settings';

    				} else if (($regs[1] == 'text/csv') && $index_csv == 1) {
    					$status['content'] = 'csv';
    					$status['state'] = 'ok';
    				} else if (($regs[1] == 'text/csv') && $index_csv == 0) {
    					$status['content'] = 'csv';
    					$status['state'] = 'Indexing of CSV files is not activated in Admin Settings';

    				} else if (($regs[1] == 'application/mspowerpoint' || $regs[1] == 'application/vnd.ms-powerpoint') && $index_ppt == 1) {
    					$status['content'] = 'ppt';
    					$status['state'] = 'ok';
    				} else if (($regs[1] == 'application/mspowerpoint' || $regs[1] == 'application/vnd.ms-powerpoint') && $index_ppt == 0) {
    					$status['content'] = 'ppt';
    					$status['state'] = 'Indexing of PPT files is not activated in Admin Settings';

    				} else if (($regs[1] == 'application/xml' || $regs[1] == 'application/rss' || $regs[1] == 'text/xml') && $index_rss == 1) {
    					$status['content'] = 'xml';
    					$status['state'] = 'ok';
       				} else if (($regs[1] == 'application/xhtml' || $regs[1] == 'application/rss' || $regs[1] == 'text/xhtml' || $regs[1] == 'application/xhtml') && $index_rss == 1) {
    					$status['content'] = 'xhtml';
    					$status['state'] = 'ok';
    				} else if (($regs[1] == 'application/xml' || $regs[1] == 'application/rss' || $regs[1] == 'text/xml' || $regs[1] == 'text/xhtml' || $regs[1] == 'application/xhtml') && $index_rss == 0) {
    					$status['content'] = 'xml';
    					$status['state'] = 'Indexing of RDF, RSD, RSS and Atom feeds is not activated in Admin Settings';

                    } else if (($regs[1] == 'application/zip' || $regs[1] == 'zip') && $index_zip == 1) {
    					$status['content'] = 'zip';
    					$status['state'] = 'ok';
                    } else if (($regs[1] == 'application/zip' || $regs[1] == 'zip') && $index_zip == 0) {
    					$status['content'] = 'zip';
    					$status['state'] = 'Indexing of ZIP archives is not activated in Admin Settings';

                    } else if (($regs[1] == 'application/rar' || $regs[1] == 'application/x-rar-compressed') && $index_rar == 1) {
    					$status['content'] = 'rar';
    					$status['state'] = 'ok';
                    } else if (($regs[1] == 'application/rar' || $regs[1] == 'application/x-rar-compressed') && $index_rar == 0) {
    					$status['content'] = 'rar';
    					$status['state'] = 'Indexing of RAR archives is not activated in Admin Settings';

                    } else if (($regs[1] == 'application/vnd.oasis.opendocument.spreadsheet') && $index_ods == 1) {
    					$status['content'] = 'ods';
    					$status['state'] = 'ok';
                    } else if (($regs[1] == 'application/vnd.oasis.opendocument.spreadsheet') && $index_ods == 0) {
    					$status['content'] = 'ods';
    					$status['state'] = 'Indexing of OpenDocument<strong>Spreadsheet</strong> is not activated in Admin Settings';

                    } else if (($regs[1] == 'application/vnd.oasis.opendocument.text') && $index_odt == 1) {
    					$status['content'] = 'odt';
    					$status['state'] = 'ok';
                    } else if (($regs[1] == 'application/vnd.oasis.opendocument.text') && $index_odt == 0) {
    					$status['content'] = 'odt';
    					$status['state'] = 'Indexing of OpenDocument<strong>Text</strong> is not activated in Admin Settings';

                    } else {
    					$status['state'] = "For Sphider-plus  not executable Text or Media. Might be:<br />JavaScript, unsupported XML or unknown feed content => UFO file<br />";

    				}

    			} else
    				if ($socket_status['timed_out'] == 1) {
    					$status['state'] = "Timed out. URL: $url0 <br />No reply from server within $fsocket_timeout seconds.";

    				} else
    					$status['state'] = "Not text or html";

    		}
    	}
    	fclose($fp);

    	return $status;
    }

    private static function _openSocket($target, $port) {
    	$fsocket_timeout = 120;
    	$errno = 0;
    	$errstr = "";

    	$fp = fsockopen($target, $port, $errno, $errstr, $fsocket_timeout);
		if($errstr != '') {
    		echo 'Fsock error: ' . $errstr;
		}
		return $fp;
    }

}