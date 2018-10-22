<?php include 'functions.php';

$url = 'https://www.google.com/adsense/new/u/0/' . $GLOBALS['pub_id'] . '/home';
$result = curl_get($url, '', '');
$result = hex_repl($result);

if (isset($set['log']))
    file_put_contents($GLOBALS['temp_folder'] . 'logs/earn.' . time(), $result);

$result = get_paid_stats($result);


$result = $result->{2}->{1};
$result = $result[0]->{2};
$result = $result[0]->{3};
$result = $result->{1}->{1};

$currency = $result[0]->{2};
$currency = $currency[0]->{2};

if ($currency == 'USD')
    $currency = '&#36;';
if ($currency == 'EUR')
    $currency = '&#8364;';
if ($currency == 'RUR')
    $currency = '&#8381;';

$today_earnings = $result[0]->{4};
$today_earnings = $today_earnings[0]->{3};
$today_earnings = $today_earnings[0]->{1};
$today_earnings = $today_earnings[0]->{2};
$today_earnings = round($today_earnings, 2);

$yesterday_earnings = $result[1]->{4};
$yesterday_earnings = $yesterday_earnings[0]->{3};
$yesterday_earnings = $yesterday_earnings[0]->{1};
$yesterday_earnings = $yesterday_earnings[0]->{2};
$yesterday_earnings = round($yesterday_earnings, 2);

$last7days = $result[2]->{4};
$last7days = $last7days[0]->{3};
$last7days = $last7days[0]->{1};
$last7days = $last7days[0]->{2};
$last7days = round($last7days, 2);

$last28days = $result[4]->{4};
$last28days = $last28days[0]->{3};
$last28days = $last28days[0]->{1};
$last28days = $last28days[0]->{2};
$last28days = round($last28days, 2);

$thismonth = $result[3]->{4};
$thismonth = $thismonth[0]->{3};
$thismonth = $thismonth[0]->{1};
$thismonth = $thismonth[0]->{2};
$thismonth = round($thismonth, 2); ?><!DOCTYPE html>
<html>
<head>
<title>Earnings</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="referrer" content="same-origin" />
<meta name="robots" content="noindex, nofollow" />
<link href="img/favicon.ico" rel="shortcut icon" type="image/x-icon" />
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=4, minimum-scale=0.1" />
<link rel="stylesheet" type="text/css" href="style.css"/>
<style>
* { font-size: 20px;  font-family: Calibri, Verdana, Arial; }
body { background: #fff; }
</style>
</head>
<body>

<p class="menu">Today so far: <?= $currency . $today_earnings ?>.</p>
<p class="menu">Yesterday: <?= $currency . $yesterday_earnings ?>.</p>
<p class="menu">Last 7 days: <?= $currency . $last7days ?>.</p>
<p class="menu">Last 28 days: <?= $currency . $last28days ?>.</p>
<p class="menu">This month: <?= $currency . $thismonth ?>.</p>


</body>
</html>