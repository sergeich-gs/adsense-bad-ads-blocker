<?php include 'functions.php';

$suffix = basename(__FILE__, '.php');

if (file_exists($GLOBALS['settings_folder'] . "settings.$suffix.ini")) {
    $set = file_get_contents($GLOBALS['settings_folder'] . "settings.$suffix.ini");
    $set = json_decode($set, 1);
} else
    die();

include 'search_bad_ads.php';
