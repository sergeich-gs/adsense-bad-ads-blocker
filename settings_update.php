<?php 

include 'functions.php';

foreach ($_POST as $value)
    if (!is_data_safely($value))
        die();

$settings = json_encode($_POST);
file_put_contents($GLOBALS['settings_folder'] . 'settings.ini', $settings);

header("Location: " . $_SERVER['HTTP_REFERER']);
//—
