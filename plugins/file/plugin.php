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
            throw new Exception;
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
        return json_encode($res, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
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
        $path = realpath($this->settings['path']) . '/' . $file;
        $real = realpath($path);

        if($path != $real)
            return "$path,$real";
            //throw new Tonic\NotFoundException;

        if(is_dir($real))
        {
            $entries = array();
            $names = scandir($real);
            foreach($names as $path)
            {
                if($path[0] == '.')
                    continue;
                $entry = $this->app->uri(__CLASS__, array($name, $func, $file . '/' . $path));
                if(is_dir($real . '/' . $path))
                    $entry .= '/';
                $entries[] = $entry;
            }
            return json_encode($entries, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        }
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
