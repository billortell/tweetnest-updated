			</div>
			<div id="secondary">
				<?php echo ( !in_array($current_url_file,explode("|",ARCHIVES_HIDE_ON)) ) ? displayMonths() : "" ; ?>

                <!-- ads spot - could put *1* or dozens...  -->

                <div>
                    <?php echo $ad1;?>
                    <?php echo $ad2;?>
                </div>

                <div style="clear:both;"></div>
			</div>
            <div style="clear:both;"></div>
		</div>
		<div id="footer">
			&copy; <?php echo date("Y") . " <a href=\"http://twitter.com/" . s($config['twitter_screenname']) . "\">" . s($author['realname']) . "</a>"; ?>, powered by <a href="http://<?php echo $_SERVER[HTTP_HOST] . APP_PATH ; ?>">Tweetaculous</a>
		</div>
	</div>
</body>
</html>
<?php if($startTime){ echo "<!-- " . round((microtime(true) - $startTime), 5) . " s -->\n"; } ?>