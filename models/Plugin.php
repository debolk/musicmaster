<?php
abstract class Plugin {
    public $name;

    // Initializes the plugin with the user configuration
    public abstract function init($conf);

    // Should return either null (not found), a Folder or a Song
    public abstract function getEntry($path);

    // Should return an array of Folders and Songs
    public abstract function search($query);
}
