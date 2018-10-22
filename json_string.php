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
    unset($_POST['num_of_cycles'], $_POST['log'], $_POST['login'], $_POST['run_interval'], $_POST['badadlist_show'], $_POST['whitelist_show'], $_POST['searchwords_show'], $_POST['display_ad_url']);
    $settings = json_encode($_POST);
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

<nobr>'!!!<?= $settings ?>'</nobr>
<br /><br />
<p>Just put this string after script name in crontab file to override all settings.</p>
<p>To override only listed in the string settings remove «!!!».</p>
<p>Or use up to three space separated parameters:<br />reviewed num_of_ads_per_page num_of_pages.<br />
for example:<br />
search_bad_ads.php <span title="Enable «Check reviewed ads»" >1</span> <span title="Override «Ads per page»" >100</span> <span title="Override «Number of pages»" >3</span>.
</p>



</body>
</html>
