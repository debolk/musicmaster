<?php
/**
 * @uri /player/mjs/{name}
 */
class MJSPlayer extends Tonic\Resource {
    static $capabilities = array('mjs');

    function enabled()
    {
        return;
        if(!in_array($this->name, $this->conf['players_enabled']))
            throw new Tonic\ConditionException;
        if($this->conf['player'][$this->name]['class'] != 'MJSPlayer')
            throw new Tonic\ConditionException;
    }

    /**
     * @method GET
     * @uri /players/mjs/{name}
     * @enabled
     */
    function get($name)
    {
        return $name;
    }
}
