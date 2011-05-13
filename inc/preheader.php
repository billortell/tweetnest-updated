<?php
	// TWEET NEST
	// Preheader

    //	$userid = 'rapextras';
    /***
     * this is whom we're goin' to show...
     * - this should be driven out by a session variable as we
     * put in acl priv's
     *
     */
    session_start();

	$startTime = microtime(true);
	
	error_reporting(E_ALL ^ E_NOTICE);
	mb_language("uni");
	mb_internal_encoding("UTF-8");
	
	define("TWEET_NEST", "0.8.2"); // Version number


    /***
     * discover if we're in maintenance mode or not...
     */
    global $isMaint;
    $isMaint = strpos($_SERVER["SCRIPT_URI"],$config["path"]."/maintenance/");

    /***
     * current file we're hitting.../calling...
     */
    $current_url_file = basename($_SERVER["SCRIPT_NAME"]);

    global $isStatus;
    $isStatus = ( $current_url_file == "status.php" );






    /***
     * includes all the settings for the site
     * -- including the tweet_username
     * (which in our case can be dynamically reset/set)
     */
	require "config.php";


     $u = $config['twitter_screenname'];
     $u = $_SESSION['user'];

    
    /***
     * store user info so we don't have to keep user= in URL
     * or in FORM input/hidden fields when searching...
     *
     * ==> this would need to become a user/login thingy..
     */
    if ( !empty($_GET[user]) ) {

    //    echo "setting user... to $_GET[user]";
        /** user to view...  */
        $_SESSION["user"] = $_GET["user"];
        $_SESSION["tempuser"] = $_SESSION["user"];

        $u = $_SESSION["tempuser"];

      //  } elseif ( empty($_SESSION["user"] ) ) {
    } elseif ( !empty($_SESSION[tmhOauth]->screen_name) ) {

    //    echo "setting user... to auth'd... ".$_SESSION[tmhOauth]->screen_name;
         /** if no request is made, default to the owners' stuff! */
        $_SESSION["user"] = $_SESSION[tmhOauth]->screen_name;
        $_SESSION["tempuser"] = $_SESSION["user"];


    } else {

        /** if no request is made, default to the owners' stuff! */
        if ( $_SESSION["tempuser"] )
            $_SESSION["user"] = $_SESSION["tempuser"];
        else
            $_SESSION["user"] = $config['twitter_screenname'];
     //   unset($_SESSION['tempuser']);
     //   echo "setting user... to default: ".$_SESSION["user"];


    }

    /** @var $u , $user -  */
    $u = $use_user = $_SESSION["user"] ;








	if(empty($config['twitter_screenname'])){ header("Location: ./setup.php"); exit; }
	date_default_timezone_set($config['timezone']);
	define("DTP", $config['db']['table_prefix']);
	
	// Get the full path
	$fPath = explode(DIRECTORY_SEPARATOR, rtrim(__FILE__, DIRECTORY_SEPARATOR));
	array_pop($fPath); array_pop($fPath); // Remove inc/preheader.php
	$fPath = implode($fPath, "/");
	define("FULL_PATH", $fPath);
	
	// SmartyPants
	include "smartypants.php";
	
	// DB
	require "class.db.php";
	$db = new DB("mysql", $config['db']);
	if(!$delayedDB){ unset($config['db']['password']); }
	
	// Twitter API class
	require "class.twitterapi.php";
	$twitterApi = new TwitterApi();
    $twitterApi->get_rate_limit_status();

	// Search
	require "class.search.php";
	$search = new TweetNestSearch();
	
	// Outputting various generic parts
	require "emoji.php";
	require "html.php";
	
	// Extensions
	require "extensions.php";
	
	$selectedDate      = array("y" => 0, "m" => 0, "d" => 0);
	$highlightedMonths = array();
	$filterMode        = "search";
	$home              = false;
	$jQueryVersion     = "1.5.1";
	$isSearch          = false;
	
	// Getting database time offset
	$dbtQ = $db->query("SELECT TIME_FORMAT(NOW() - UTC_TIMESTAMP(), '%H%i') AS `diff`");
	$dbtR = $db->fetch($dbtQ);
	
	$dbOffset          = date("Z") - ($dbtR['diff'] * 36); if(!is_numeric($dbOffset)){ $dbOffset = 0; }
	$dbOffset          = $dbOffset >= 0 ? "+" . $dbOffset : $dbOffset; // Explicit positivity/negativity
	
	global $db, $twitterApi, $search, $selectedDate, $highlightedMonths, $filterMode, $home, $dbOffset;
	define("DB_OFFSET", $dbOffset);
	
	// String manipulation functions
	function s($str, $flags = ENT_COMPAT){ return htmlspecialchars($str, $flags); } // Shorthand
	function x($str, $attr = NULL){ return p(s($str, ENT_NOQUOTES), $attr); } // Shorthand
	function p($str, $attr = NULL, $force = false){ global $config; return ($config['smartypants'] || $force) ? SmartyPants($str, $attr) : $str; }
	
	// Numeric manipulation functions
	function pad($int){ if($int < 10){ return "0" . $int; } return $int; }
	
	// Consts
	define("PST_GZIP", (substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], "gzip") > 0));
	define("PST_GZIP_S", (PST_GZIP ? ".gz" : ""));
	
	// Check for cURL
	if(!extension_loaded("curl")){
	    $prefix = (PHP_SHLIB_SUFFIX === "dll") ? "php_" : "";
	    if(!function_exists("dl") || !@dl($prefix . "curl." . PHP_SHLIB_SUFFIX)){
	        trigger_error("Unable to load the PHP cURL extension.", E_USER_ERROR);
	        exit;
	    }
	}
	
	// Author info
	$authorQ     = $db->query("SELECT * FROM `".DTP."tweetusers` WHERE `screenname` = '" . $db->s($config['twitter_screenname']) . "' LIMIT 1");
	$author      = $db->fetch($authorQ);
	$authorextra = unserialize($author['extra']);
	global $author, $authorextra;


    function display_user_profile($profile){
        if ( empty($profile) )
            return;

        ob_start();
        ?>
            <div class='user_profile'>
                <img src="<?php echo s($profile['profileimage']); ?>" style='margin-top: 5px; margin-bottom: 10px;' width="48" height="48" alt="" />
                <h2>
                        <strong><?php echo s($profile['realname']); ?></strong>
                </h2>
                <p>
                    <a href="<?php echo APP_PATH."/user/".s($profile['screenname']); ?>">
                        <strong>@<?php echo s($profile['screenname']); ?></strong>
                    </a>
                </p>
            </div>
    <?php
        return ob_get_clean();
    }

    /***
     * @return array list of users..... and their first tweet!?
     */
    function user_list($retArr = FALSE){
        global $db;
        $q = $db->query("SELECT * FROM `".DTP."tweetusers`");
        $user_list = array();
        while($user = $db->fetch($q)){
            $user_list[] = $user;
        }

        if ( $retArr )
            return $user_list;


        $user_list_str = "";
        foreach ( $user_list as $user ) {
            $user_list_str .= display_user_profile($user);
        }
        return $user_list_str;
    }

	function getURL($url, $auth = NULL){
        global $twitterApi;
		// HTTP grabbin' cURL options, also exsecror
		$httpOptions = array(
			CURLOPT_FORBID_REUSE   => true,
			CURLOPT_POST           => false,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_TIMEOUT        => 30,
			CURLOPT_USERAGENT      => "Mozilla/5.0 (Compatible; libCURL)",
			CURLOPT_VERBOSE        => false,
			CURLOPT_SSL_VERIFYPEER => false // Insecurity?
		);
		$conn = curl_init($url);
		$o    = $httpOptions;
        if(is_array($auth) && count($auth) == 2){
            $o[CURLOPT_USERPWD] = $auth[0] . ":" . $auth[1];
        }


        // return headers on call (if secure post - meaning it's in the header)
        if( strpos($url,"https") !== FALSE ){
            $o[CURLOPT_HEADER] = TRUE;
        }

		curl_setopt_array($conn, $o);
		$file = curl_exec($conn);

        /*** added to parse header - body separation when necc. see above ***/
        if ( $o[CURLOPT_HEADER] ) {
            list($headers, $file) = explode("\r\n\r\n", $file, 2);
        }

        // X-Ratelimit-Limit
        // necessary to store these, if you're not a developer -
        // using oauth/developer key... in call (auth)
        //$xHeaders = $twitterApi->set_xHeaders($file);
        $xHeaders = $twitterApi->set_xHeaders($headers);

        $info = curl_getinfo($conn);

        if( strpos($url,"rate_limit_status") === FALSE ){
            echo ( !DEBUG_MAINTENANCE ) ? "" : "<h2>".$twitterApi->get_remaining_hits()." Remaining Calls</h2>";
        }
        
		if(!curl_errno($conn)){
			curl_close($conn);
			return $file;
		} else {
			$a = array(false, curl_errno($conn), curl_error($conn));
			curl_close($conn);
			return $a;
		}
	}
	
	function findURLs($str){
		$urls = array();
		preg_match_all("/\b(((https*:\/\/)|www\.).+?)(([!?,.\"\)]+)?(\s|$))/", $str, $m);
		foreach($m[1] as $url){
			$u = ($url[0] == "w") ? "http://" . $url : $url;
			$urls[$u] = parse_url($u);
		}
		return $urls;
	}
	
	function domain($host){
		if(empty($host) || !is_string($host)){ return false; }
		if(preg_match("/^[0-9\.]+$/", $host)){ return $host; } // IP
		if(substr_count($host, ".") <= 1){
			return $host;
		} else {
			$h = explode(".", $host, 2);
			return $h[1];
		}
	}

	function l($html){ // Display log line in correct way, depending on HTTP or not
		global $web;
        if ( DEBUG_MAINTENANCE === FALSE )
            return;
		return $web ? str_replace("</li>\n", "</li>", $html) : strip_tags(str_replace("<li>", "<li> - ", $html));
	}

	function ls($html){
		global $web;
		return $web ? s($html) : $html; // Only encode HTML special chars if we're actually in a HTML doc
	}

	function good($html){
		return "<strong class=\"good\">" . $html . "</strong>";
	}

	function bad($html){
		return "<strong class=\"bad\">" . $html . "</strong>";
	}

	function dieout($html){
		echo $html;
		require "mfooter.php";
		die();
	}

	// STUPE STUPE STUPEFY
	function stupefyRaw($str, $force = false){
		global $config;
		return ($config['smartypants'] || $force) ? str_replace(
			array("–", "—", "‘", "’", "“", "”", "…"),
			array("---", "--", "'", "'", "\"", "\"", "..."),
			$str) : $str;
	}


	// Define import routines
	function totalTweets($p){
		global $twitterApi;
		$p = trim($p);
		if(!$twitterApi->validateUserParam($p)){ return false; }

        // call the api once more!
        //---------------------------------------
		$data = $twitterApi->query("1/users/show.json?" . $p);

        // @TODO we should update some sorta prefs on the tweetnest -
        // -- so it mimics their own twitter account (ie. background, etc...)

		if(is_array($data) && $data[0] === false){ dieout(l(bad("Error: " . $data[1] . "/" . $data[2]))); }
		return $data->statuses_count;
	}

	function importTweets($p){
		global $twitterApi, $db, $config, $access, $search;
		$p = trim($p);
		if(!$twitterApi->validateUserParam($p)){ return false; }
		$maxCount = 200;
		$tweets   = array();
		$sinceID  = 0;
		$maxID    = 0;


		echo l("Importing:\n");

		// Do we already have tweets?
		$pd = $twitterApi->getUserParam($p);
		if($pd['name'] == "screen_name"){
			$uid        = $twitterApi->getUserId($pd['value']);
			$screenname = $pd['value'];
		} else {
			$uid        = $pd['value'];
			$screenname = $twitterApi->getScreenName($pd['value']);
		}

        /* get twitter user row */
        $dtfavtest = time() - SYNC_FAVORITES;
        $tuQ = $db->query("SELECT * FROM `".DTP."tweetusers` WHERE `userid` = '" . $db->s($uid) . "' AND lastupdated < ".$dtfavtest."");
        $favsync = ( $db->numRows($tuQ) > 0 AND SYNC_FAVORITES ) ;



        /*** coordinating last fetched tweet ***/
		$tiQ = $db->query("SELECT `tweetid` FROM `".DTP."tweets` WHERE `userid` = '" . $db->s($uid) . "' ORDER BY `id` DESC LIMIT 1");
		if($db->numRows($tiQ) > 0){
			$ti      = $db->fetch($tiQ);
			$sinceID = $ti['tweetid'];
		}

		echo l("User ID: " . $uid . "\n");

		// Find total number of tweets
		$total = totalTweets($p);
		if($total > 3200){ $total = 3200; } // Due to current Twitter limitation
		$pages = ceil($total / $maxCount);

		echo l("Total tweets: <strong>" . $total . "</strong>, Pages: <strong>" . $pages . "</strong>\n");
		if($sinceID){
			echo l("Newest tweet I've got: <strong>" . $sinceID . "</strong>\n");
		}


        /***
         * @todo maybe track the regularity of posts/tweets..
         * and minimize calls to maximize pull...
         * (IOW - wait 'til we typ. have 150+ tweets before pulling, on active accounts
         * thereby minimizing the calls to receive 20 tweets each... ;)
         */

        if ( GET_TWEETS ) {

            // Retrieve tweets
            for($i = 0; $i < $pages; $i++){
                $path = "1/statuses/user_timeline.json?" . $p . "&include_rts=true&count=" . $maxCount . ($sinceID ? "&since_id=" . $sinceID : "") . ($maxID ? "&max_id=" . $maxID : "");
                echo l("Retrieving page <strong>#" . ($i+1) . "</strong>: <span class=\"address\">" . ls($path) . "</span>\n");

                // call the api once more!
                //---------------------------------------
                $data = $twitterApi->query($path);

                if(is_array($data) && $data[0] === false){ dieout(l(bad("Error: " . $data[1] . "/" . $data[2]))); }
                echo l("<strong>" . ($data ? count($data) : 0) . "</strong> new tweets on this page\n");
                if(!$data){ break; } // No more tweets
                echo l("<ul>");
                foreach($data as $tweet){
                    echo l("<li>" . $tweet->id_str . " " . $tweet->created_at . "</li>\n");
                    $tweets[] = $twitterApi->transformTweet($tweet);
                    $maxID    = sprintf("%.0F", (float)((float)$tweet->id - 1));
                }
                echo l("</ul>");
                if(count($data) < ($maxCount - 50)){
                    echo l("We've reached last page\n");
                    break;
                }
            }

            if(count($tweets) > 0){
                // Ascending sort, oldest first
                $tweets = array_reverse($tweets);
                echo l("<strong>All tweets collected. Reconnecting to DB...</strong>\n");
                $db->reconnect(); // Sometimes, DB connection times out during tweet loading. This is our counter-action
                echo l("Inserting into DB...\n");
                $error = false;
                foreach($tweets as $tweet){
                    $q = $db->query($twitterApi->insertQuery($tweet));
                    if(!$q){
                        dieout(l(bad("DATABASE ERROR: " . $db->error())));
                    }
                    $text = $tweet['text'];
                    $te   = $tweet['extra'];
                    if(is_string($te)){ $te = @unserialize($tweet['extra']); }
                    if(is_array($te)){
                        // Because retweets might get cut off otherwise
                        $text = (array_key_exists("rt", $te) && !empty($te['rt']) && !empty($te['rt']['screenname']) && !empty($te['rt']['text']))
                            ? "RT @" . $te['rt']['screenname'] . ": " . $te['rt']['text']
                            : $tweet['text'];
                    }
                    $search->index($db->insertID(), $text);
                }
                echo !$error ? l(good("Done!\n")) : "";
            } else {
                echo l(bad("Nothing to insert.\n"));
            }

        }

        if ( $favsync ) {

            // Checking personal favorites -- scanning all
            echo l("<div>");
            echo l("<p>");
            echo l("\n<strong>Syncing favourites...</strong>\n");
            $pages    = ceil($total / $maxCount); // Resetting these
            // $sinceID  = 0;
            // $maxID    = 0;
            $favs     = array();
            for($i = 0; $i < $pages; $i++){
                $path = "1/favorites.json?" . $p . "&count=" . $maxCount . ($i > 0 ? "&page=" . $i : "");
                echo l("Retrieving page <strong>#" . ($i+1) . "</strong>: <span class=\"address\">" . ls($path) . "</span>\n");

                // call the api once more!
                //---------------------------------------
                $data = $twitterApi->query($path);

                if(is_array($data) && $data[0] === false){ dieout(l(bad("Error: " . $data[1] . "/" . $data[2]))); }
                echo l("<strong>" . ($data ? count($data) : 0) . "</strong> total favorite tweets on this page\n");
                if(!$data){ break; } // No more tweets
                echo l("<ul>");
                foreach($data as $tweet){
                    if($tweet->user->id_str == $uid){
                        echo l("<li>" . $tweet->id_str . " " . $tweet->created_at . "</li>\n");
                        $favs[] = $tweet->id_str;
                    }
                }
                echo l("</ul>");
                echo l("</p>");
                if(count($data) > 0){ echo l("<strong>" . count($favs) . "</strong> favorite own tweets on this page\n"); }
                if(count($data) < ($maxCount - 50)){ break; } // We've reached last page
            }
            $db->query("UPDATE `".DTP."tweets` SET `favorite` = '0'"); // Blank all favorites
            $db->query("UPDATE `".DTP."tweets` SET `favorite` = '1' WHERE `tweetid` IN ('" . implode("', '", $favs) . "')");
            echo l(good("Updated favorites!"));
            echo l("</div>");

        }
    }


    function fetch_tweets($q) {
        global $db;
        $array     = is_array($q);
        $count     = $array ? count($q) : $db->numRows($q);
        $tweets = "";
        if($count > 0){
            if(!$array){
                while($tweet = $db->fetch($q)){
                    $tweets[] = $tweet;
                }
            } else {
                $tweets = $q;
            }
        }
        return $tweets;
    }


    function load_user($debug=TRUE){

        global $twitterApi, $config, $db;
        echo ( !$debug ) ? "" : l("Connecting & parsing...\n");
        if ( empty($_GET["screenname"]) )
            $path = "1/users/show.json?screen_name=" . $config['twitter_screenname'];
        else
            $path = "1/users/show.json?screen_name=" . trim($_GET[screenname]);



        echo ( !$debug ) ? "" : l("Connecting to: <span class=\"address\">" . ls($path) . "</span>\n");

        $data = $twitterApi->query($path);
        if($data){
            $extra = array(
                "created_at" => (string) $data->created_at,
                "utc_offset" => (string) $data->utc_offset,
                "time_zone"  => (string) $data->time_zone,
                "lang"       => (string) $data->lang,
                "profile_background_color"     => (string) $data->profile_background_color,
                "profile_text_color"           => (string) $data->profile_text_color,
                "profile_link_color"           => (string) $data->profile_link_color,
                "profile_sidebar_fill_color"   => (string) $data->profile_sidebar_fill_color,
                "profile_sidebar_border_color" => (string) $data->profile_sidebar_border_color,
                "profile_background_image_url" => (string) $data->profile_background_image_url,
                "profile_background_tile"      => (string) $data->profile_background_tile
            );
            echo ( !$debug ) ? "" : l("Checking...\n");
            $db->query("DELETE FROM `".DTP."tweetusers` WHERE `userid` = '0'"); // Getting rid of empty users created in error
            $q = $db->query("SELECT * FROM `".DTP."tweetusers` WHERE `userid` = '" . $db->s($data->id_str) . "' LIMIT 1");
            if($db->numRows($q) <= 0){
                $iq = "INSERT INTO `".DTP."tweetusers` (`userid`, `screenname`, `realname`, `location`, `description`, `profileimage`, `url`, `extra`, `enabled`) VALUES ('" . $db->s($data->id_str) . "', '" . $db->s($data->screen_name) . "', '" . $db->s($data->name) . "', '" . $db->s($data->location) . "', '" . $db->s($data->description) . "', '" . $db->s($data->profile_image_url) . "', '" . $db->s($data->url) . "', '" . $db->s(serialize($extra)) . "', '1');";
            } else {
                $iq = "UPDATE `".DTP."tweetusers` SET `screenname` = '" . $db->s($data->screen_name) . "', `realname` = '" . $db->s($data->name) . "', `location` = '" . $db->s($data->location) . "', `description` = '" . $db->s($data->description) . "', `profileimage` = '" . $db->s($data->profile_image_url) . "', `url` = '" . $db->s($data->url) . "', `extra` = '" . $db->s(serialize($extra)) . "' WHERE `userid` = '" . $db->s($data->id_str) . "' LIMIT 1";
            }
            echo ( !$debug ) ? "" : l("Updating...\n");
            $q = $db->query($iq);
            echo ( !$debug ) ? "" : ($q ? l(good("Done!")) : l(bad("DATABASE ERROR: " . $db->error())));
        } else { echo ( !$debug ) ? "" : l(bad("No data! Try again later.")); }
    }

    function delete_user($screenname,$tweets=TRUE){

        global $twitterApi, $config, $db;
        $q = $db->query("SELECT  * FROM `".DTP."tweetusers` WHERE `screenname` = '" . $db->s($screenname) . "'");
        $user      = $db->fetch($q);

        /** delete user tweet words first!*/
        $q = $db->query("SELECT  * FROM `".DTP."tweets` WHERE `userid` = '" . $db->s($user["userid"]) . "'");
        while ( $user_tweet = $db->fetch($q) ) {
            $db->query("DELETE FROM `".DTP."tweetwords` WHERE `tweetid` = '".$user_tweet["id"]."'"); // Getting rid of empty users created in error
        }

        /** delete the tweets now... */
        $db->query("DELETE FROM `".DTP."tweets` WHERE `userid` = '" . $db->s($user["userid"]) . "'");

        /** delete the user now... */
        $db->query("DELETE FROM `".DTP."tweetusers` WHERE `screenname` = '" . $db->s($screenname) . "'");

        if ( $_SESSION[tmhOauth]->screen_name == $screenname ) {
            unset($_SESSION[tmhOauth]);
            unset($_SESSION["user"]);
            unset($_SESSION["authtoken"]);
            unset($_SESSION["authsecret"]);

        }
    }


	/***
	 *
	 *	@param (string)
	 *
	 **/
	function getUserid($u) {
		global $authorextra;
	}


	function getUserWhere($u=false,$andInstead=false,$uid=FALSE){
        
		if ( empty($u) )
			return "";
		if ( !$uid )
			$qwhr = ( ($andInstead)?" AND ":" WHERE " ) . " `".DTP."tweetusers`.`screenname` = '$u' ";
		else 
			$qwhr = ( ($andInstead)?" AND ":" WHERE " ) . " userid = '$u' ";

		return $qwhr;
	}

	/***
     * check for maintenance mode
     */


	if ( $isMaint ) {

    //    $config['twitter_screenname'] = $u;

	} else {

		/***
		 *		assign user to view...
		 **/
   //     $u = $use_user = ( empty( $_SESSION["user"] ) ) ? $config['twitter_screenname'] : $_SESSION["user"] ;
	//	$config['twitter_screenname'] = $use_user;
	
	}








	/***
	 *		Author info for later use
	 **/
	$authorQ     = $db->query("SELECT * FROM `".DTP."tweetusers` 
		WHERE `screenname` = '" . $db->s($use_user) . "' 
		LIMIT 1");

	$author      = $db->fetch($authorQ);
    global $author, $authorextra;








    /***
     * check to see if they're in our dB
     * ------------------------------------
     */

    if ( $_SESSION[tmhOauth] ) {

        /** override the tmhOauth screen_name */
    //    $_SESSION[tmhOauth]->screen_name = "GingerB42";

        $authorQ     = $db->query("SELECT * FROM `".DTP."tweetusers`
            WHERE `screenname` = '" . $db->s($use_user) . "'
            LIMIT 1");

        $author      = $db->fetch($authorQ);

        /** @var $found - found us an already loaded user... */
        $found = !empty( $author );

    } else {

        $found = TRUE;

    }

    /***
     * if found - lets get the details of this bad boy...
     * if not found - let's poll twitter to get the details
     */
    if ( $found ) {

        $authorextra = unserialize($author['extra']);

    } else {


        switch ($_GET[loadtype]) {

            /***
             * need to have them load things up first....
             * -----------------------
             */
            case "":
                header("Location: ".APP_PATH."/loaduser/".$_SESSION[tmhOauth]->screen_name);
                exit();
                break;

            /***
             * need to have them load things up first....
             * -----------------------
             */
            case "tweets":
            //    header("Location: ".APP_PATH."/loaduser/".$_SESSION[tmhOauth]->screen_name);
            //    exit();
                break;

        }

    }





	/***
	 *		setup where clauses
	 **/
	$qwhr = array();
	if ( !empty( $u ) ) {
		$qwhr['where']          = getUserWhere($u);
        $qwhr['and']            = getUserWhere($u, TRUE);
        $qwhr['and_tu']         = str_replace(DTP."tweetusers","tu",$qwhr['and']);
		$qwhr['where_userid']   = getUserWhere($author[userid], FALSE, TRUE);
		$qwhr['and_userid']     = getUserWhere($author[userid], TRUE, TRUE);
	} else {
		// fill with blanks... to view all! :)
		$qwhr['where'] = $qwhr['and'] = $qwhr['where_userid'] = $qwhr['and_userid'] = "";
	}



    
    /***
     * login in/out link for navbar area...
     */
    $loginout_url = ( !empty($_SESSION[tmhOauth]) ) ? "
                        <a href='".APP_PATH."/auth/?action=logout'>
                                Logout
                        </a>
                    " : "
                        <a href='".APP_PATH."/auth/'>
                                Login & download tweets now!
                        </a>
                    " ;


    define("APP_OAUTH_USER",!empty($_SESSION["tmhOauth"]));