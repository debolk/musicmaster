<?php
/**
 * @uri /plugin
 */
class PluginManager extends Tonic\Resource {
    /**
     * @method GET
     * @provides application/json
     */
    function index()
    {
        $plugins = array();
        foreach($this->conf['plugins_enabled'] as $plugin)
        {
            $class = $this->conf['plugins'][$plugin]['class'];

            $urls = array();
            foreach($class::$capabilities as $capability)
                $urls[] = array(
                    'name' => $capability,
                    'url' => $this->app->uri($this->conf['plugins']['capabilities'][$capability]),
                );

            $plugins[] = array(
                'name' => $plugin,
                'url' => $this->app->uri($class, array($plugin)),
                'capabilities' => $urls,
            );
        }

        $res['description'] = "Currently registered plugins, applications should ignore plugins that don't have a supported capability.";
        $res['plugins'] = $plugins;

        return json_encode($res, JSON_PRETTY_PRINT);
    }
}
