<?php
class Song {
    public $title;
    public $artist;
    // Url to song
    public $location;

    public function __construct($title, $artist, $location)
    {
        $this->title = $title;
        $this->artist = $artist;
        $this->location = $location;
    }

    public function toJSON()
    {
        return json_encode($this);
    }
}
