<?php

$ad1 = <<< END
<img src='http://dummyimage.com/175x70.png/f90/fff&text=ad+spot+1' border="0" alt="ad spot 1"/>
END;


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
	<link rel="stylesheet" href="<?php echo s($styleFile); ?>" type="text/css" />
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
		<div id="top">

			<div id="author">
                
                <?php if ( $config["logo"] ) { ?>
                <div class=nest_logo></div>
                <?php } ?>

                <img src="<?php echo s($author['profileimage']); ?>" width="48" height="48" alt="" />
                <h2>
                    <a href="<?php echo $path."/user/".s($config['twitter_screenname']); ?>">
                        <strong><?php echo s($author['realname']); ?></strong>
                    </a>
                </h2>
                <p>
                    <strong>@<?php echo s($config['twitter_screenname']); ?></strong> -
                    <a href="http://twitter.com/<?php echo s($config['twitter_screenname']); ?>">
                        <?php echo s("stalk me!"); ?>
                    </a>
                </p>
                <p><?php echo s($author['location']); ?></p>
			</div>
            

			<div id="info">
				<p>The below is an off-site archive of all tweets posted by <strong >@<?php echo s($config['twitter_screenname']); ?></strong> ever</p>

                <?php if($config['follow_me_button']){ ?>
                <p class="follow"><a href="http://twitter.com/<?php echo s($config['twitter_screenname']); ?>">
                    Follow me on Twitter
                </a>
                </p>
                <?php echo "\n"; } ?>


			</div>

		</div>



		<div id="content" class="<?php echo $content_class;?>">

            <div>
                <div style='float:right; padding: 10px auto;'>
                    <a href="<?php echo APP_PATH;?>/download">
                            <strong>download your tweets!</strong>
                    </a>
                </div>

                <div class='search_form_toggle'>
                    <span>use search tool</span>
                </div>
                <div style='clear:both;'></div>

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
                <div style='display:none;'>
                    <form id="search" action="<?php echo $path; ?>/search" method="get">
                        <div>
                            <input type="text" name="q" value="<?php if($searchQuery){ echo s($searchQuery); } ?>" />
                            <br/><span class="meonly"><?php echo !empty($_SESSION[user])? $_SESSION[user]." only " : "" ;?></span>
                            <input type="checkbox" name="meonly" checked=checked />
                        </div>
                    </form>
                </div>
            </div>


            <div style="clear:both;"></div>
<?php if($preBody){ echo "\t\t\t" . $preBody . "\n"; } ?>
			<div id="c"><div id="primary">
