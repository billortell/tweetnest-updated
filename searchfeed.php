<?php
	// PONGSOCKET TWEET ARCHIVE
	// Search page
	
	require "inc/preheader.php";
	require "inc/feedcreator.class.php";
	
	$path = rtrim($config['path'], "/");
	
	$month = false;
	if(!empty($_GET['m']) && !empty($_GET['y'])){
		$m = ltrim($_GET['m'], "0");
		if(is_numeric($m) && $m >= 1 && $m <= 12 && is_numeric($_GET['y']) && $_GET['y'] >= 2000){
			$month = true;
			$selectedDate = array("y" => $_GET['y'], "m" => $m, "d" => 0);
		}
	}
	
	$sort = "time"; // Sorting by time for the feed
	
	$tooShort = (strlen($_GET['q']) < $search->minWordLength || $search->minWordLength > 1 && strlen(trim($_GET['q'], "*")) <= 1);

	if(!$tooShort){
		$mq = $search->monthsQuery($_GET['q']);
		while($d = $db->fetch($mq)){ $highlightedMonths[$d['y'] . "-" . $d['m']] = $d['c']; }
		
		$results = $search->query(
			$_GET['q'],
			$sort,
			($month 
				? " AND YEAR(FROM_UNIXTIME(`time`" . DB_OFFSET . ")) = '" . s($_GET['y']) . "' AND MONTH(FROM_UNIXTIME(`time`" . DB_OFFSET . ")) = '" . s($m) . "'"
				: ""
			)
		);
	}

	$pageTitle   = "Searching for \"" . $_GET['q'] . "\"" . ($month ? " in " . date("F Y", mktime(1,0,0,$m,1,$_GET['y'])) : "");
	$SearchFeed = new UniversalFeedCreator();
	$SearchFeed->title = $pageTitle;
	$SearchFeed->link = sprintf("http://%s%s",$_SERVER["HTTP_HOST"],$path);
	$SearchFeed->description = $pageTitle;
	
	foreach($results as $result){
		$item = new FeedItem();
		$item->title = $result['text'];
		$item->link = sprintf("http://twitter.com/%s/status/%s",$result['screenname'],$result['tweetid']);
		$item->date = $result['time'];
		$item->description = $result['text'];
		$SearchFeed->addItem($item);
	}

	$SearchFeed->outputFeed("ATOM1.0");