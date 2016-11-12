<?php
namespace BrightSearch;

// error_reporting(E_ALL|E_STRICT);

class Search {
	private $_wildcount;
	private $_prefix;
	private $_conn;
	private $_strict = false;

	private $_possibleToFind = true;
	private $_break = 1;

	function __construct() {
		$this -> _conn = \Connection::getInstance();
		// Force utf-8
		header('Content-Type: text/html; charset=utf-8');
		
		Config::getInstance() -> setup();

		$this -> _prefix = Config::getInstance() -> TABLE_PREFIX;
	}

	/**
	 * Searches the site for the given query
	 * @param string $query
	 */
	public function searchSite($query, $page = 1) {
		if($page < 1)
			$page = 1;
		
		$query = filter_var($query, FILTER_SANITIZE_STRING);
        $orig_query = $query;
		$strictpos = strpos($query, '!'); // if  ! is in position 0, we have to search strict
    	$this -> _wildcount = substr_count($query, '*');
    	$type = Config::getInstance() -> DEFAULT_SEARCH_TYPE;

    	$this -> _strict = false;
    	$mustbe_and = false;

		if ($this -> _wildcount || $strictpos === 0) {
			if ($type != 'and') {
				$mustbe_and = true;
			}
			$type = 'and';                  //  if wildcard, or strict search mode, switch always to AND search
			$this -> _strict  = true;          //  prevent wildcard for quotes search
			if(strpos($query, " ", 3)) {
				$query = substr($query, 0, strpos($query, " ", 3)); // only the first word of the query will be used for these search modes
				$one_word = true;
        	}
		}

		if ($type != "or" && $type != "and" && $type != "phrase" && $type != "tol") {
			$type = "and";
		}

        //  if search with wildcards is activated in Admin settings for queries containing numbers
        if (Config::getInstance() -> WILD_NUM == true && preg_match("/[0-9]/i", $query ) && !strstr($query, " ") && !strstr($query, "*")) {
            $query = "*$query*";
        }

        if ($this -> _wildcount || $strictpos === 0 || $type =='tol') {  //  if wildcard, strict or tolerant search mode, we have to search a lot but only for the first word
            $first = strpos($query, ' ');
            if ($first) {
                $query = substr($query, 0, $first);
            }
        }

        $query      = str_replace('http://', '', $query);    // URL's are stored without this in database
        $query      = preg_replace("/&nbsp;/", " ", $query); // replace '&nbsp;' with " "
        $query      = preg_replace("/&apos;/", "'", $query); // replace '&apos;' with " ' "
        $multi_word = strpos($query, " ");      			 // check, whether the query contains a 'blank' character?

        //  if search without quotes is activated in Admin settings
        if (Config::getInstance() -> NO_QUOTES == true && !$multi_word) {
            $query = preg_replace("/&#8216;|&lsquo;|&#8217;|&rsquo;|&#8242;|&prime;|‘|‘|´|`/", "'", $query);
            $quote = strstr($query, "'");
            if ($quote && !$strict_search) {
                $q_pos = strpos($query, "'");
                $word1 =  substr($query, 0, $q_pos);
                $word2 =  substr($query, $q_pos+1);
                $query = '';
                if (strlen($word1) >= Config::getInstance() -> MIN_WORD_LENGTH) {
                    $query = $word1;
                }

                if (strlen($word2) >= Config::getInstance() -> MIN_WORD_LENGTH	) {
                    $query .= "*$word2";
                }
            }
        }
        
        if ($query == ''){    //  don't care about 'blank' queries
            return null;
        }
    	$starttime  = microtime(true);

		if ($type == "phrase") {
			$query = str_replace('"','',$query);
			$query = "\"".$query."\"";
		}

		// catch " if only entered once
		if (substr_count($query,'\"')==1){
			$query = str_replace('\"','',$query);
		}

		if (Config::getInstance() -> CASE_SENSITIVE == false && $type != "phrase") {
			$query = Utils::lower_ent($query);
			$query = Utils::lower_case($query);
		}

		$words = Utils::makeboollist($query, $type);
		$ignorewords = array_key_exists('ignore',$words) ? $words['ignore'] : null;


        if (is_array($ignorewords)) {
            $full_result['ignore_words'] = $words['ignore'];
        }
        // We're all set for the search!
		$result = $this -> _search($words, $page, Config::getInstance() -> RESULTS_PER_PAGE, $type);
		return $result;
	}

	private function _buildLinkList($keywords, $searchword, $mode, $type) {
		$i = 0;
		$linklist = array();
		if($keywords) {
			while(list($key, $result) = each($keywords)) {
                $keywordId = $result -> keywordId;
                $keyword = $this -> _conn -> escape_string($result -> keyword);
                $searchword = $mode == 'normal' ? $keyword : $this -> _conn -> escape_string($searchword);
                $wordmd5 = substr(md5($keyword), 0, 1);             // calculate attribute for link_keyword table

				$accept = true;
				if ($mode == 'tol' && mb_strlen($keyword) != mb_strlen($searchword)){     //  use only those results with same length as searchword
					$accept = false;
				}
				if($accept) {
					if (Config::getInstance() -> DEFAULT_SEARCH_SORT == Config::SORT_HITCOUNTS) {
						// get query hit results
						if($mode == 'strict' || $mode = 'normal') {
							$whereclause= "kw.keywordId AND keyword='$searchword'";
							$distinct = 'distinct';
							$tbls = ", {$this -> _prefix}keywords kw";
						} else {
							$distinct = '';
							$tbls = '';
							$whereclause = "'$keywordId'";
						}

						$sql = "SELECT $distinct linkId, hits, indexdate
								FROM {$this -> _prefix}link_keyword$wordmd5 lk $tbls
								WHERE lk.keywordId= $whereclause
								ORDER BY hits DESC";


					} else {
						// get weight results
						$join = '';
						$mdsort = '';
						$mdfield = '';
						if(Config::getInstance() -> DEFAULT_SEARCH_SORT == Config::SORT_MICRODATA) {
							$sf = Config::getInstance() -> MICRODATA_SORT_FIELD;
							$mdsort = 'value, ';
							$mdfield = 'value, ';
							$join = "LEFT JOIN {$this -> _prefix}microdata md ON md.linkId = lk.linkId AND name='$sf'";
						}
						if($mode == 'normal') {
							$sql = "SELECT DISTINCT lk.linkId, weight, $mdfield lk.indexdate
									FROM {$this -> _prefix}link_keyword$wordmd5 lk
									INNER JOIN {$this -> _prefix}keywords kw ON lk.keywordId = kw.keywordId AND keyword='$searchword'
									$join
									ORDER BY $mdsort weight DESC";
						} else {
							$sql = "SELECT lk.linkId, weight, lk.indexdate $mdfield
									FROM {$this -> _prefix}link_keyword$wordmd5 lk
									$join
									WHERE keywordId = '{$keywordId}'
									ORDER BY $mdsort weight DESC";
						}
					}

					$linkIds = $this -> _conn -> getRows($sql);

					if($linkIds) {

						foreach($linkIds as $row) {
							$linklist[$i]['id'][] = $row -> linkId;

		                    if (Config::getInstance() -> DEFAULT_SEARCH_SORT == Config::SORT_INDEXDATE) {
		                        // use indexdate
		                        $linklist[$i]['weight'][$row -> linkId] = $row -> indexdate;
		                    } else if(Config::getInstance() -> DEFAULT_SEARCH_SORT == Config::SORT_MICRODATA) {
		                    	// Multiply if Microdata is found
		                    	$mp = ($row -> value) ? 10 : 1;
								$linklist[$i]['weight'][$row -> linkId] = $row -> weight * $mp;
		                    } else {
		                        // use weight
		                        $linklist[$i]['weight'][$row -> linkId] = $row -> weight;
		                    }

							if ($mode != 'tol' && Config::getInstance() -> DEFAULT_SEARCH_SORT == Config::SORT_HITCOUNTS) {
								// ensure that result is also available in full text
		                        $fullTxt = $this -> _conn -> getField("SELECT fulltxt FROM {$this -> _prefix}links where linkId = '{$row -> linkId}'");

		                        if (!Config::getInstance() -> CASE_SENSITIVE) {
		                            $fullTxt= Utils::lower_ent($fullTxt);
		                            $fullTxt = Utils::lower_case($fullTxt);
		                        }
		                        if($mode == 'wildcard') {
		                        	$searchword = str_replace("%", '', $searchword);
		                        } else if($mode == 'normal' && $type == 'phrase') {
		                        	$searchword = $phrase_query;
		                        }

								$foundIt = substr_count($fullTxt, $searchword);
		                        $linklist[$i]['weight'][$row -> linkId] = $foundIt;

		                        if($mode == 'strict' && $foundIt > 0) {
									$j = 0;
									while($j < $foundIt) {
										$foundIn = strpos($fullTxt, $searchword);
										$tmpFront = substr($fullTxt, $foundIn - 1, 20); //  one character before found match position
										$pos = $foundIn + strlen($searchword);
										$tmpBehind = substr($fullTxt, $pos, 20); //  one character behind found match position
										$fullTxt = substr($fullTxt, $pos);  //  get rest of fulltxt
		                                //  check whether found match is realy strict
		                                $foundBefore = preg_match("/[(a-z)-_*.\/\:&@\w]/", substr($tmpFront, 0, 1));
		                                $foundBehind = preg_match("/[(a-z)-_*.,\/\:&@\w]/", substr($tmpBehind, 0, 1));

		                                if ($foundBefore == 1 || $foundBehind == 1) {
		                                	// correct count of hits
		                                    $linklist[$i]['weight'][$row -> linkId]--;
		                                }

										$j++;
									}
								}
							}
						}
						if ($type == "or" && Config::getInstance() -> DEFAULT_SEARCH_SORT == Config::SORT_HITCOUNTS) {
                            $i = 0;
                        } else {
                        	$i++;
                        }
					} else {
						if($mode == 'normal' && $type != 'or') {
							$this -> _possibleToFind = false;
							break;
						}
					}
				}
			}
		} else {
			$this -> _break = 1;
			$this -> _possibleToFind = false;
		}
		return $linklist;
	}

	/**
	 *
	 * @param array $searchstr
	 * @param int $start
	 * @param int $per_page
	 * @param string $type
	 * @param boolean $strictsearch
	 */
	private function _search($searchstr, $start, $per_page, $type) {

        $res = $this -> _slave_search ($searchstr, $start, $per_page, $type);
        $res1 = $res;
		$res = array_slice($res, 0, Config::getInstance() -> MAX_RESULTS, TRUE);

        if (array_key_exists('did_you_mean',$res1)){     //  for translit to Greeek use result array 1
    		return $res1;
        }

        $all = count($res);
		if ($all == 0) {
			return null;
		}
		if (Config::getInstance() -> DEFAULT_SEARCH_SORT == Config::SORT_MOSTPOPULAR) {             //      enter here if 'Most Popular Click' on top of listing
			sort_by_bestclick($res);
		} else {
			usort($res, '\BrightSearch\Utils::cmp_weight');
		}

        $results = count ($res);  //  total amount of results

        //  reduce results for one page in result listing
        $offset = ($start-1) * $per_page;
        $res = array_slice($res, $offset, $per_page);

        $return = array('results' => $res);
		$return['maxweight'] = $res[0] -> maxweight;
		$return['numresults'] = $results;
        $return['hilight'] = $searchstr['hilight'];

		return $return;
	}


    private function _slave_search($searchstr, $start, $per_page, $type) {
    	$linklist = array();
    	$res = array();
		$this -> possibleToFind = true;
		
        $notlist = array();
		//find all sites that should not be included in the result
		if (count($searchstr['+']) == 0) {
			return $notlist;
		}
		$wordarray = array_key_exists('-', $searchstr) ? $searchstr['-'] : null;
		$not_words = 0;

		while ($not_words < count($wordarray)) {

			$searchword = addslashes($wordarray[$not_words]);

			$wordmd5 = substr(md5($searchword), 0, 1);
            $query1 = "SELECT linkId
            			FROM {$this -> _prefix}link_keyword$wordmd5 lk, {$this -> _prefix}keywords kw
            			WHERE lk.keywordId= kw.keywordId
            			AND keyword='$searchword'";
			$result = $this -> _conn -> getRows($query1);

			foreach($result as $row) {
				$notlist[$not_words]['id'][$row -> linkId] = 1;
			}
			$not_words++;
		}

		if(count($notlist) == 0) {
			$not_words = 0;
		}
		//find all sites containing the search phrase
		$wordarray = array_key_exists('+s', $searchstr) ? $searchstr['+s'] : null;
		$phrase_words = 0;
		// It's probably impossible to get more than one phrase words
		while ($phrase_words < count($wordarray)) {

			$searchword = $this -> _conn -> escape_string($wordarray[$phrase_words]);
            $phrase_query = $searchword;

            //  search for phrase in fulltext
            if (Config::getInstance() -> CASE_SENSITIVE == true) {
                $query1 = "SELECT id as `linkId` FROM {$this -> _prefix}search WHERE text LIKE '%$searchword%'";
            } else {
                $searchword = Utils::lower_case($searchword);
                $query1 = "SELECT id as `linkId` FROM {$this -> _prefix}search WHERE CONVERT(LOWER(text)USING utf8)  LIKE '%$searchword%'";
            }

			$result = $this -> _conn -> getRows($query1);
			$num_rows = count($result);

			if ($num_rows == 0) {
                // phrase not found in fulltext. Now try to find in title tag
                if (Config::getInstance() -> CASE_SENSITIVE == true) {
                    $query1 = "SELECT id as `linkId` FROM {$this -> _prefix}search WHERE title LIKE '%$searchword%'";
                } else {
                    $searchword = Utils::lower_case($searchword);
                    $query1 = "SELECT id as `linkId` FROM {$this -> _prefix}search WHERE CONVERT(LOWER(title)USING utf8) LIKE '%$searchword%'";
                }

    			$result = $this -> _conn -> getRows($query1);

				$num_rows = count($result);

                if ($num_rows == 0) {
     				$this -> possibleToFind = false;
    				break;
                }
			}

			foreach($result as $row) {
				$phraselist[$phrase_words]['id'][$row -> linkId] = 1;
				$phraselist[$phrase_words]['val'][$row -> linkId] = $row -> linkId;
			}
			$phrase_words++;
		}

		//find all sites that include the search word
		$wordarray = $searchstr['+'];
		$words = 0;
        $searchword = trim($this -> _conn -> escape_string($wordarray[$words]));   //  get only first word of search query

        $strictpos = strpos($searchword, '!'); //   if  ! is in position 0, we have to search strict

        if($this -> _strict) {
        	// Find exact keyword
        	$keywordsquery = "SELECT keywordId, keyword from {$this -> _prefix}keywords where keyword = '$searchword'";
        	$linklist = $this -> _buildLinkList($this -> _conn -> getRows($keywordsquery), $searchword, 'strict', $type);
        } else {

        	// Query contains wildcard
        	$wildcount = substr_count($searchword, '*');
            if ($wildcount) {
                $searchword = str_replace('*','%', $searchword);
                $keywordsquery = "SELECT keywordId, keyword from {$this -> _prefix}keywords where keyword like '$searchword'";
	        	$linklist = $this -> _buildLinkList($this -> _conn -> getRows($keywordsquery), $searchword, 'wildcard', $type);

        	} else {

        		if($type == 'tol') {
	        		// Tolerant search
					$searchword = Utils::remove_accents($searchword);
			        $get = array("a", "c", "e", "i", "o", "u");
			        $out = array("%", "%", "%", "%", "%", "%");
			        $searchword = str_ireplace($get, $out, $searchword);

			        $keywordsquery = "SELECT keywordId, keyword from {$this -> _prefix}keywords where keyword like '$searchword'";
		        	$linklist = $this -> _buildLinkList($this -> _conn -> getRows($keywordsquery), $searchword, 'tolerant', $type);
        		} else {
        			// Default search
        			$arr = array();
        			foreach($wordarray as $word) {
        				$arr[] = new Keyword($word);
        			}
		        	$linklist = $this -> _buildLinkList($arr, $searchword, 'normal', $type);
        		}
        	}
        }

		$words = count($linklist);


		$result_array_full = array();
		if($this -> possibleToFind && $words > 0) {
			if ($words == 1 && $not_words == 0) { // for OR-Sarch without query_hits and one word query, we already do have the result
				$result_array_full = $linklist[0]['weight'];
			} else {    //     otherwise build an intersection of all the results
				$j = 1;
				$min = 0;
				while ($j < $words) {
					if (count($linklist[$min]['id']) > count($linklist[$j]['id'])) {
						$min = $j;
					}
					$j++;
				}

				$j = 0;
				$temp_array = $linklist[$min]['id'];
				$count = 0;
				while ($j < count($temp_array)) {
					$k = 0; //and word counter
					$n = 0; //not word counter
					$o = 0; //phrase word counter
	                if (Config::getInstance() -> DEFAULT_SEARCH_SORT == Config::SORT_HITCOUNTS) {
	                    $weight = 0;
	                } else {
	                    $weight = 1;
	                }

					$this -> _break = 0;
	                if ($type == 'phrase' && Config::getInstance() -> DEFAULT_SEARCH_SORT == Config::SORT_HITCOUNTS) {    // for PHRASE search: find out how often the phrase was found in fulltxt (not for weighting %  scores)
	    				while ($k < $words && $this -> _break == 0) {
	    					if ($linklist[$k]['weight'][$temp_array[$j]] > 0) {
	                            $weight = $linklist[$k]['weight'][$temp_array[$j]];
	    					} else {
	    						$this -> _break = 1;
	    					}
	    					$k++;
	    				}

	                } else {
	                    while ($k < $words && $this -> _break == 0) {
	                        if (array_key_exists($temp_array[$j], $linklist[$k]['weight']) && $linklist[$k]['weight'][$temp_array[$j]] > 0) {

	                            if (Config::getInstance() -> DEFAULT_SEARCH_SORT == Config::SORT_INDEXDATE) {
	                                $weight = $linklist[$k]['weight'][$temp_array[$j]];     //  use indexdate
	                            } else {
	                                $weight = $weight + $linklist[$k]['weight'][$temp_array[$j]];   //  calculate weight
	                            }
	                        } else {
	                            $this -> _break = 1;
	                        }
	                        $k++;
	                    }

	                }
					while ($n < $not_words && $this -> _break == 0) {
						if (array_key_exists($temp_array[$j], $notlist[$n]['id']) && $notlist[$n]['id'][$temp_array[$j]] > 0) {
							$this -> _break = 1;
						}
						$n++;
					}

					while ($o < $phrase_words && $this -> _break == 0) {
						if (array_key_exists($temp_array[$j] , $phraselist[$o]['id']) && $phraselist[$o]['id'][$temp_array[$j]] != 1) {
							$this -> _break = 1;
						}
						$o++;
					}

					if ($this -> _break == 0) {
						$result_array_full[$temp_array[$j]] = $weight;
						$count ++;
					}
					$j++;
				}
			}
		}

		if ((count($result_array_full) == 0 || $this -> possibleToFind == false) && Config::getInstance() -> DID_YOU_MEAN_ENABLED == 1) {
			reset ($searchstr['+']);
			$near_words = array();
			foreach ($searchstr['+'] as $word) {
                $word2 = str_ireplace("Ã", "à", addslashes("$word"));
 				$max_distance = 100;
				$near_word ="";

                //  first try to find any keywords using the soundex algorithm
                $result = $this -> _conn -> getRows("SELECT keyword FROM {$this -> _prefix}keywords WHERE soundex(keyword) = soundex('$word2%')");

                if (!$result) {
                    //  if no match with first trial, try to find keywords with additional characters at the end
                    $result = $this -> _conn -> getRows("SELECT keyword FROM {$this -> _prefix}keywords WHERE keyword like '$word2%'");
                }

				foreach($result as $row) {
                    $distance = levenshtein($row -> keyword, $word);
					if ($distance < $max_distance && $distance <10) {
						$max_distance = $distance;
						$near_word = ($row -> keyword);
					}
				}
				if ($near_word != "" && $word != $near_word) {
					$near_words[$word] = $near_word;
				}
			}

            if ($this -> _wildcount == 0 && count($near_words) > 0) {   // No 'Did you mean' for wildcount search
    			$res['did_you_mean'] = $near_words;
    			return $res;
            }
		}
        //  limit amount of results in result listing
        $result_array_full = array_slice($result_array_full, 0, Config::getInstance() -> MAX_RESULTS, TRUE);
		
        //return $result_array_full;

 		if (count($result_array_full) == 0) {
            $result_array_full = array();
			return $result_array_full;  // return blank array, otherwise array_merge() will not work in PHP5
		}

        if (array_key_exists('did_you_mean',$result_array_full)){
    		return $result_array_full;
        }

		arsort ($result_array_full);

		$result_array_temp = $result_array_full;

		while (list($key, $value) = each ($result_array_temp)) {
			$result_array[$key] = $value;

		}

		$keys = array_keys($result_array);
		$maxweight = $result_array[$keys[0]];
        $count = 0;

        foreach ($result_array as $row) {
            $weight = $row;
            if (Config::getInstance() -> DEFAULT_SEARCH_SORT != Config::SORT_INDEXDATE) {         // limit result output to min. relevance level or hits in full text
                if (Config::getInstance() -> DEFAULT_SEARCH_SORT != Config::SORT_HITCOUNTS) {     // no weight calculation for hits in full text
                    $weight = number_format($row / $maxweight * 100, 0);
                    if ($weight >= Config::getInstance() -> MIN_RELEVANCE) {
                        $count = ($count+1) ;
                    }
                } else {
                    if ($row >= Config::getInstance() -> MIN_RELEVANCE && $row > 0) {   //      present results only if relevance is met AND hits in full text are available
                        $count = ($count+1) ;
                    }
                }

            } else {
                $count = ($count+1) ;
            }
        }

        if ($count != 0) {
            $result_array = array_chunk($result_array, $count, true);   //      limit result output(weight > relevance level OR hits in fulltext > 0)
        }

        $result_array = $result_array[0];
		$results = count($result_array);
		$in = array();
		for ($i = 0; $i <min($results, ($start -1)* Config::getInstance() -> MAX_RESULTS+ Config::getInstance() -> MAX_RESULTS) ; $i++) {
			$in[] = $keys[$i];
		}

		if (count($in) == 0) {
			$res['results'] = $results;
			return $res;
		}

		$inlist = implode(",", $in);


		$query1 = "SELECT distinct id AS `linkId`, url, title, description, text AS `fulltxt`, '0' as `click_counter`
					FROM {$this -> _prefix}search
					WHERE id IN ($inlist)";
		$result = $this -> _conn -> getRows($query1);

		$i = 0;
		
		$highlight= $searchstr['hilight'];
		arsort($highlight);    //  reverse order, to highlight voluminous words first
		$md = new Microdata();
		foreach($result as $row) {
            $res[$i]['title'] = $row -> title;
            $res[$i]['url'] = $row -> url;
			$res[$i]['fulltxt'] = $row -> fulltxt;
			$res[$i]['highlight'] = $row -> fulltxt;
			$res[$i]['highlighttitle'] = $row -> title;
            $res[$i]['click_counter'] = $row -> click_counter;
            $res[$i]['weight'] = $result_array[$row -> linkId];
            $res[$i]['maxweight'] = $maxweight;
            $res[$i]['results'] = $count;
            if(Config::getInstance() -> DEFAULT_SEARCH_SORT == Config::SORT_MICRODATA) {
				$res[$i]['microdata'] = $md -> getMicrodata($row -> linkId);
            }
            $res[$i] = (object)$res[$i];
            $this -> _highlight($res[$i], $highlight);
            $i++;
		}

        return $res;
    }
    
    private function _highlight(&$obj, $highlight) {
		$places = array();
		$tmp = $obj -> fulltxt;
		
		foreach($highlight as $word) {
			$found_in = true;    //  pointer position start
			$pos_absolute = 0; // The position of the match in the original fulltext
			$wlen = strlen($word);
			$tmp = $obj -> fulltxt;
			while ($found_in !== false) {
				if (Config::getInstance() -> CASE_SENSITIVE) {
					$found_in = strpos($tmp, $word);      //  find position of first query hit
				}else {
					$found_in = stripos($tmp, $word);
				}
				if($found_in !== false) {
					$tmp_front = $found_in !== 0 ?  substr($tmp, $found_in - 1) : ''; //  one character before found match position
					$pos = $found_in + $wlen;
					$pos_absolute = $pos_absolute + $found_in;
					$tmp = substr($tmp, $pos);  // get rest of fulltxt
		
					if($this -> _strict) {
						//  check weather found match is realy strict
						$found_before = preg_match("/[(a-z)-_*.\/\:&@\w]/", substr($tmp_front, 0, 1));
						$found_behind = preg_match("/[(a-z)-_*.,\/\:&@\w]/", substr($tmp, 0, 1));
					}
		
					if (!$this -> _strict || ($found_before === 0 && $found_behind === 0)) {
						$places[] = $pos_absolute;   //  remind absolut position of match
						$found_in = false;
					}
				}
			}
		}
		
		sort($places);
		$obj -> highlight = substr($obj -> fulltxt, $places[0], Config::getInstance() -> RESULT_LENGTH);
		$obj -> highlight = substr($obj -> highlight,0, strrpos($obj -> highlight, ' '));
		foreach($highlight as $word) {
			$obj -> highlight = str_replace($word, "<span class='highlight'>$word</span>", $obj -> highlight);
			$obj -> highlighttitle = str_replace($word, "<span class='highlight'>$word</span>", $obj -> highlighttitle);
		}
    	
    }
}
