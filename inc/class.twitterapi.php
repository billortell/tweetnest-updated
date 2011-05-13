<?php
	// TWEET NEST
	// Twitter API class
	// (a simple one)
	
	class TwitterApi {
		// HTTP grabbin' cURL options, by exsecror
		public $httpOptions = array(
			CURLOPT_FORBID_REUSE   => true,
			CURLOPT_POST           => false,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_TIMEOUT        => 30,
			CURLOPT_USERAGENT      => "Mozilla/5.0 (Compatible; libCURL)",
			CURLOPT_VERBOSE        => false,
			CURLOPT_SSL_VERIFYPEER => false // Insecurity?
		);
		public $dbMap = array(
			"id"           => "tweetid",
			"created_at"   => "time",
			"text"         => "text",
			"source"       => "source",
			"coordinates"  => "coordinates",
			"geo"          => "geo",
			"place"        => "place",
			"contributors" => "contributors",
			"user.id"      => "userid"
		);

        public $xHeaders = array();
        public $rate_limits;

        

        public function set_xHeaders($file){
            // x-headers to keep
            $pm = array(
                    "X-RateLimit-Limit"=>"hourly_limit",
                    "X-RateLimit-Remaining"=>"remaining_hits",
                    "X-RateLimit-Reset"=>"reset_time_in_seconds",
                    "X-Transaction"=>NULL
                );
            $xTmp = array();
            foreach ($pm as $pmp => $localKey){
                preg_match('|' . $pmp . ': ([0-9]+)|',$file,$b);
                $xTmp[$pmp] = (int)$b[1];
                if ( $localKey ) {
                    $this->rate_limits->$localKey = $xTmp[$pmp];
                }
            }
            // tack on current location's timestamp for relativity.
            $xTmp["current"] = time();
            $xTmp["tz_offset"] = 3600*round(($xTmp["X-Transaction"] - $xTmp["current"])/3600);
            $this->xHeaders = $xTmp;
            return $this->get_xHeaders();
        }

        public function get_xHeaders($p=false){
            if ( empty($this->xHeaders) )
                return false;
            return ( empty( $p ) ? $this->xHeaders : $this->xHeaders[$p] ) ;
        }

        public function withinLimit(){
            $xHead = $this->get_xHeaders();
            return ( empty($xHead) || $xHead['X-RateLimit-Remaining'] > 0 ) ;
        }

        // returns something like...
        // {"hourly_limit":150,"reset_time_in_seconds":1303842231,"reset_time":"Tue Apr 26 18:23:51 +0000 2011","remaining_hits":90}
        public function get_rate_limit_status(){
            $this->rate_limits = $this->query("1/account/rate_limit_status.json","json",NULL,FALSE);
            $this->rate_limits->xxx = 0; // should store a caching mechanism - to tell how valid this is... so we don't overcall it.
            return $this->rate_limits;
        }

        public function get_hourly_limit(){
            if ( empty($this->rate_limits) )
                return false;
            return $this->rate_limits->hourly_limit;
        }

        public function get_remaining_hits(){
            if ( empty($this->rate_limits) )
                return false;
            return $this->rate_limits->remaining_hits;
        }

        public function get_reset_time_in_seconds(){
            if ( empty($this->rate_limits) )
                return false;
            return $this->rate_limits->reset_time_in_seconds;
        }

        public function get_reset_time(){
            if ( empty($this->rate_limits) )
                return false;
            return $this->rate_limits->reset_time;
        }

		public function query($path, $format = "json", $auth = NULL, $ssl = true){
			$format = mb_strtolower(trim($format));
			$path   = ltrim($path, "/");
			if($format != "xml" && $format != "json"){ return false; }
			$url    = "http" . ($ssl ? "s" : "") . "://api.twitter.com/" . $path;
			$file   = "";

            if ( !empty( $this->rate_limits ) ) {
                if ( $this->get_remaining_hits() <= 0 ) {
                    echo ( !DEBUG_MAINTENANCE ) ? "" : l("returning... cuz we're over the limit!\n");
                    return false;
                } else {
                    echo ( !DEBUG_MAINTENANCE ) ? "" : l( $this->get_remaining_hits() - 1 ." calls will remain after this one!\n");
                }
            }

			do {

				if($file != ""){ sleep(2); } // Wait two secs if we got a failwhale
				$file = getURL($url, $auth);
				if(is_array($file)){ return $file; } // Error
                
			} while(
				// Protect against failwhale
				(
					($format == "xml" && mb_substr($file, 0, 2) != "<?") || // Invalid XML
					($format == "json" && !in_array(mb_substr($file, 0, 1), array("[", "{"))) // Invalid JSON
				)
				&& mb_substr_count(mb_strtolower($file), "over capacity") > 0
			);
			if($format == "xml"){
				$data = simplexml_load_string($file);
				if(!empty($data->error)){ die($data->error); }
				return $data;
			}
			if($format == "json"){
				// Prevent issues with long ints on 32-bit systems
				$file = preg_replace("/\"([a-z_]+_)?id\":(\d+)(,|\}|\])/", "\"$1id\":\"$2\"$3", $file);
				$data = json_decode($file);
				if(!empty($data->error)){ die($data->error); }
				return $data;
			}
			return false;
		}
		//TODO: BUILD IN SUPPORT FOR "RATE LIMIT EXCEEDED"
		
		public function validateUserParam($p){
			return (preg_match("/^user_id=[0-9]+$/", $p) || preg_match("/^screen_name=[0-9a-zA-Z_]+$/", $p));
		}
		
		public function getUserParam($str){
			list($name, $value) = explode("=", $str, 2);
			return array("name" => $name, "value" => $value);
		}
		
		public function userId($i){
			return "user_id=" . $i;
		}
		
		public function screenName($str){
			return "screen_name=" . $str;
		}
		
		public function getUserId($screenname){
			global $db;
			$q = $db->query("SELECT * FROM `".DTP."tweetusers` WHERE `screenname` = '" . $db->s($screenname) . "' LIMIT 1");
			if($db->numRows($q) > 0){
				$u = $db->fetch($q);
				return $u['userid'];
			}
			return false;
		}
		
		public function getScreenName($uid){
			global $db;
			$q = $db->query("SELECT * FROM `".DTP."tweetusers` WHERE `userid` = '" . $db->s($uid) . "' LIMIT 1");
			if($db->numRows($q) > 0){
				$u = $db->fetch($q);
				return $u['screenname'];
			}
			return false;
		}
		
		public function transformTweet($tweet){ // API tweet object -> DB tweet array
			$t = array(); $e = array(); 
			foreach(get_object_vars($tweet) as $k => $v){
				if(array_key_exists($k, $this->dbMap)){
					$key = $this->dbMap[$k];
					$val = $v;
					if(in_array($key, array("text", "source"))){
						$val = (string) $v;
					} elseif(in_array($key, array("tweetid", "id"))){
						$val = (string) $v; // Yes, I pass tweet id as string. It's a loooong number and we don't need to calc with it.
					} elseif($key == "time"){
						$val = strtotime($v);
					}
					$t[$key] = $val;
				} elseif($k == "user"){
					$t['userid'] = (string) $v->id_str;
				} elseif($k == "retweeted_status"){
					$rt = array(); $rte = array();
					foreach(get_object_vars($v) as $kk => $vv){
						if(array_key_exists($kk, $this->dbMap)){
							$kkey = $this->dbMap[$kk];
							$vval = $vv;
							if(in_array($kkey, array("text", "source"))){
								$vval = (string) $vv;
							} elseif(in_array($kkey, array("tweetid", "id"))){
								$vval = (string) $vv;
							} elseif($kkey == "time"){
								$vval = strtotime($vv);
							}
							$rt[$kkey] = $vval;
						} elseif($kk == "user"){
							$rt['userid']     = (string) $vv->id_str;
							$rt['screenname'] = (string) $vv->screen_name;
						} else {
							$rte[$kk] = $vv;
						}
					}
					$rt['extra'] = $rte;
					$e['rt']     = $rt;
				} else {
					$e[$k] = $v;
				}
			}
			$t['extra'] = $e;
			$tt = hook("enhanceTweet", $t, true);
			if(!empty($tt) && is_array($tt) && $tt['text']){
				$t = $tt;
			}
			return $t;
		}
		
		public function entityDecode($str){
			return str_replace("&amp;", "&", str_replace("&lt;", "<", str_replace("&gt;", ">", $str)));
		}
		
		public function insertQuery($t){
			global $db;
			$type = ($t['text'][0] == "@") ? 1 : (preg_match("/RT @\w+/", $t['text']) ? 2 : 0);
			return "INSERT INTO `".DTP."tweets` (`userid`, `tweetid`, `type`, `time`, `text`, `source`, `extra`, `coordinates`, `geo`, `place`, `contributors`) VALUES ('" . $db->s($t['userid']) . "', '" . $db->s($t['tweetid']) . "', '" . $db->s($type) . "', '" . $db->s($t['time']) . "', '" . $db->s($this->entityDecode($t['text'])) . "', '" . $db->s($t['source']) . "', '" . $db->s(serialize($t['extra'])) . "', '" . $db->s(serialize($t['coordinates'])) . "', '" . $db->s(serialize($t['geo'])) . "', '" . $db->s(serialize($t['place'])) . "', '" . $db->s(serialize($t['contributors'])) . "');";
		}
	}