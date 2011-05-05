<?php

    // PONGSOCKET TWEET ARCHIVE
    // Front page

	require "inc/preheader.php";

	$qsql = "SELECT `".DTP."tweets`.*, `".DTP."tweetusers`.`screenname`, `".DTP."tweetusers`.`realname`, `".DTP."tweetusers`.`profileimage` FROM `".DTP."tweets`
		LEFT JOIN `".DTP."tweetusers` ON `".DTP."tweets`.`userid` = `".DTP."tweetusers`.`userid`
		".$qwhr['where']."
		ORDER BY `".DTP."tweets`.`time` DESC LIMIT ".DEFAULT_SHOW_LIMIT;

	$q = $db->query($qsql);

	$pageHeader = $_SESSION["user"] . "'s recent tweets";
	$home       = true;

	require "inc/header.php";
	echo tweetsHTML($q);
	require "inc/footer.php";