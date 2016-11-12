<?php
require(__DIR__ . '/library/Bright/Bright.php');
require(__DIR__ . '/BrightSearch.php');

$u = new BrightUtils();
$u->inBrowser(true);

$i = new \BrightSearch\Index();
if (!isset($argv) || count($argv) == 1) {
    $argv = array('Search.php', BASEURL, -1, 'robots.txt');
}
error_reporting(E_ALL | E_STRICT);
\BrightSearch\Config::getInstance()->IGNORE_NOINDEX = false;
\BrightSearch\Config::getInstance()->IGNORE_NOFOLLOW = false;
\BrightSearch\Config::getInstance()->DEFAULT_SEARCH_SORT = \BrightSearch\Config::SORT_RELEVANCE;
$i->indexSite($argv[1], $argv[2], $argv[3]);