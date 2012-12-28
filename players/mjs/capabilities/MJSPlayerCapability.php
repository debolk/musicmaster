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
        'status' => array(
            'GET' => array(
                'description' => 'Gets the playback state of the player (playing, paused, stopped)',
            ),
            'PUT' => array(
                'description' => 'Puts the playback state of the player (playing, paused, stopped)',
                'data' => 'playing / paused / stopped',
            ),
        ),
        'current' => array(
            'GET' => array(
                'description' => 'Returns currently playing song',
            ),
            'POST' => array(
                'description' => 'Skip to previous / next song',
                'data' => 'next / previous',
            ),
            'PUT' => array(
                'description' => 'Skip to given playlist item',
                'data' => 'playlist item uri',
            ),
        ),
        'playlist' => array(
            'GET' => array(
                'description' => 'Returns an array of playlist items',
                'returns' => 'Array containing Song objects',
            ),
            'POST' => array(
                'description' => 'Append a song to the playlist',
                'data' => 'Song uri',
            ),
            'DELETE' => array(
                'description' => 'Clear playlist'
            ),
        ),
        'playlist/<item>' => array(
            'GET' => array(
                'description' => 'Return a description of the given playlist item',
            ),
            'POST' => array(
                'description' => 'Append a song to the playlist before this item',
                'data' => 'Song uri',
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
        return json_encode($ret, JSON_PRETTY_PRINT);
    }
} 
