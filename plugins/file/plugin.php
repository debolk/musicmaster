<?php
/**
 * @uri /plugin/file/{name}
 */
Class FilePlugin extends Tonic\Resource {
    static $capabilities = array('browse', 'search');
}
