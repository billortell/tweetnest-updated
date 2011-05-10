<?php

if ( !function_exists('sys_get_temp_dir')) {
    function sys_get_temp_dir() {
        if( $temp=getenv('TMP') )        return $temp;
        if( $temp=getenv('TEMP') )        return $temp;
        if( $temp=getenv('TMPDIR') )    return $temp;
        $temp=tempnam(__FILE__,'');
        if (file_exists($temp)) {
            unlink($temp);
            return dirname($temp);
        }
        return null;
    }
}

// http://php.net/manual/en/function.unserialize.php
function isSerialized($str) {
    return ($str == serialize(false) || @unserialize($str) !== false);
}
//   exit("real tmp path is: ".realpath(sys_get_temp_dir()));

/***
 * to pseudo-remove the possibility of integers being converted
 * to gobbly gook scientific notation - we're going to convert all to string
 * if they're not already...
 ***/
function strCast($v) {
    return $v."";
}



/****
 * SAVING the "TEMP" file
 * ---------------------------------
 */

/***
 * instead download just use -
 * ==> filename = 'php://output'
 * else save file and push later.
 * ==> filename =  realpath(sys_get_temp_dir()) . '/' . $filename
 */

//$filename_path = realpath(sys_get_temp_dir()) . '/' . $filename_base;


/** @var $filename_path - create our own temp file destiny */
$tmp_filename =  EXPORT_TMP_PREFIX . "_" . $_SESSION["user"] . "_" .  EXPORT_TIMESTAMP . "." . EXPORT_SUFFIX ;
$filename_path = tempnam("/" . realpath(sys_get_temp_dir()) , $tmp_filename);
$fp = fopen( $filename_path, 'w');


if ( count( $exportArr ) > 0 ) {

    /** @var $list - grab only 3 tweets....  */
    $list = ( $limit > 0 ) ? array_slice($exportArr,0,EXPORT_TWEET_LIMIT,TRUE) : $exportArr ;

    /** init column headings being sent yet... **/
    $col_headings_sent = FALSE;


    /*** let's push it out to the file/window/browser **/
    foreach ($list as $fields) {

        $deny_extra = FALSE;

        if ( $deny_extra ) {
            unset($fields["extra"]);
        }

        $fields_new = $fields;

        if ( !empty($fields["extra"]) ) {
            $fields_extra = unserialize($fields["extra"]);
        //    $fields_new = array_merge($fields_new,$fields_extra);

            /** definitely get this, if we're tracking 'extra' field data */
            if ( !empty($fields_extra["retweet_count"]) ) {
                $fields_new["retweet_count"] = $fields_extra["retweet_count"] ;
            } else
                $fields_new["retweet_count"] = 0;
        }

        // set the default timezone to use. Available since PHP 5.1

        /** condition time */
        $fields_new["time_ez"] = date("r e",$fields_new["time"]) ;

        /** unset "rt" - as it's superfluous to what we already have! **/
        unset($fields_new["rt"]);
        unset($fields_new["id_str"]);

        $suspSerialized = array("rt","coordinates","geo","place","contributors");
        $suspArr = array();
        foreach ( $suspSerialized as $suspKey ) {
            if ( !empty( $fields_new[$suspKey] ) ) {
                $unser_susp = unserialize($fields_new[$suspKey]);
                if ( !empty( $unser_susp ) AND is_array($unser_susp) ) {

                //    echo "suspected unser:";
                //    echo "<pre>";
                //    print_r($unser_susp);
                //    echo "</pre>";

                    $unser_susp = array_map("strCast",$unser_susp);
                    array_merge($suspArr,$unser_susp);
                }
            //    $fields_new[$suspKey] = "";
            }
        }
        $fields_new = array_merge($fields_new, $suspArr);

        /** remove poss. for scient. notation */
        $fields_new = array_map("strCast",$fields_new);


        /** check for headings sent... */
        if ( !$col_headings_sent ) {
            fputcsv($fp, array_keys($fields_new), $delim);
            $col_headings_sent = TRUE;
        }

        /** send the real-actual data */
        fputcsv($fp, $fields_new, $delim);
    }

}
/** close it all off! */
fclose($fp);


/***
 * determine how we want the fies delivered...
 * if via .csv or .zip
 */
$zip = TRUE;
$fileOnly = TRUE;

if ( $zip ) {

    // incoming parameter to use: $filename_path
    //----------------------------
    $directoryToZip="./"; // This will zip all the file(s) in this present working directory

    $outputDir=$_SERVER[DOCUMENT_ROOT].APP_PATH."/temp/"; //Replace "/" with the name of the desired output directory.

//echo "outputDir: ".$outputDir."<br>";
$outputDir=$_SERVER[DOCUMENT_ROOT].dirname($_SERVER["SCRIPT_NAME"])."/temp/"; //Replace "/" with the name of the desired output directory.
//echo "outputDir: ".$outputDir."<br>";
//exit();
    $zipName="yourtweets.zip";

    include_once("CreateZipFile.inc.php");
    $createZipFile=new CreateZipFile;

    if ( !$fileOnly ) {

        //Code toZip a directory and all its files/subdirectories
        $createZipFile->zipDirectory($directoryToZip,$outputDir);

    } else {

        $fileToZip = $filename_path;
        $fp_main = "yourtweets.csv";

        // Code to Zip a single file
    //    $createZipFile->addDirectory($outputDir);
        $fileContents=file_get_contents($filename_path);
        $createZipFile->addFile($fileContents, $fp_main);

    }


    $rand=time();
    $zipName=$rand."_". $_SESSION["user"] ."_".$zipName;
    $fd=fopen($outputDir.$zipName, "wb");
    $out=fwrite($fd,$createZipFile->getZippedfile());
    fclose($fd);

    $createZipFile->forceDownload($outputDir.$zipName);
    @unlink($zipName);

} else {

    /****
     * DOWNLOAD the file
     * ---------------------------------
     */

    header('Content-Length:' . filesize($filename_path));
    header("Content-Disposition: attachment; filename=\"".$filename_base."\"");

    /** @var $filePointer - which file to connect/read and passthru from */
    $filePointer = fopen($filename_path,"rb");
    fpassthru($filePointer);

    @unlink($filename_path);

}

/** stop the streaming! */
exit();
