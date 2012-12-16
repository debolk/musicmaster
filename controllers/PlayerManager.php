<?php
/**
 * @uri /player
 */
class PlayerManager extends Tonic\Resource {
    /**
     * @method GET
     * @provides application/json
     */
    function index()
    {
        $players = array();
        foreach($this->conf['players_enabled'] as $player)
        {
            $class = $this->conf['players'][$player]['class'];

            $urls = array();
            foreach($class::$capabilities as $capability)
                $urls[] = array(
                    'name' => $capability,
                    'url' => $this->app->uri($this->conf['players']['capabilities'][$capability]),
                );

            $players[] = array(
                'name' => $player,
                'url' => $this->app->uri($class, array($player)),
                'capabilities' => $urls,
            );
        }

        $res['description'] = "Currently registered players, applications should ignore players that don't have a supported capability.";
        $res['players'] = $players;

        return json_encode($res, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    }
}
