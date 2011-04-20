<?php
	// PONGSOCKET TWEET ARCHIVE
	// Image display extension
	
	if(!function_exists("imgid")){
		function imgid($path){
			$m = array();
			preg_match("@/([a-z0-9]+).*@i", $path, $m);
			if(count($m) > 0){
				return $m[1];
			}
			return false;
		}
	}
	
	class Extension_Images {
		public function enhanceTweet($tweet){
			$imgs  = array();
			$links = findURLs($tweet['text']);
			foreach($links as $link => $l){
				if(is_array($l) && array_key_exists("host", $l) && array_key_exists("path", $l)){
					$domain = domain($l['host']);
					$imgid  = imgid($l['path']);
					if($imgid){
						if($domain == "flic.kr"){
							$imgid = explode("/",$l['path']);
							$c = count($imgid) - 1;
							$imgid = $imgid[$c];
							$imgs[$link] = "http://flic.kr/p/img/".$imgid."_m.jpg";
						}
						if($domain == "twitpic.com"){
							$imgs[$link] = "http://twitpic.com/show/thumb/" . $imgid;
						}
						if($domain == "yfrog.com" || $domain == "yfrog.us"){
							$imgs[$link] = "http://yfrog.com/" . $imgid . ".th.jpg";
						}
						if($domain == "tweetphoto.com" || $domain == "pic.gd" || $domain == "plixi.com"){
							$imgs[$link] = "http://api.plixi.com/api/tpapi.svc/imagefromurl?size=medium&url=" . $link;
						}
						if($domain == "twitgoo.com"){
							$values = simplexml_load_string(getURL("http://twitgoo.com/api/message/info/" . $imgid));
							$imgs[$link] = (string) $values->thumburl;
						}
						if($domain == "img.ly"){
							$imgs[$link] = "http://img.ly/show/thumb/" . $imgid;
						}
						if($domain == "pict.mobi"){
							$imgs[$link] = "http://pict.mobi/show/thumb/" . $imgid;
						}
						if($domain == "imgur.com"){
							$imgs[$link] = "http://i.imgur.com/" . $imgid . "s.jpg";
						}
						if($domain == "twitvid.com"){
							$imgs[$link] = "http://images.twitvid.com/" . $imgid . ".jpg";
						}
						if($domain == "instagr.am"){
							$html = (string) getURL($link);
							preg_match('/<meta property="og:image" content="[^"]+"\/>/i', $html, $matches);
							if(isset($matches[0])){
								$imgs[$link] = substr($matches[0], 35, -3);
							}			
						}
					}
				}
			}
			if(count($imgs) > 0){
				$tweet['extra']['imgs'] = $imgs;
			}
			return $tweet;
		}
		
		public function displayTweet($d, $tweet){
			@$tweetextra = unserialize($tweet['extra']);
			if(array_key_exists("imgs", $tweetextra)){
				preg_match("/^([\t]+)</", $d, $m); $x = $m[1];
				$ds    = explode("\n", $d, 2);
				$imgd  = ""; $i = 1; $is = array();
				foreach($tweetextra['imgs'] as $link => $img){
					$imgd .= 
						$x . "\t<a class=\"pic pic-" . s($i) . "\" href=\"" . s($link) . "\">" .
						"<img src=\"" . s($img) . "\" alt=\"\" /></a>\n";
					$is[$link] = $i++;
				}
				foreach($is as $link => $i){
					$ds[1] = preg_replace(
						"/class=\"([^\"]*)\" href=\"" . preg_quote(s($link), "/") . "\"/",
						"class=\"$1 picl picl-" . s($i) . "\" href=\"" . s($link) . "\"", 
						$ds[1]
					);
				}
				$d     = implode("\n", array($ds[0], rtrim($imgd, "\n"), $ds[1]));
			}
			return array($d, $tweet);
		}
	}
	
	$o = new Extension_Images();