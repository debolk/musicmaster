<?php
/**
 * @uri /player/mjs/{name}
 * @uri /player/mjs/{name}/{func}
 * @uri /player/mjs/{name}/{func}/{item}
 */
class MJSPlayer extends Tonic\Resource {
    static $capabilities = array('mjs');

    /**
     * Don't show functions if the player is not enabled
     */
    function init()
    {
        if(!in_array($this->name, $this->conf['players_enabled']))
            throw new Tonic\ConditionException;

        $this->settings = $this->conf['players'][$this->name];
        if($this->settings['class'] != 'MJSPlayer')
            throw new Tonic\ConditionException;
    }

    /**
	 * @method OPTIONS
	 * Returns acceptible methods
	 */
	public function options()
	{
		$response = new Tonic\Response(200, "");
		$response->Allow = "GET,HEAD,POST,PUT,PATCH";
        $response->AccessControlAllowMethods = "GET,HEAD,POST,PUT,PATCH";
        $response->AccessControlAllowHeaders = "Content-Type";
		return $response;
	}

    /**
     * Show basic info about player
     * @method GET
     * @init
     * @func
     * @priority 1
     */
    function get($name)
    {
        $res = array();
        $res['name'] = $name;
        $res['capabilities'] = array();

        foreach(self::$capabilities as $capability)
        {
            $c = array();
            $c['name'] = $capability;
            $c['url'] = $this->app->uri($this->conf['players']['capabilities'][$capability]);
            $res['capabilities'][] = $c;
        }

        return json_encode($res);
    }

    /**
     * Return the playback status of the player
     * @method GET
     * @init
     * @func status
     * @priority 1
     */
    function getStatus($name, $func)
    {
        $json = json_decode($this->request('status'));
        return json_encode($json);
    }

    /**
     * Put the playback status of the player
     * @method PUT
     * @init
     * @func status
     * @priority 1
     */
    function putStatus($name, $func)
    {
	$data = json_decode($this->request->data);

	$accept = array('playing', 'stopped', 'paused');
        if(!in_array($data->status, $accept))
            throw new Tonic\ConditionException;

        $request = json_encode(array('status' => $data->status));

	print_r($this->request('status', 'POST', $request));

        return $this->getStatus($name, $func);
    }

    /**
     * Returns a description of the currently playing song
     * @method GET
     * @init
     * @func current
     * @priority 1
     */
    function getCurrent($name, $func)
    {
        $data = json_decode($this->request('current'));

        if(!isset($data->file))
            return '{}';

        $res = array();
        $res['url'] = $this->app->uri('MJSPlayer', array($name, 'playlist', $data->file->uid));
        if($data->file->tag != '')
            $res['song'] = $data->file->tag;
        $res['duration'] = $data->duration;
        $res['position'] = $data->position;

        return json_encode($res);
    }

    /**
     * Move relative to current playlist item
     * @method POST
     * @init
     * @func current
     * @priority 1
     */
    function postCurrent($name, $func)
    {
        $data = json_decode($this->request->data);

        $accept = array('next', 'previous');
        if(!in_array($data->action, $accept))
            throw new Tonic\ConditionException;

        $request = json_encode(array('status' => $data->action));

        $this->request('current', 'POST', $request);
        sleep(0.1); //HACK: to prevent segfault in MJS

        return getCurrent($name, $func);
    }

    /**
     * Put current item
     * @method PUT
     * @init
     * @func current
     * @priority 1
     */
    function putCurrent($name, $func)
    {
        $json = json_decode($this->request->data);
        $uid = explode('/', $json->uri);
        $uid = $uid[count($uid) - 1];

        if($this->request->data != $this->app->uri('MJSPlayer', array($name, 'playlist', $uid)))
            throw new Tonic\ConditionException;

        $data = json_encode(array('uid' => $uid));
        $this->request('current', 'POST', $data);

        return '';
    }

    /**
     * Get the playlist
     * @method GET
     * @init
     * @func playlist
     * @item
     * @priority 2
     */
    function getPlaylist($name, $func)
    {
        $data = json_decode($this->request('playlist'));
        if(!isset($data->files))
            throw new Tonic\Exception;

        $res = array();
        $res['items'] = array();
        foreach($data->files as $file)
        {
            $item = array();
            $item['url'] = $this->app->uri('MJSPlayer', array($name, 'playlist', $file->uid));
            if($file->tag != '')
                $item['song'] = $file->tag;
            $item['location'] = $file->location;
            $res['items'][] = $item;
        }

        return json_encode($res, JSON_PRETTY_PRINT);
    }

    /**
     * Clear the playlist
     * @method DELETE
     * @init
     * @func playlist
     * @item
     * @priority 2
     */
    function deletePlaylist($name, $func)
    {
        $this->request('playlist', 'DELETE');
        return '';
    }

    /**
     * Append a song
     * @method POST
     * @init
     * @func playlist
     * @item
     * @priority 2
     */
    function postPlaylist($name, $func)
    {
        $json = json_decode($this->request->data);
        $url = $json->uri;
        $song = $this->getObject($url);

        if($song->type != 'song')
            throw new Tonic\ConditionException;

        $data = array(
            'location' => $song->location,
            'tag' => $url,
        );
        $this->request('playlist', 'POST', json_encode($data));

        return '';
    }

    /**
     * Get playlist item
     * @method GET
     * @init
     * @func playlist
     * @priority 1
     */
    function getPlaylistItem($name, $func, $item)
    {
        $data = json_decode($this->request('playlist'));
        if(!isset($data->files))
            throw new Tonic\Exception;

        $listitem = null;
        foreach($data->files as $file)
            if($file->uid == $item)
                $listitem = $file;

        if($listitem == null)
            throw new Tonic\NotFoundException;

        $res = array();
        $res['location'] = $listitem->location;
        if($listitem->tag != '')
            $res['song'] = $listitem->tag;

        return json_encode($res);
    }

    /**
     * Remove song from playlist
     * @method DELETE
     * @init
     * @func playlist
     * @priority 1
     */
    function deletePlaylistItem($name, $func, $item)
    {
        //Prevent deletion of other urls
        $item = str_replace('/', '', $item);
        $this->request('playlist/' . $item, 'DELETE');

        return '';
    }

    /**
     * Inserts a song before a given item
     * @method POST
     * @init
     * @func playlist
     * @priority 1
     */
    function postPlaylistItem($name, $func, $item)
    {
        $item = str_replace('/', '', $item);

        $url = $this->request->data;
        $song = $this->getObject($url);

        if($song->type != 'song')
            throw new Tonic\ConditionException;

        $data = array(
            'location' => $song->location,
            'tag' => $url,
        );
        $this->request('playlist/' . $item, 'POST', json_encode($data));

        return '';
    }

    /**
     * Do a http request
     */
    function request($url, $method = 'GET', $data = '')
    {
	$curl = curl_init($this->settings['url'] . $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        if($data != '')
        {
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
            curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Length: ' . strlen($data)));
        }
	$result = curl_exec($curl);
	if(!$result)
		return curl_error($curl);

	return $result;
    }

    /**
     * Retrieves a json object
     */
    function getObject($url)
    {
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        return json_decode(curl_exec($curl));
    }

    /**
     * @method GET
     * @priority 0
     */
    function notFound($name, $func = '', $item = '')
    {
        throw new Tonic\NotFoundException;
    }

    /**
     * Only run when func matches a given value
     */
    protected function func($verb = '')
    {
        if ($verb != $this->func) throw new Tonic\ConditionException;
    }

    /**
     * Only run when item matches a give value
     */
    protected function item($value = '')
    {
        if($value != $this->item) throw new Tonic\ConditionException;
    }
}
