<?php
define('USE_ORIGINAL',-1000);

class TwitterAppInterface extends TwitterApp {
    
    /**
     * The path to our temporary files directory
     *
     * @var string Path to store image files
     */
    public $path;

    /**
     * The path to our final image that was pushed to Twitter Profile
     *
     * @var string Path to store FINAL (manipulated) image
     */
    public $final_path;

    /**
     * Initialize a new TwitterAvatars object
     *
     * @param tmhOAuth $tmhOAuth A tmhOAuth object with consumer key and secret
     * @param string $path Path to store image files (default 'tmp')
     */
    public function  __construct(tmhOAuth $tmhOAuth, $path = 'tmp') {
        
        // call the parent class' constructor
        parent::__construct($tmhOAuth);

        /** put the userdata into an array to access later? */
        $_SESSION["tmhOauth"] = $this->userdata;

        /** prep for temp filepaths... uploading, .zipping, etc... */
        if ( $this->confDir($path) ) {

            /** added username to multi-user-ify it all */
            // save the path variable
            if ($this->confDir($path . '/' . $this->userdata->screen_name)) {
                $this->path = $path . '/' . $this->userdata->screen_name;
            }

        }
        else
            die("<h1>Ooops!</h1><p>Seems you need to change your permissions on " . $this->truepath($_SERVER[SCRIPT_URI].$path) . " to 775. Fix and rerun!</p>");

    }

    /**
     * This function is to replace PHP's extremely buggy realpath().
     * @param string The original path, can be relative etc.
     * @return string The resolved path, it might not exist.
     */
    public function truepath($path){
        // attempts to detect if path is relative in which case, add cwd
        if(strpos($path,':')===false && (strlen($path)==0 || $path{0}!='/')){
            $path=getcwd().DIRECTORY_SEPARATOR.$path;
        }
        // resolve path parts (single dot, double dot and double delimiters)
        $path = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $path);
        $parts = array_filter(explode(DIRECTORY_SEPARATOR, $path), 'strlen');
        $absolutes = array();
        foreach ($parts as $part) {
            if ('.'  == $part) continue;
            if ('..' == $part) {
                array_pop($absolutes);
            } else {
                $absolutes[] = $part;
            }
        }
        $path=implode(DIRECTORY_SEPARATOR, $absolutes);
        // if file exists and it is a link, use readlink to resolves links
        if(file_exists($path) && linkinfo($path)>0)$path=readlink($path);
        return $path;
    }

    function confDir($fe) {

        if (!file_exists($fe)) {
            if ($md = @mkdir($fe, 0777) && $od = @chmod($fe, 0777))
                return true;
            else
                return false;

        }
        return true;
    }

    /**
     * Download data from specified URL
     *
     * @param string $url URL to download
     * @return string Downloaded data
     */
    protected function download($url) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $ret = curl_exec($ch);
        curl_close($ch);

        return $ret;
    }


    /**
     * Save a GD image resource to a PNG file
     *
     * @param resource $img GD image resource identifier
     * @param string $name Name of the image
     * @return string Path to the saved image
     */
    protected function saveImage($img, $name, $ovr=FALSE) {
        /** @var $path resulting 'net' path to save images */
        $path = $this->path . '/' . $name . '.png';
        if ( $ovr )
            $this->rmvIfExists($path);
        imagepng($img, $path);
      //  imagedestroy($img);
        return $path;
    }


    public function rmvIfExists($path){
        if ( file_exists($path) )
            unlink($path);
    }



    private function curlImageParam($path){
        if ( $_SERVER[HTTP_HOST]=="localhost" ){
            return '@' . $path . ';type=image/png';
        } else {
            return '@' . SITE_PATH . "/" . $path . ';type=image/png;filename=' . basename($path);
        }
    }



    private function getErrorCode($code){
        switch ($code){
            case 200:
                return true;
                break;
            default:
                echo "[ERROR: $code] ... uh, something happened!<br>";
                echo "<pre style='text-align:left;'>";
                print_r($this->tmhOAuth->response);
                echo "</pre>";
                break;
        }
    }




    public function globToArr($fileGlob=array(),$qty=null){

        $fileArr = array();
        // have we checked within the last hour?
        if (!empty($fileGlob)) {

            // loop through files, deleting old files (12+ hours)
            foreach ($fileGlob as $file) {
                $fileArr[] = $file;
                if ( count($fileArr) == $qty && $qty )
                    break;  // exit out cuz we want no more!
            }

        }
        return $fileArr;
    }

    /***
     * @param array $paths (or string that we make into an array)
     * @return array of paths (de-ROOT-ized)
     */
    public function deRootize($paths=array()){
        if ( empty($paths))
            return array(); // no files to process

        if ( !is_array($paths) )
            $paths = array($paths);

        if ( is_array($paths) ) {
            $newPaths = array();
            foreach ( $paths as $path ){
                $newPaths[] = "/".ltrim(str_replace($_SERVER[DOCUMENT_ROOT],"",$path),"/");
            }
        }

        return $newPaths;
    }



}
