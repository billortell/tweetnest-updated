<?php
	// PONGSOCKET TWEET ARCHIVE
	// Search page
	
	require "inc/preheader.php";
	$isSearch = true;
	
	$path = rtrim($config['path'], "/");
	if(empty($_GET['tnid'])){ header("Location: " . $path . "/"); exit; }
	
	$month = false;

    /***
     * grab the single tweet status (by twitter "statuses" id)
     */
    $results = $search->query_by_id(
        $_GET['tnid']
    );

	$pageTitle   = "here's what I tweeted...";

	require "inc/header.php";
?>
<?php
	echo ( empty( $results) ) ?
        "<div class=\"notweets\">Not sure what you're after - but it's not found here!</div>"
        : tweetHTML($results[0]);
	require "inc/footer.php";