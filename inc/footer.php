			</div>


            <?php if ( APP_SHOW_SIDEBAR ) { ?>

			<div id="secondary">
                
				<?php echo ( !in_array($current_url_file,explode("|",ARCHIVES_HIDE_ON)) ) ? displayMonths() : "" ; ?>

                <!-- ads spot - could put *1* or dozens...  -->

                <div style='display:none;'>
                    <?php echo str_repeat($ad2.$ad1,1);?>
                </div>

                <div style='padding:7px;'>

                    <script type="text/javascript"><!--
                    google_ad_client = "ca-pub-4505986509846513";
                    /* tweetstuff_120x240 */
                    google_ad_slot = "1947663500";
                    google_ad_width = 120;
                    google_ad_height = 240;
                    //-->
                    </script>
                    <script type="text/javascript"
                    src="http://pagead2.googlesyndication.com/pagead/show_ads.js">
                    </script>

                </div>


                <div style='padding:7px; display:none;'>
    
                    <script type="text/javascript"><!--
                    google_ad_client = "ca-pub-4505986509846513";
                    /* twitterstuff_120x600_text */
                    google_ad_slot = "9713979669";
                    google_ad_width = 120;
                    google_ad_height = 600;
                    //-->
                    </script>
                    <script type="text/javascript"
                    src="http://pagead2.googlesyndication.com/pagead/show_ads.js">
                    </script>

                </div>


                <div style="clear:both;"></div>
			</div>

            <?php } ?>
                
            <div style="clear:both;"></div>
		</div>
	</div>

    <?php if ( APP_SHOW_FOOTER_SECTION ) { ?>
    <div id="footer">
        &copy; <?php echo date("Y") . " <a href=\"http://twitter.com/" . s($config['twitter_screenname']) . "\">" . s($author['realname']) . "</a>"; ?>, powered by
        <strong><a href="http://<?php echo $_SERVER[HTTP_HOST] . APP_PATH ; ?>">Tweetaculous</a></strong>
    </div>
    <?php } ?>

    <?php if($startTime){ echo "<!-- " . round((microtime(true) - $startTime), 5) . " s -->\n"; } ?>

    </body>
</html>
