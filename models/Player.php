<?php
abstract class Player {
    public abstract function isPlaying();
    public abstract function isPaused();

    public abstract function next();
    public abstract function previous();
    // Set the current song to a given uid
    public abstract function setCurrent($uid);

    // Append a given QueuedSong to the playlist
    public abstract function append($song);
    // Append a given QueuedSong before another QueuedSong
    public abstract function insert($song, $before);

    // Returns a QueuedSong
    public abstract function getCurrent();
    // Returns an array of QueuedSongs
    public abstract function getPlaylist();
}
