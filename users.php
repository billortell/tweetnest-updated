<?php
	// PONGSOCKET TWEET ARCHIVE
	// Favorites page
	
	require "inc/preheader.php";
	
	$pageHeader = "Tweeters we track";
	
	require "inc/header.php";
echo <<< END
    <style type="text/css">
        div.user_profile img {
            float: left;
            margin-right:20px;
        }
        div.user_profile h2 {
            margin-bottom: 5px;
        }
        div.user_profile h2 a {

            text-decoration: none;
        }        
        div.user_profile h2 a strong {
            color: #223344;
            text-decoration: none;
        }
        div.user_profile p {
            margin: 0px;
        }
    </style>

END;

	echo user_list();
	require "inc/footer.php";