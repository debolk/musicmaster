<?php
class Song {
    // The path to this song relative to the plugin
    public $path;
    public $title;
    public $artist;
    // Url to song
    public $location;

    public function __construct($path, $title, $artist, $location)
    {
        $this->path = $path;
        $this->title = $title;
        $this->artist = $artist;
        $this->location = $location;
    }

    public function toJSON()
    {
        $res["type"] = "song";
        $res["title"] = $this->title;
        $res["artist"] = $this->artist;
        $res["location"] = $this->location;
        return json_encode($res);
    }
}
