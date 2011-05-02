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
    $content_class = "status_bg";

	require "inc/header.php";
?>
<div class=status>

    <?php
	echo ( empty( $results) ) ?
        "<div class=\"notweets\">Not sure what you're after - but it's not found here!</div>"
        : tweetHTML($results[0]);
	?>

    <p>&nbsp;</p>
    <p>&nbsp;</p>
    <p>&nbsp;</p>
    <p>&nbsp;</p>

    <div style='clear:both;'></div>
</div>


<?php

require "inc/footer.php";