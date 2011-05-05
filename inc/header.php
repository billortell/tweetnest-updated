<?php

$ad1 = <<< END
<img style='margin: 7px auto;' src='http://dummyimage.com/175x70.png/ff5e99/fff&text=ad+spot+1' border="0" alt="ad spot 1"/>
END;
$ad2 = <<< END
<img style='margin: 7px auto;' src='http://dummyimage.com/175x70.png/1FA9E6/fff&text=ad+spot+1' border="0" alt="ad spot 1"/>
END;

$ga_top = <<< END
<script type="text/javascript"><!--
google_ad_client = "ca-pub-4505986509846513";
/* tweetstuff_468x15 */
google_ad_slot = "9802607339";
google_ad_width = 468;
google_ad_height = 15;
//-->
</script>
<script type="text/javascript"
src="http://pagead2.googlesyndication.com/pagead/show_ads.js">
</script>
END;





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


	// TWEET NEST
	// HTML Header

	header("Content-Type: text/html; charset=utf-8");
	$path = s(rtrim($config['path'], "/"));
	$headTitle = "Tweets by @" . s($config['twitter_screenname']) . ($pageTitle ? " / " . p(s($pageTitle), 3) : "");
	$styleFile = (substr($config['css'], 0, 7) == "http://" || substr($config['css'], 0, 8) == "https://") ? $config['css'] : $path . "/" . ltrim($config['css'], "/");
	unset($config['twitter_password'], $config['db']['password']); // Some sort of security
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title><?php echo $headTitle; ?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="description" content="An archive of all tweets written by <?php echo s(rtrim($author['realname'], ".")); ?>." />
	<meta name="author" content="<?php echo s($author['realname']); ?>" />
	<link rel="stylesheet" href="<?php echo s($styleFile); ?>?csstype=<?php echo $csstype;?>" type="text/css" />
	<?php if($isSearch):?>
	<link rel="alternate" type="application/atom+xml" href="<?php echo $path; ?>/searchfeed?q=<?php echo s($searchQuery); ?>" title="Atom Feed" />	
	<?php endif;?>
	<link rel="stylesheet" href="<?php echo $path; ?>/emoji.css" type="text/css" />
	<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/<?php echo s($jQueryVersion); ?>/jquery.min.js"></script>
<?php if($config['anywhere_apikey']){ ?><script type="text/javascript" src="http://platform.twitter.com/anywhere.js?id=<?php echo s($config['anywhere_apikey']); ?>&amp;v=1"></script><?php echo "\n"; } ?>
	<script type="text/javascript" src="<?php echo $path; ?>/tweets.js"></script>
</head>
<body>
	<div id="container">

        <?php if ( APP_SHOW_HEADER_SECTION ) { ?>
		<div id="top">

            <div id="author">
                
                <?php if ( $config["logo"] ) { ?>
                <a href="<?php echo APP_PATH;?>">
                    <div class=nest_logo></div>
                </a>
                <?php } ?>

                <img src="<?php echo s($author['profileimage']); ?>" style='margin-top: 5px; margin-bottom: 10px;' width="48" height="48" alt="" />
                <h2>
                    <a href="<?php echo $path."/user/".s($author['screenname']); ?>">
                        <strong><?php echo s($author['realname']); ?></strong>
                    </a>
                </h2>
                <p>
                    <a href="http://twitter.com/<?php echo s($author['screenname']); ?>" target="_blank">
                        <?php echo s(""); ?>
                        <strong>@<?php echo s($author['screenname']); ?></strong>
                    </a>
                </p>
                <p class=location style='display:none;'><?php echo s($author['location']); ?></p>
			</div>


			<div id="info">

                <p>The below is an off-site archive of all tweets posted by <strong >@<?php echo s($author['screenname']); ?></strong> ever</p>

                <?php if($config['follow_me_button']){ ?>
                <p class="follow"><a href="http://twitter.com/<?php echo s($config['twitter_screenname']); ?>">
                    Follow me on Twitter
                </a>
                </p>
                <?php echo "\n"; } ?>


			</div>

		</div>
        <?php } ?>



		<div id="content" class="<?php echo $content_class;?>">

            <div>

                <?php if ( APP_SHOW_NAVBAR ) { ?>
                <div id=navbar>

                    <!-- login/out url link -->
                    <div style='float:left; padding: 10px auto;font-weight:bold;'>
                        <?php echo $loginout_url; ?>
                    </div>

                    <!-- download tweets, search, user list nav bar links -->
                    <div style='float:right; padding: 10px auto; font-weight:bold;'>
                        <a href="<?php echo APP_PATH;?>/download">
                                download your tweets!
                        </a>
                    </div>
                    <div class='search_form_toggle' style='float:right; padding: 10px auto;font-weight:bold;'>
                        <a href="javascript:void(0);">
                            use search tool
                        </a>
                    </div>
                    <div style='float:right; padding: 10px auto; font-weight:bold;'>
                        <a href="<?php echo APP_PATH;?>/users">
                                user list
                        </a>
                    </div>


                    <div style='clear:both;'></div>
                </div>
                <?php } ?>


                <div class='search_form' style='display:none;'>

                    <form id="search_big" action="<?php echo $path; ?>/search" method="get">
                        <div>
                            type in any word, combination of words, we'll find it!
                            <br/>
                            <input type="text" name="q" value="<?php if($searchQuery){ echo s($searchQuery); } ?>" />
                            <br/>
                            <span class="meonly"><?php echo !empty($_SESSION[user])? "search within <strong>".$_SESSION[user]."</strong> only " : "" ;?></span>
                            <input type="checkbox" name="meonly" <?php echo ( empty($_GET[q]) || $_GET[meonly] || ( $_GET[q] AND !empty($_GET[meonly]) ) ) ? "checked=checked":"";?> />
                        </div>
                    </form>
                    <div style='clear:both;'></div>

                </div>
                <div style='clear:both;'></div>
            </div>
            <div style='clear:both;'></div>


            <!-- separation of title and 'ads' on right? -->

            <div style='float:left; width: 70%;'>

                <h1><?php echo $pageHeader ? p(s($pageHeader, ENT_NOQUOTES), 3, true) : p(s($pageTitle, ENT_NOQUOTES), 3, true); ?></h1>

                <?php if ($pageTitleSearchTerms) : ?>
                <h2><strong class="searchword"><?php echo $pageTitleSearchTerms;?></strong></h2>
                <?php endif; ?>

            </div>


            <div style='float:right; width: 26%;font-size: 85%;'>
                <?php //echo displayMonths(4,TRUE);?>
            </div>





            <div style="clear:both;"></div>

            <?php if ( APP_SHOW_ARCHIVE_CHART ) { ?>
            <?php if($preBody){ echo "\t\t\t" . $preBody . "\n"; } ?>
            <?php } ?>


            <div id="c"><div id="primary">
