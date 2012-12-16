<?php
// Register class
$conf['player']['capabilities']['mjs'] = 'MJSPlayerCapability';

/**
 * @uri /player/capabilities/mjs
 */
class MJSPlayerCapability extends Tonic\Resource {
    static $name = "mjs";
    static $description = "Default MJS functions";
    static $functions = array(
        'next' => array(
            'POST' => array(
                'description' => 'Advances player to next song if available',
            ),
        ),
        'previous' => array(
            'POST' => array(
                'description' => 'Skips back one song if available',
            ),
        ),
        'playlist' => array(
            'GET' => array(
                'description' => 'Returns an array of songs currently in the playlist',
                'returns' => 'Array containing Song objects',
            ),
            'POST' => array(
                'description' => 'Append a song to the playlist',
                'postdata' => 'Song object',
            ),
        ),
    );

    /**
     * @method GET
     */
    function getIndex()
    {
        $ret['description'] = self::$description;
        $ret['functions'] = self::$functions;
        return json_encode($ret, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    }
} 
