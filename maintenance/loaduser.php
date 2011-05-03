<?php
	// TWEET NEST
	// Load user

	require "mpreheader.php";
	$pageTitle = "Loading user info";
	require "mheader.php";


    define("DEBUG_MAINTENANCE",TRUE);
    load_user(DEBUG_MAINTENANCE);

	require "mfooter.php";