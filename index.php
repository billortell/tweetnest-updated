<?php


// PONGSOCKET TWEET ARCHIVE
// Front page


  //  $u = ( !empty($_GET[user]) ? $_GET[user] : $u );

	require "inc/preheader.php";

	$qsql = "SELECT `".DTP."tweets`.*, `".DTP."tweetusers`.`screenname`, `".DTP."tweetusers`.`realname`, `".DTP."tweetusers`.`profileimage` FROM `".DTP."tweets`
		LEFT JOIN `".DTP."tweetusers` ON `".DTP."tweets`.`userid` = `".DTP."tweetusers`.`userid`
		".$qwhr['where']."
		ORDER BY `".DTP."tweets`.`time` DESC LIMIT 25";


	$q = $db->query($qsql);
	$pageHeader = "Recent tweets";
	$home       = true;

	require "inc/header.php";
	echo tweetsHTML($q);
	require "inc/footer.php";