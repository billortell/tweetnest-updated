<?php
    require "inc/preheader.php";

	$pageHeader = "Deleting Tweets from this site";
    $content_class = "";

    // @todo need more security on this one...
    // if ( $_SESSION[tmhOauth]->screen_name == $_GET["user"] ) {
    if ( !empty($_GET["user"]) ) {
        delete_user($_GET[user]);
    }


	require "inc/header.php";
    ?>

    <div class="">
        <p>
            We've completely deleted <?php echo $_GET['user'];?>'s tweets from our site!
        </p>
    
        <h2>
            <a href="<?php echo APP_PATH; ?>/">Go back to the main page...</a>
        </h2>

        <p>&nbsp;</p>
        <p>&nbsp;</p>
        <p>&nbsp;</p>
        <p>&nbsp;</p>

        <div style='clear:both;'></div>
    </div>

<?php

    require "inc/footer.php";