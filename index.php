<?php

// load Tonic
require_once 'lib/tonic/src/Tonic/Autoloader.php';

$conf = array();

// Scans a folder for capabilities and adds them to the config file
function addCapabilities($folder, &$conf)
{
    $plugins = scandir($folder . '/');
    foreach($plugins as $plugin)
    {
        if($plugin[0] == '.')
            continue;

        if(!is_dir($folder . '/' . $plugin . '/capabilities/'))
            continue;

        $capabilities = scandir($folder . '/' . $plugin . '/capabilities/');
        foreach($capabilities as $capability)
        {
            if($capability[0] == '.')
                continue;

            require_once($folder . '/' . $plugin . '/capabilities/' . $capability);

            $class = explode('.',$capability)[0];
            $name = $class::$name;
            $conf[$folder]['capabilities'][$name] = $class;
        }
    }
}

addCapabilities('plugins', $conf);
addCapabilities('players', $conf);

// Load user settings
@require_once('settings.php');


$config = array(
    'load' => array(
        'controllers/*.php',
        'models/*.php',
        'players/*/player.php',
        'players/*/capabilities/*.php',
        'plugins/*/plugin.php',
        'plugins/*/capabilities/*.php',
        'helpers/*.php',
    ),
    #'mount' => array('Tyrell' => '/musicmaster/index'), // mount in example resources at URL /nexus
    #'cache' => new Tonic\MetadataCacheFile('/tmp/tonic.cache') // use the metadata cache
    #'cache' => new Tonic\MetadataCacheAPC // use the metadata cache
    #'baseUri' => '/musicmaster/index.php/'
);

if(isset($conf['base']))
    $config['baseUri'] = $conf['base'];

$app = new Tonic\Application($config);

#echo $app; die;

$request = new Tonic\Request();

#echo $request; die;

try {

    $resource = $app->getResource($request);
    $resource->conf = $conf;

    #echo $resource; die;

    $response = $resource->exec();

}
catch (Tonic\UnauthorizedException $e) {
    $response = new Tonic\Response(401, $e->getMessage());
    $response->wwwAuthenticate = 'Basic realm="My Realm"';

} catch (Tonic\Exception $e) {
    $error = json_encode([
        'error' => $e->getMessage(),
        'exception' => (string)$e,
    ]);
    $response = new Tonic\Response($e->getCode(), $error);
}

#echo $response;
$response->contentType = 'application/json';
$response->AccessControlAllowOrigin = '*';

$response->output();
