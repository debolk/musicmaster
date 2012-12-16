<?php
/**
 * @uri /plugin/capabilities/search
 */
class SearchPluginCapability extends Tonic\Resource {
    static $name = "search";
    static $description = "Search plugin functions";
    static $functions = array(
        'search' => array(
            'POST' => array(
                'description' => 'Returns an array of resources based on a query',
                'postdata' => 'A string containing the search query',
                'returns' => 'An array of Songs and Folders',
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
