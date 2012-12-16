<?php
ini_set('display_errors', 'On');
$conf['base'] = 'http://music/musicmaster';

$conf['plugins_enabled'] = array('files');
$conf['plugins']['files']['class'] = 'FilePlugin';
$conf['plugins']['files']['path'] = '/pub/music/';

$conf['players_enabled'] = array('madras');
$conf['players']['madras']['class'] = 'MJSPlayer';
$conf['players']['madras']['url'] = 'http://madras.maxmaton.nl:8080';
