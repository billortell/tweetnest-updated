<?php
    require "inc/preheader.php";

    $p = "screen_name=" . preg_replace("/[^0-9a-zA-Z_-]+/", "", $_SESSION[tmhOauth]->screen_name);

    if ( !$isMaint ) {
        define("DEBUG_MAINTENANCE",FALSE);
    }
    importTweets($p);


	$pageHeader = "Loading Your Tweets from Twittter";
    $content_class = "";

	require "inc/header.php";
    ?>

    <div class="">
        <p>
            We've loaded your tweets - and so you're all set now!
        </p>
    
        <h2>
            <a href="<?php echo APP_PATH . "/user/" . $_GET[screenname]; ?>">Let's look at your final page!</a>
        </h2>

        <p>&nbsp;</p>
        <p>&nbsp;</p>
        <p>&nbsp;</p>
        <p>&nbsp;</p>

        <div style='clear:both;'></div>
    </div>

<?php

    require "inc/footer.php";