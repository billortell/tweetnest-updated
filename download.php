<?php
    require "inc/preheader.php";
    

    /****
     * retrieve my personal tweets!
     * -----------------------------------
     */
    if ( $_GET["action"] == "export" ){

        $qsql = "SELECT `".DTP."tweets`.*, `".DTP."tweetusers`.`screenname`, `".DTP."tweetusers`.`realname`, `".DTP."tweetusers`.`profileimage` FROM `".DTP."tweets`
            LEFT JOIN `".DTP."tweetusers` ON `".DTP."tweets`.`userid` = `".DTP."tweetusers`.`userid`
            ".$qwhr['where']."
            ORDER BY `".DTP."tweets`.`time`";

        $q = $db->query($qsql);

        $exportArr      = fetch_tweets($q);

        $delim          = EXPORT_DELIMINATOR;
        $filename_base  = EXPORT_FILENAME_SEEN_AS;
        $limit          = EXPORT_TWEET_LIMIT; // 0 - no limit, else, limits to value

        require_once("inc/export.php");
        exit();

    } else {

        $qsql = "SELECT `".DTP."tweets`.*, `".DTP."tweetusers`.`screenname`, `".DTP."tweetusers`.`realname`, `".DTP."tweetusers`.`profileimage` FROM `".DTP."tweets`
            LEFT JOIN `".DTP."tweetusers` ON `".DTP."tweets`.`userid` = `".DTP."tweetusers`.`userid`
            ".$qwhr['where']."
            ORDER BY `".DTP."tweets`.`time` DESC LIMIT ".DEFAULT_SHOW_LIMIT;

        $q = $db->query($qsql);


    }



	$pageHeader = "Download tweets";
    $content_class = "download_bg";

	require "inc/header.php";
    ?>

<div class=download>
    <p>
        Make sure you check back frequently to get your downloaded tweets!  And, help
        us continue to offer this service for free by referring others.
    </p>

    <p>
        <em>Thank you!</em>
    </p>

    <h2>
        <a href="?action=export">Download your tweets here!</a>
    </h2>

    <p>&nbsp;</p>
    <p>&nbsp;</p>
    <p>&nbsp;</p>
    <p>&nbsp;</p>

    <div style='clear:both;'></div>
</div>

<?php

require "inc/footer.php";