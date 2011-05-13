<?php
	// TWEET NEST
	// Search class
	
	// Inspiration from http://www.zez.org/article/articleview/83/
	// Inspiration from PunBB
	
	class TweetNestSearch {
		public $minWordLength = 3;
		
		protected function stripWhitespace($text){
			$itext = str_replace(".", " ", $text);
			$itext = str_replace(",", " ", $itext);
			$itext = str_replace("'", " ", $itext);
			$itext = str_replace("\"", " ", $itext);
			$itext = str_replace("\n", " ", $itext);
			$itext = str_replace("\r", " ", $itext);
			$itext = preg_replace("/\s+/", " ", $itext);
			return $itext;
		}

        protected function stripURLs($t){
            $f = "#\b(https?|ftp|file)://[-A-Z0-9+&@\#/%?=~_|!:,.;]*[-A-Z0-9+&@\#/%=~_|]#i";
            return preg_replace($f, '', $t); //this regex is the best!
        }

        protected function stripStopWords($t){
            $sw = array("a","able","about","above","abst","accordance","according","accordingly","across","act","actually","added","adj","adopted","affected","affecting","affects","after","afterwards","again","against","ah","all","almost","alone","along","already","also","although","always","am","among","amongst","an","and","announce","another","any","anybody","anyhow","anymore","anyone","anything","anyway","anyways","anywhere","apparently","approximately","are","aren","arent","arise","around","as","aside","ask","asking","at","auth","available","away","awfully","b","back","be","became","because","become","becomes","becoming","been","before","beforehand","begin","beginning","beginnings","begins","behind","being","believe","below","beside","besides","between","beyond","biol","both","brief","briefly","but","by","c","ca","came","can","cannot","can't","cause","causes","certain","certainly","co","com","come","comes","contain","containing","contains","could","couldnt","d","date","did","didn't","different","do","does","doesn't","doing","done","don't","down","downwards","due","during","e","each","ed","edu","effect","eg","eight","eighty","either","else","elsewhere","end","ending","enough","especially","et","et-al","etc","even","ever","every","everybody","everyone","everything","everywhere","ex","except","f","far","few","ff","fifth","first","five","fix","followed","following","follows","for","former","formerly","forth","found","four","from","further","furthermore","g","gave","get","gets","getting","give","given","gives","giving","go","goes","gone","got","gotten","h","had","happens","hardly","has","hasn't","have","haven't","having","he","hed","hence","her","here","hereafter","hereby","herein","heres","hereupon","hers","herself","hes","hi","hid","him","himself","his","hither","home","how","howbeit","however","hundred","i","id","ie","if","i'll","im","immediate","immediately","importance","important","in","inc","indeed","index","information","instead","into","invention","inward","is","isn't","it","itd","it'll","its","itself","i've","j","just","k","keep","keeps","kept","keys","kg","km","know","known","knows","l","largely","last","lately","later","latter","latterly","least","less","lest","let","lets","like","liked","likely","line","little","'ll","look","looking","looks","ltd","m","made","mainly","make","makes","many","may","maybe","me","mean","means","meantime","meanwhile","merely","mg","might","million","miss","ml","more","moreover","most","mostly","mr","mrs","much","mug","must","my","myself","n","na","name","namely","nay","nd","near","nearly","necessarily","necessary","need","needs","neither","never","nevertheless","new","next","nine","ninety","no","nobody","non","none","nonetheless","noone","nor","normally","nos","not","noted","nothing","now","nowhere","o","obtain","obtained","obviously","of","off","often","oh","ok","okay","old","omitted","on","once","one","ones","only","onto","or","ord","other","others","otherwise","ought","our","ours","ourselves","out","outside","over","overall","owing","own","p","page","pages","part","particular","particularly","past","per","perhaps","placed","please","plus","poorly","possible","possibly","potentially","pp","predominantly","present","previously","primarily","probably","promptly","proud","provides","put","q","que","quickly","quite","qv","r","ran","rather","rd","re","readily","really","recent","recently","ref","refs","regarding","regardless","regards","related","relatively","research","respectively","resulted","resulting","results","right","run","s","said","same","saw","say","saying","says","sec","section","see","seeing","seem","seemed","seeming","seems","seen","self","selves","sent","seven","several","shall","she","shed","she'll","shes","should","shouldn't","show","showed","shown","showns","shows","significant","significantly","similar","similarly","since","six","slightly","so","some","somebody","somehow","someone","somethan","something","sometime","sometimes","somewhat","somewhere","soon","sorry","specifically","specified","specify","specifying","state","states","still","stop","strongly","sub","substantially","successfully","such","sufficiently","suggest","sup","sure","t","take","taken","taking","tell","tends","th","than","thank","thanks","thanx","that","that'll","thats","that've","the","their","theirs","them","themselves","then","thence","there","thereafter","thereby","thered","therefore","therein","there'll","thereof","therere","theres","thereto","thereupon","there've","these","they","theyd","they'll","theyre","they've","think","this","those","thou","though","thoughh","thousand","throug","through","throughout","thru","thus","til","tip","to","together","too","took","toward","towards","tried","tries","truly","try","trying","ts","twice","two","u","un","under","unfortunately","unless","unlike","unlikely","until","unto","up","upon","ups","us","use","used","useful","usefully","usefulness","uses","using","usually","v","value","various","'ve","very","via","viz","vol","vols","vs","w","want","wants","was","wasn't","way","we","wed","welcome","we'll","went","were","weren't","we've","what","whatever","what'll","whats","when","whence","whenever","where","whereafter","whereas","whereby","wherein","wheres","whereupon","wherever","whether","which","while","whim","whither","who","whod","whoever","whole","who'll","whom","whomever","whos","whose","why","widely","willing","wish","with","within","without","won't","words","world","would","wouldn't","www","x","y","yes","yet","you","youd","you'll","your","youre","yours","yourself","yourselves","you've","z","zero");
            $wordlist = implode("|", $sw);
            $t = preg_replace("/\b($wordlist)\b/ie", 'str_repeat("*", strlen("\\1")) ', $t);
            $t = str_replace("*","",$t);
            return $t;
        }

		protected function keywordify($text){
            // Immediately strip urls from keywordification...
            $text = $this->stripURLs($text);
            // Immediately strip stop words from keywordification...
            $text = $this->stripStopWords($text);
			// No fancy apostrophes and dashes in keywords
			$text = strtolower(stupefyRaw($text, true));
			// Remove any apostrophes which aren't part of words
			$keywords = substr(preg_replace("((?<=\W)'|'(?=\W))", "", " " . $text . " "), 1, -1);
			// Remove symbols and multiple whitespace
			$keywords = preg_replace("/[\^\$&\(\)<>`\"\|,@_\?%~\+\[\]{}:=\/#\\\\;!\.\s]+/", " ", $keywords);
			return $keywords;
		}
		
		public function index($id, $text){
			global $db;
			$itext = $this->keywordify($text);
			
			$words = explode(" ", $itext);
			$wordcount = count($words);
			$uniques = array_count_values($words);
			$words = array_unique($words); // Strip duplicate words

			foreach($words as $word){
				if(strlen($word) >= $this->minWordLength){
					// Does word already exist?
					$wQ = $db->query("SELECT * FROM `".DTP."words` WHERE `word` = '" . $db->s($word) . "' LIMIT 1");
					if($db->numRows($wQ) > 0){
						$w = $db->fetch($wQ);
						$wordID = $w['id'];
						$db->query("UPDATE `".DTP."words` SET `tweets` = `tweets`+1 WHERE `id` = '" . $db->s($wordID) . "' LIMIT 1");
					} else {
						$db->query("INSERT INTO `".DTP."words` (`word`, `tweets`) VALUES ('" . $db->s($word) . "', '1')");
						$wordID = $db->insertID();
					}
					// Relevance value
					$frequency = $uniques[$word] / $wordcount;
					$db->query("INSERT INTO `".DTP."tweetwords` (`tweetid`, `wordid`, `frequency`) VALUES ('" . $db->s($id) . "', '" . $db->s($wordID) . "', '" . $db->s($frequency) . "')");
				}
			}
		}


        /****
         * @param  $tnid - tweet nest's twitter status id (tweet id)
         * @param string $extraWhere
         * @return array|bool|DB_result|resource
         */
        public function query_by_id($tnid, $extraWhere = ""){
            global $db;
            global $qwhr_and, $qwhr;

            if ( !empty($extraWhere) ) {
                $extraWhere = " AND " . $extraWhere;
            }

            $extraWhere .= " AND t.tweetid = '$tnid' ";

            /***
             * works!
             *
                SELECT `t`.*, `tu`.`screenname`, `tu`.`realname`, `tu`.`profileimage` FROM
                tn_tweets t
                LEFT JOIN `tn_tweetusers` `tu` ON `t`.`userid` = `tu`.`userid`
                WHERE
                `t`.`tweetid` = '63248179071696896'
             *
             */
            // Do it!
            $tweets = array();
            $query  = $db->query(
                "SELECT t.*, tu.screenname, tu.realname, tu.profileimage " .
                "FROM ".DTP."tweets t " .
                "LEFT JOIN `".DTP."tweetusers` `tu` ON `t`.`userid` = `tu`.`userid` " .
                "WHERE 1 " . $extraWhere . " "
            );


            // Get all instances
            while($t = $db->fetch($query)){
                $tweets[] = $t;
            }

            // How many instances of each tweet? The more, the more relevant it is
            return $tweets;
        }



        /***
         * @param  $q
         * @param string $sort
         * @param string $extraWhere
         * @return array|bool|DB_result|resource
         */
		public function query($q, $sort = "relevance", $extraWhere = ""){
			global $db;
			global $qwhr_and, $qwhr;

        //    echo "qwhr_and: ".$qwhr_and."<br/>";
        //    echo "<pre>";
        //    print_r($qwhr);
        //    echo "</pre>";
            
            
			$stf   = 0.7; // Search words can at most be present in 70% of the tweets
			if(strlen($q) < $this->minWordLength){ return false; } // <3 ;)
			
			$qtext = $this->keywordify($q);
			$words = explode(" ", $qtext);
			$words = array_unique($words);
			
			// Get total amount of tweets
			$tQ    = $db->query("SELECT COUNT(*) AS `count` FROM `".DTP."tweets`");
			$t     = $db->fetch($tQ);
			$total = $t['count'];
			if($total < 1){ return false; }
			
			// Build query string
			$sqlA = ""; $sqlO = "";
			foreach($words as $word){
				if($sqlA != ""){ $sqlA .= " AND "; $sqlO .= " OR "; }
				$ws    = "`w`.`word` LIKE '" . $db->s(str_replace("*", "%", $word)) . "'";
				$sqlA .= $ws;
				$sqlO .= $ws;
			}
			
			// Are we just requesting the months?
			if($sort == "months"){
           //     exit();
                
				return $db->query(
					"SELECT MONTH(FROM_UNIXTIME(`t`.`time`)) AS `m`, YEAR(FROM_UNIXTIME(`t`.`time`)) AS `y`, COUNT(DISTINCT `t`.`id`) AS `c` " .
					"FROM `".DTP."tweets` `t` " .
					"INNER JOIN `".DTP."tweetwords` `tw` ON `t`.`id` = `tw`.`tweetid` " .
					"INNER JOIN `".DTP."words` `w` ON `tw`.`wordid` = `w`.`id` " .
					"WHERE (" . $sqlO . ") AND ((`w`.`tweets` / " . $total . ") < " . $stf . ") " .

                    /** srching amongst the loggedin/session'd user only! */
                    (($_GET[meonly])?$qwhr['and_tu']:"") .

					"GROUP BY `y`, `m` ORDER BY `y` DESC, `m` DESC"
				);
			}
			
			// Do it!
			$tweets = array();
			$query  = $db->query(
				"SELECT `w`.`word`, `t`.*, `tu`.`screenname`, `tu`.`realname`, `tu`.`profileimage` " .
				"FROM `".DTP."words` `w` " .
				"INNER JOIN `".DTP."tweetwords` `tw` ON `tw`.`wordid` = `w`.`id` " .
				"INNER JOIN `".DTP."tweets` `t` ON `tw`.`tweetid` = `t`.`id` " .
				"LEFT JOIN `".DTP."tweetusers` `tu` ON `t`.`userid` = `tu`.`userid` " .
				"WHERE (" . $sqlO . ") AND ((`w`.`tweets` / " . $total . ") < " . $stf . ")" . $extraWhere . " " .

                /** srching amongst the loggedin/session'd user only! */
                (($_GET[meonly])?$qwhr['and_tu']:"") .
                        
				"ORDER BY " . ($sort == "time" ? "`t`.`time` DESC" : "`tw`.`frequency` DESC")
			);


			if($sort == "time"){
				while($t = $db->fetch($query)){
					// We don't want duplicates when sorting by time
					// .. but we do want accumulated word listing anyway
					$word = $t['word'];
					if(isset($tweets[$t['id']])){
						$t['word'] = $tweets[$t['id']]['word'];
						if(is_array($t['word'])){
							$t['word'][] = $word;
						} else {
							$t['word'] = array($t['word'], $word);
						}
					}
					$tweets[$t['id']] = $t;
				}
				return array_values($tweets);
			} else {
				// Get all instances
				while($t = $db->fetch($query)){
					if(!isset($tweets[$t['id']])){
						$tweets[$t['id']] = array();
					}
					$tweets[$t['id']][] = $t;
				}
				// How many instances of each tweet? The more, the more relevant it is
				$tweetsc = array();
				foreach($tweets as $tid => $ta){
					$c     = count($ta);
					$tweet = $ta[0];
					if(!isset($tweetsc[$c])){
						$tweetsc[$c] = array();
					}
					if($c > 1){
						$words = array();
						foreach($ta as $t){
							$words[] = $t['word'];
						}
						$tweet['word'] = $words;
					}
					$tweetsc[$c][] = $tweet;
				}
				// Final list in correct order
				krsort($tweetsc);
				$tweetlist = array();
				foreach($tweetsc as $c => $tweets){
					foreach($tweets as $tweet){
						$tweetlist[] = $tweet;
					}
				}
				return $tweetlist;
			}
		}
		
		public function monthsQuery($q){
			return $this->query($q, "months");
		}
	}