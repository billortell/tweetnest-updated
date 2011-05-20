<?php
    require "inc/preheader.php";

    $p = "screen_name=" . preg_replace("/[^0-9a-zA-Z_-]+/", "", $_SESSION[tmhOauth]->screen_name);

    if ( !$isMaint ) {
        define("DEBUG_MAINTENANCE",FALSE);
    }
    importTweets($p);


	$pageHeader = "We loaded your Tweets!";
    $content_class = "";

	require "inc/header.php";
    ?>

    <div class="">
        <p>
            We've loaded your tweets - and so you're all set now!  You can always download your
            latest tweets from this site.  We'll keep it up to date as best as possible.
        </p>
        <p>
            You can  <a href="/download">download your tweets</a> now.
        </p>

        <p>
            In addition, you have a special page for which to refer people to - that doesn't
            involve them having to go to Twitter&trade; at all for...
        </p>

        <h2>
            <a href="<?php echo APP_PATH . "/user/" . $_GET[screenname]; ?>">Let's look at your own personalized offline tweets page!</a>
        </h2>

        <p>&nbsp;</p>
        <p>&nbsp;</p>
        <p>&nbsp;</p>
        <p>&nbsp;</p>

        <div style='clear:both;'></div>
    </div>

<?php

    require "inc/footer.php";