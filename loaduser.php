<?php
    require "inc/preheader.php";
    
    if ( !$isMaint ) {
        define("DEBUG_MAINTENANCE",FALSE);
    }
    load_user(DEBUG_MAINTENANCE);

	$pageHeader     = "We've loaded your Twitter User Profile!";
    $content_class  = "";

	require "inc/header.php";
    ?>

    <div class="">
        <p>
            Proceed now to load your tweets into the system, or check back in a few to
            have your tweets automatically loaded into our system at regular intervals.
        </p>

        <h2>
            <a href="<?php echo APP_PATH."/loadtweets/". $_GET[screenname];?>">Can't wait!? Load them now!</a>
        </h2>

        <p>&nbsp;</p>
        <p>&nbsp;</p>
        <p>&nbsp;</p>
        <p>&nbsp;</p>

        <div style='clear:both;'></div>
    </div>

    <?php

    require "inc/footer.php";