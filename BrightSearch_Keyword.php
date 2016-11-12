<?php
namespace BrightSearch;

class Keyword {

	function __construct($keyword = '', $count = 0) {
		$this -> keyword = $keyword;
		$this -> count = $count;
	}

	public $keyword;
	public $weight = 0;
	public $keywordId = 0;
	public $id = 0;
	public $intitle = false;
	public $inh2 = false;
	public $inh3 = false;
	public $inh4 = false;
	public $inh5 = false;
	public $inh6 = false;
	public $indescription = false;
	public $inkeywords = false;
	public $inparagraphs = array();
	public $count = 0;
}