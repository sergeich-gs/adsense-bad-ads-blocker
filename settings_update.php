<?php

include 'functions.php';

foreach ($_POST as $value)
    if (!is_data_safely($value))
        die();

if(isset($_POST['username']))
    unset($_POST['username']);

if(isset($_POST['cron_settings'])) {

    $suffix = (int) $_POST['cron_settings'];

    if($suffix > 1) {
        if(!file_exists(__DIR__ . "/cron$suffix.php")) {
            copy (__DIR__ . "/cron1.php", __DIR__ . "/cron$suffix.php");
        }
    }

    $suffix = '.cron' . $suffix;
    unset($_POST['cron_settings']);

    if(isset($_POST['set_name']))
        $_POST['set_name'] = base64_encode($_POST['set_name']);
} else
    $suffix = '';

$settings = json_encode($_POST);
file_put_contents($GLOBALS['settings_folder'] . "settings$suffix.ini", $settings);

header("Location: " . $_SERVER['HTTP_REFERER']);
//—
