<?php
/**
 * @uri /plugin/capabilities/browse
 */
class BrowsePluginCapability extends Tonic\Resource {
    static $name = "browse";
    static $description = "Browse plugin functions";
    static $functions = array(
        'browse/{path}' => array(
            'GET' => array(
                'description' => 'Returns an array of resources at that path',
                'returns' => 'An array of Songs and Folders or a Song'
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
