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
        $command = "locate -l 100 -id /pub/mp3/mp3.db " . $query;

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
            return str_replace('/pub/mp3', $base, $path);
        };
        $matches = array_map($urlize, $matches);

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
                $entry = $this->app->uri(__CLASS__, array($name, $func));
                if($file != '')
                    $entry .= '/' . $file;
                $entry .= '/' . $filename;
                if(is_dir($path . '/' . $filename))
                    $entry .= '/';
                $entries[] = $entry;
            }

            $res = array();
            $res['type'] = 'directory';
            $res['name'] = 'unknown';
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
            return $res->toJSON();
        }

        throw new Tonic\NotFoundException;
    }

    /**
     * Returns ID3 tags of a given filename as an associative array
     */
    function getID3Tags($filename)
    {
        exec("mp3info -p \"%t\n%a\n%l\n\" " . escapeshellarg($filename), $tags);
        $res = array(
            'title' => $tags[0],
            'artist' => $tags[1],
            'album' => $tags[2],
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
}
