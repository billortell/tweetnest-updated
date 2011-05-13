<?php
	// PONGSOCKET TWEET ARCHIVE
	// Favorites page
	
	require "inc/preheader.php";
	
	$pageHeader = "Tweeters we track";
	
	require "inc/header.php";
echo <<< END
    <style type="text/css">
        div.user_profile {
            margin-bottom: 20px;
        }
        div.user_profile img {
            float: left;
            margin-right:20px;
        }
        div.user_profile h2 {
            margin-bottom: 5px;
            margin-top:0px;
        }
        div.user_profile h2 a {

            text-decoration: none;
        }        
        div.user_profile h2 a strong {
            color: #223344;
            text-decoration: none;
            float:left;

        }
        div.user_profile p {
            margin: 0px;
        }
        div.user_profile .total_tweets h1 {
            margin-bottom:0px;
            text-align:right;
        }
        div.user_profile .total_tweets p {
            font-size: 85%;
            font-style: italic;
            text-align:right;
        }
    </style>

END;

	echo user_list();
	require "inc/footer.php";