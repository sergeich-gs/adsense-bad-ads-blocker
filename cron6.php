<?php include 'functions.php';

$suffix = basename(__FILE__, '.php');

if (file_exists($GLOBALS['settings_folder'] . "settings.$suffix.ini")) {
    $set = file_get_contents($GLOBALS['settings_folder'] . "settings.$suffix.ini");
    $set = json_decode($set, 1);

    if (isset($set['redirects_text']) || isset($set['redirects_media']))
        $GLOBALS['level3domains'] = file($GLOBALS['settings_folder'] . 'level3domains.txt', FILE_IGNORE_NEW_LINES);

    if(isset($set['whitelist']))
        if ($set['whitelist'])
            $GLOBALS['whitelist'] = file($GLOBALS['settings_folder'] . 'whitelist.txt', FILE_IGNORE_NEW_LINES);
} else
    die();

include 'search_bad_ads.php';
