<?php
class Song {
    // The path to this song relative to the plugin
    public $path;
    public $title;
    public $artist;

    public function __construct($path, $title, $artist)
    {
        $this->path = $path;
        $this->title = $title;
        $this->artist = $artist;
    }

    public function toJSON()
    {
        $res["type"] = "song";
        $res["title"] = $this->title;
        $res["artist"] = $this->artist;
        return json_encode($res);
    }
}
