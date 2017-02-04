<?php
/**
 * @uri /plugin/file/{name}
 * @uri /plugin/file/{name}/{func}
 * @uri /plugin/file/{name}/{func}/(.*)
 */
Class FilePlugin extends Tonic\Resource {
    static $capabilities = array('browse', 'search');

    /**
     * Load settings
     */
    function initialize($name){
        $this->settings = @$this->conf['plugins'][$name];
        if($this->settings['class'] != __CLASS__)
            throw new Tonic\NotFoundException;
    }

    /**
     * Urlencodes a path without encoding the slashes
     */
    function encode_path($path){
        $parts = explode('/', $path);
        $removed = array();
        if(substr($parts[0], 0, 4) == "http")
            $removed = array_splice($parts, 0, 1);

        $encoded = array_map("urlencode",$parts);
        $result = array_merge($removed, $encoded);
        return implode('/', $result);
    }

    /**
     * Returns some information about this plugin
     * @method GET
     * @func
     * @priority 1
     */
    function index($name){
        $this->initialize($name);
        $res = array();
        $res['description'] = 'Filesystem plugin';
        $res['common-name'] = $name;
        $res['settings'] = $this->settings;
        return json_encode($res, JSON_PRETTY_PRINT);
    }

    /**
     * Search the filesystem
     * @method GET
     * @func search
     * @priority 1
     */
    function search($name, $func, $query = '')
    {
        if(strlen($query) <= 3)
            return '[]';
        $matches = array();
        $query = escapeshellarg($query);
        $command = 'grep ' . $query . '/pub/mp3/index';

        exec($command, $matches);

        $real = function($path) {
            if(is_dir($path))
                return $path;
            return readlink($path);
        };
        $matches = array_map($real, $matches);
        $matches = array_values(array_unique($matches));

        $urlize = function($path) use ($name){
            $base = $this->app->uri(__CLASS__, array($name, 'browse'));
            return $base . DIRECTORY_SEPARATOR . $path;
        };
        $matches = array_map($urlize, $matches);
        $matches = array_map($this->encode_path, $matches);

        return json_encode($matches, JSON_PRETTY_PRINT);
    }

    /**
     * Browse in the filesystem
     * @method GET
     * @func browse
     * @priority 1
     */
    function browse($name, $func, $file = '')
    {
        $this->initialize($name);

        //Put extension at end of filename again
        $type = array_search($this->request->accept[0], $this->request->mimetypes);
        if($type != 'html')
            $file .= '.' . $type;

        $file = urldecode($_SERVER['REQUEST_URI']);
        $file = substr($file, strpos($file, 'browse/') + 6);

        //Check if requested url is within the base path
        $basepath = realpath($this->settings['path']);
        $path = $basepath;
        if($file != '')
            $path .= '/' . $file;
        $path = realpath($path);

        if($path && $basepath && substr_compare($path, $basepath, 0, strlen($basepath)) != 0)
            throw new Tonic\NotFoundException;

        //Handle directories
        if(is_dir($path))
        {
            $entries = array();
            $names = scandir($path);
            foreach($names as $filename)
            {
                if($filename[0] == '.')
                    continue;
                if(!is_readable($path . '/' . $filename))
                    continue;
                $entry = $this->app->uri(__CLASS__, array($name, $func));
                if($file != '')
                    $entry .= '/' . $file;
                $entry .= '/' . $filename;
                if(is_dir($path . '/' . $filename))
                    $entry .= '/';
                $entries[] = $this->encode_path($entry);
            }

            $res = array();
            $res['type'] = 'directory';
            $res['name'] = basename($path);
            if($path == $basepath)
                $res['name'] = $name;
            $res['entries'] = $entries;
            return json_encode($res, JSON_PRETTY_PRINT);
        }

        //Handle mp3 files
        if(is_file($path))
        {
            //Create new songinfo
            $tags = $this->getID3Tags($path);
            $res = new Song($path, $tags['title'], $tags['artist'], $path);
            $res->album = $tags['album'];
            $res->length = $tags['length'];
            return $res->toJSON();
        }

        throw new Tonic\NotFoundException;
    }

    /**
     * Returns ID3 tags of a given filename as an associative array
     */
    function getID3Tags($filename)
    {
        exec("mp3info -p \"%t\n%a\n%l\n%S\n\" " . escapeshellarg($filename), $tags);
        $res = array(
            'title' => $tags[0],
            'artist' => $tags[1],
            'album' => $tags[2],
            'length' => (int)$tags[3],
        );
        return $res;
    }

    /**
     * Fallback method for unknown or unsupported functions
     * @method GET
     * @priority 0
     */
    function notfound($name, $func = '', $file = '')
    {
        throw new Tonic\NotFoundException;
    }

    protected function func($verb = '')
    {
        if ($verb != $this->func) throw new Tonic\ConditionException;
    }

    /**
         * @method OPTIONS
         * Returns acceptible methods
         */
        public function options()
        {
                $response = new Tonic\Response(200, "");
                $response->allow = "GET,HEAD,POST,PUT,PATCH";
        $response->accessControlAllowMethods = "GET,HEAD,POST,PUT,PATCH";
        $response->accessControlAllowHeaders = "Content-Type";
                return $response;
        }
}
