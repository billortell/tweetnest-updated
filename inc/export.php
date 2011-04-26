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
$tmp_filename =  EXPORT_TMP_PREFIX . EXPORT_TIMESTAMP . "." . EXPORT_SUFFIX ;
$filename_path = tempnam("/" . realpath(sys_get_temp_dir()) , $tmp_filename);
$fp = fopen( $filename_path, 'w');


if ( count( $exportArr ) > 0 ) {

    /** @var $list - grab only 3 tweets....  */
    $list = ( $limit > 0 ) ? array_slice($exportArr,0,EXPORT_TWEET_LIMIT,TRUE) : $exportArr ;

    /** init column headings being sent yet... **/
    $col_headings_sent = FALSE;


    /*** let's push it out to the file/window/browser **/
    foreach ($list as $fields) {
        $fields_orig = $fields;

        unset($fields_orig["extra"]);
        $fields_extra = unserialize($fields["extra"]);
        $fields_new = array_merge($fields_orig,$fields_extra);


        $suspSerialized = array("coordinates","geo","place","contributors");
        $suspArr = array();
        foreach ( $suspSerialized as $suspKey ) {
            $unser_susp = unserialize($fields_new[$suspKey]);
            if ( !empty( $unser_susp ) ) {
                array_merge($suspArr,$unser_susp);
            }
            $fields_new[$suspKey] = "";
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


/****
 * DOWNLOAD the file
 * ---------------------------------
 */

header('Content-Length:' . filesize($filename_path));
header("Content-Disposition: attachment; filename=\"".$filename_base."\"");

/** @var $filePointer - which file to connect/read and passthru from */
$filePointer = fopen($filename_path,"rb");
fpassthru($filePointer);

/** stop the streaming! */
exit();
