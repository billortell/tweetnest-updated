<?php
	// TWEET NEST
	// Load tweets
	
	error_reporting(E_ALL ^ E_NOTICE); ini_set("display_errors", true); // For easy debugging, this is not a production page
	@set_time_limit(0);
	
	require_once "mpreheader.php";
	$p = "";
	// The below is not important, so errors surpressed
	$f = @fopen("loadlog.txt", "a"); @fwrite($f, "Attempted load " . date("r") . "\n"); @fclose($f); 
	



	$pageTitle = "Loading tweets";
	require "mheader.php";


	// Identifying user
	if(!empty($_GET['userid']) && is_numeric($_GET['userid'])){
		$q = $db->query("SELECT * FROM `".DTP."tweetusers` WHERE `userid` = '" . $db->s($_GET['userid']) . "' LIMIT 1");
		if($db->numRows($q) > 0){
			$p = "user_id=" . preg_replace("/[^0-9]+/", "", $_GET['userid']);
		} else {
			dieout(l(bad("Please load the user first.")));
		}
	} else {
		if(!empty($_GET['screenname'])){
			$q = $db->query("SELECT * FROM `".DTP."tweetusers` WHERE `screenname` = '" . $db->s($_GET['screenname']) . "' LIMIT 1");
			if($db->numRows($q) > 0){
				$p = "screen_name=" . preg_replace("/[^0-9a-zA-Z_-]+/", "", $_GET['screenname']);
			} else {
				dieout(l(bad("Please load the user first.")));
			}
		}
	}
	

    if($p){
		importTweets($p);
	} else {
        $dttest = time() - LAST_UPDATED_GAP;
		$q = $db->query("SELECT * FROM `".DTP."tweetusers` WHERE `lastupdated` < $dttest AND `enabled` = '1'");
        $users_updated = 0;
		if($db->numRows($q) > 0){
			while($u = $db->fetch($q)){
				$uid = preg_replace("/[^0-9]+/", "", $u['userid']);
                echo l("<div>");
                echo l("<h1>".$u['realname']." (@".$u['screenname'].")</h1>");
				echo l("<h4>Trying to grab from user_id=" . $uid . "...</h4>\n");
				importTweets("user_id=" . $uid);
                echo l("<p>finished with user_id: <strong>".$u['realname']."</strong></p>");
                $qu = $db->query("UPDATE `".DTP."tweetusers` SET lastupdated = '".time()."' WHERE userid=$uid AND `enabled` = '1'");
                echo l("</div>");
                $users_updated++;

                if ( $users_updated < LAST_UPDATED_LIMIT )
                    sleep(2);
                else
                    break;
			}
		} else {
			echo l(bad("No users to import to!"));
		}
	}
	
	require "mfooter.php";