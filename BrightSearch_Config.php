<?php
namespace BrightSearch;

class Config {

	const SORT_RELEVANCE = 1;
	const SORT_MOSTPOPULAR = 5;
	const SORT_INDEXDATE = 6;
	const SORT_HITCOUNTS = 7;
	const SORT_MICRODATA = 8;


	public $IGNORE_NOINDEX = true;
	public $IGNORE_NOFOLLOW = true;

	public $CRAWLMICRODATA = true;

	public $AUTH_USER = 'ids';
	public $AUTH_PASS = '1qazse4rfv';
	/**
	 * @var int Relative weight of a word in the title of a webpage
	**/
	public $TITLE_WEIGHT = 20;

	/**
	 * @var int The relative weight of a word in a h2
	 */
	public $H2_WEIGHT = 18;

	/**
	 * @var int The relative weight of a word in a h3
	 */
	public $H3_WEIGHT = 17;

	/**
	 * @var int The relative weight of a word in a h4
	 */
	public $H4_WEIGHT = 16;

	/**
	 * @var int The relative weight of a word in a h5
	 */
	public $H5_WEIGHT = 15;

	/**
	 * @var int The relative weight of a word in a h6
	 */
	public $H6_WEIGHT = 14;

	/**
	 * @var int The relative weights of a word in the nth p tag (First p tag get a score of 5, 2nd a score of 4, etc)
	 */
	public $PWEIGHTS = array(5,4,3,2,1);

	/**
	 * @var int Relative weight of a word in the domain name
	**/
	public $DOMAIN_WEIGHT = 60;

	/**
	 * @var int Relative weight of a word in the path name
	**/
	public $PATH_WEIGHT = 10;

	/**
	 * @var int Relative weight of a word in meta_keywords
	**/
	public $META_WEIGHT = 5;

	/**
	 * @var int Minimal number of characters
	 */
	public $MIN_WORD_LENGTH = 3;

	/**
	 * @var int Maximum number of occurence
	 */
	public $WORD_UPPER_BOUND = 100;

	/**
	 * @var boolean Whether or not numbers should be indexed
	 */
	public $INDEX_NUMBERS = true;

	/**
	 * @var boolean Search is case sensative
	 */
	public $CASE_SENSITIVE = false;

	/**
	 * @var boolean Queries with numbers become wildsearch
	 */
	public $WILD_NUM = false;

	/**
	 * @var boolean Search without quotes
	 */
	public $NO_QUOTES = false;

	/**
	 * @var string Default search type
	 */
	public $DEFAULT_SEARCH_TYPE = 'and';

	/**
	 * @var string Default search sort
	 */
	public $DEFAULT_SEARCH_SORT = Config::SORT_MICRODATA;

	/**
	 * @var string Default search sort for microdata sort
	 */
	public $MICRODATA_SORT_FIELD = 'startDate';

	/**
	 * @var boolean Is there greek text?
	 */
	public $IS_GREEK = false;

	/**
	 * @var boolean Is there greek text?
	 */
	public $IS_CYRILLIC = false;

	/**
	 * @var string Mysql Table Prefix
	 */
	public $TABLE_PREFIX = 'bs_';

	/**
	 * @var int Max. quantity of results for result listing
	 */
	public $MAX_RESULTS = 999;

	/**
	 * @var int Max. quantity of results for per page
	 */
	public $RESULTS_PER_PAGE = 10;

	/**
	 * @var boolean use did you mean
	 */
	public $DID_YOU_MEAN_ENABLED = true;

	/**
	 * @var int	Min. relevance level (%) to be shown at result pages
	 */
	public $MIN_RELEVANCE = 0;
	
	/**
	 * @var int Max. length of the result text
	 */
	public $RESULT_LENGTH = 100;
	
	/**
	 * @staticvar Connection The instance of this class
	 */
	static private $instance;
	
	/**
	 * Gets a single instance of the connection class
	 * @static
	 * @return StdClass An instance of the connction class
	 */
	public static function getInstance(){
		if(!isset(self::$instance)){
			$object= __CLASS__;
			self::$instance= new $object;
		}
		return self::$instance;
	}

	public static function setup() {
		$prefix = Config::getInstance() -> TABLE_PREFIX;
		$sqla = array();
		$sqla[] = "CREATE TABLE IF NOT EXISTS `{$prefix}settings` (
					  `id` int(11) NOT NULL AUTO_INCREMENT,
					`IGNORE_NOINDEX` tinyint(1) NOT NULL DEFAULT '0', 
					`IGNORE_NOFOLLOW` tinyint(1) NOT NULL DEFAULT '0', 
					`CRAWLMICRODATA` tinyint(1) NOT NULL DEFAULT '1', 
					`AUTH_USER` varchar(255) NULL, 
					`AUTH_PASS` varchar(255) NULL, 
					`TITLE_WEIGHT` int(11) NOT NULL DEFAULT '20', 
					`H2_WEIGHT` int(11) NOT NULL DEFAULT '18', 
					`H3_WEIGHT` int(11) NOT NULL DEFAULT '17', 
					`H4_WEIGHT` int(11) NOT NULL DEFAULT '16', 
					`H5_WEIGHT` int(11) NOT NULL DEFAULT '15', 
					`H6_WEIGHT` int(11) NOT NULL DEFAULT '14', 
					`PWEIGHTS` VARCHAR(20) NOT NULL DEFAULT '5,4,3,2,1', 
					`DOMAIN_WEIGHT` int(11) NOT NULL DEFAULT '60', 
					`PATH_WEIGHT` int(11) NOT NULL DEFAULT '10', 
					`META_WEIGHT` int(11) NOT NULL DEFAULT '5', 
					`MIN_WORD_LENGTH` int(11) NOT NULL DEFAULT '3', 
					`WORD_UPPER_BOUND` int(11) NOT NULL DEFAULT '100',
					`INDEX_NUMBERS` tinyint(1) NOT NULL DEFAULT '1', 
					`CASE_SENSITIVE` tinyint(1) NOT NULL DEFAULT '0', 
					`WILD_NUM` tinyint(1) NOT NULL DEFAULT '0', 
					`NO_QUOTES` tinyint(1) NOT NULL DEFAULT '0',
					`DEFAULT_SEARCH_TYPE` VARCHAR(20) NOT NULL DEFAULT 'and', 
					`DEFAULT_SEARCH_SORT` INT(11) NOT NULL DEFAULT '1', 
					`MICRODATA_SORT_FIELD` VARCHAR(20) NOT NULL DEFAULT 'startDate',
					`IS_GREEK` tinyint(1) NOT NULL DEFAULT '0', 
					`IS_CYRILLIC` tinyint(1) NOT NULL DEFAULT '0',
					`MAX_RESULTS` int(11) NOT NULL DEFAULT '999', 
					`RESULTS_PER_PAGE` int(11) NOT NULL DEFAULT '10', 
					`DID_YOU_MEAN_ENABLED` tinyint(1) NOT NULL DEFAULT '1', 
					`MIN_RELEVANCE` int(11) NOT NULL DEFAULT '0', 
					`RESULT_LENGTH` int(11) NOT NULL DEFAULT '100',
				  	PRIMARY KEY (`id`)) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
		
		$sqla[] = "CREATE TABLE IF NOT EXISTS `{$prefix}keywords` (
					  `keywordId` int(11) NOT NULL AUTO_INCREMENT,
					  `keyword` varchar(255) DEFAULT NULL,
					  PRIMARY KEY (`keywordId`),
					  UNIQUE KEY `keyword` (`keyword`)
					) ENGINE=MyISAM  DEFAULT CHARSET=utf8;";

		$sqla[] = "CREATE TABLE IF NOT EXISTS `{$prefix}microdata` (
				  `id` int(11) NOT NULL AUTO_INCREMENT,
				  `linkId` int(11) DEFAULT NULL,
				  `parent` int(11) NOT NULL,
				  `name` varchar(100) NOT NULL,
				  `value` varchar(1000) NOT NULL,
				  `indexdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
				  PRIMARY KEY (`id`),
				  KEY `url` (`linkId`,`name`),
				  KEY `name` (`name`),
				  KEY `value` (`value`(255))
				) ENGINE=MyISAM  DEFAULT CHARSET=utf8;";

		$sqla[] = "CREATE TABLE IF NOT EXISTS `{$prefix}search` (
					  `id` int(11) NOT NULL AUTO_INCREMENT,
					  `url` varchar(1024) NOT NULL,
					  `title` varchar(255) NOT NULL,
					  `keywords` varchar(255) NOT NULL,
					  `encoding` varchar(20) NOT NULL,
					  `description` text NOT NULL,
					  `text` longtext NOT NULL,
					  `creationdate` timestamp NULL DEFAULT NULL,
					  `modificationdate` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
					  `md5sum` varchar(255) NOT NULL,
					  `sha1url` varchar(255) NOT NULL,
					  `deleted` tinyint(1) DEFAULT '0',
					  PRIMARY KEY (`id`),
					  KEY `url` (`url`(333)),
					  KEY `md5sum` (`md5sum`),
					  KEY `deleted` (`deleted`),
					  UNIQUE KEY `sha1url` (`sha1url`)
					) ENGINE=MyISAM  DEFAULT CHARSET=utf8;";

		$keywordX =array ("link_keyword0","link_keyword1","link_keyword2","link_keyword3","link_keyword4","link_keyword5", "link_keyword6","link_keyword7","link_keyword8","link_keyword9","link_keyworda","link_keywordb", "link_keywordc","link_keywordd","link_keyworde","link_keywordf");
		for($i = 0; $i < 16; $i++) {
			$sqla[] = "CREATE TABLE IF NOT EXISTS `{$prefix}{$keywordX[$i]}` (
						`linkId` int(11) NOT NULL,
						`keywordId` int(11) NOT NULL,
						`weight` int(3) DEFAULT NULL,
						`hits` int(3) DEFAULT NULL,
						`indexdate` datetime DEFAULT NULL,
						`deleted` tinyint(1) DEFAULT '0',
	  					UNIQUE KEY `linkkeywordId` (`linkId`,`keywordId`),
						KEY `linkId` (`linkId`),
						KEY `keyid` (`keywordId`),
						KEY `deleted` (`deleted`)
						) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
		}

		foreach($sqla as $sql) {
			\Connection::getInstance() -> insertRow($sql);
		}
		
		$sql = "SELECT count(id) FROM {$prefix}settings";
		if(\Connection::getInstance() -> getField($sql) == 0) {
			\Connection::getInstance() -> insertRow("INSERT INTO `{$prefix}settings` (id) VALUES ('1');");
		}
		
		$settings = \Connection::getInstance() -> getRow("SELECT * FROM {$prefix}settings");
		foreach($settings as $key => $value) {
			switch($key) {
				case 'PWEIGHTS':
					Config::getInstance() -> {'$'. $key} = explode(',',$value);
					break;
					
				case 'IGNORE_NOINDEX':
				case 'IGNORE_NOFOLLOW':
				case 'CRAWLMICRODATA':
				case 'INDEX_NUMBERS':
				case 'CASE_SENSITIVE':
				case 'WILD_NUM':
				case 'NO_QUOTES':
				case 'IS_GREEK':
				case 'IS_CYRILLIC':
				case 'DID_YOU_MEAN_ENABLED':
					Config::getInstance() -> {'$'. $key} = $value == 1;
					break;
				default:
					Config::getInstance() -> {'$'. $key} = $value;
					
			}
		}
	}
}