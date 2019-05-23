<?php //—
include 'functions.php';

$bad = 0;
foreach ($_POST as $index => $value) {
    if (!is_data_safely($value))
        $bad = 1;
    if (!$value)
        unset($_POST[$index]);
}

if (!$bad) {
    
if(isset($_POST['username'])) {
    if(trim($_POST['username'])) {
        $username = trim($_POST['username']);
        unset($_POST['username']);
    }    
}  
  
if(!isset($username)) {
    $username = exec('whoami');
}
  
    unset($_POST['num_of_cycles'], $_POST['log'], $_POST['login'], $_POST['run_interval'], $_POST['badadlist_show'], $_POST['whitelist_show'], $_POST['searchwords_show'], $_POST['display_ad_url']);
    $settings = json_encode($_POST);


$script_folder = __DIR__;

$cron_settings = <<<CRON_SET


2,4,8,13,53,58  *  *  *  * $username /usr/bin/php $script_folder/cron1.php
<br /><br />
1,3,6,12,52,59  *  *  *  * $username /usr/bin/php $script_folder/cron2.php
<br /><br />
12,20,25,35,40,51  *  *  *  * $username /usr/bin/php $script_folder/cron3.php
<br /><br />
7,11,22,33,39,49  *  *  *  * $username /usr/bin/php $script_folder/cron4.php
<br /><br />
2,17,27,38,47,54  *  *  *  * $username /usr/bin/php $script_folder/cron5.php
<br /><br />
15,30,45  *  *  *  * $username /usr/bin/php $script_folder/cron6.php


CRON_SET;

} ?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="referrer" content="same-origin" />
<meta name="robots" content="noindex, nofollow" />
<link href="img/favicon.ico" rel="shortcut icon" type="image/x-icon" />
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=4, minimum-scale=0.1" />

<style>
* { font-size: 16px;  font-family: Calibri, Verdana, Arial; }
body { background: #fff; }
</style>
</head>
<body>

<?= $cron_settings ?>


<br /><br />
<p>Just put this strings to your crontab file.</p>


</body>
</html>
