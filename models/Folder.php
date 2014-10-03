<?php
class Folder {
    public $path;
    // An array of entries in this folder
    // This should be relative links from $path
    // Folders should have a trailing /
    public $entries;

    public function getJSON()
    {
        $res["type"] = "folder";
        $res["entries"] = $this->entries;
        return json_encode($res);
    }
}
