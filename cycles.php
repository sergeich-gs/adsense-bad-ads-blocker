<?php include 'functions.php';

foreach ($_GET as $value)
    if (!is_data_safely($value))
        die();


if (isset($_GET['start'])) {
    $start_pos = (int)$_GET['start'];
} else
    $start_pos = 0;

if (isset($_GET['searcher'])) {
    $file_fore_search = $_GET['searcher'];
    $nextfile = 'searcher=' . $_GET['searcher'] . '&';
} else {
    $file_fore_search = 'search_bad_ads.php';
    $nextfile = '';
}


$ads_per_cycle = $set['num_of_pages'] * $set['num_of_ads_per_page'];
$count = $start_pos / $ads_per_cycle;
$count++;
$cycle_report = '<p>Cycles done: ' . $count . ' of ' . $set['num_of_cycles'] . '</p>';

if ($count < $set['num_of_cycles']) {

    $next_start_pos = $ads_per_cycle + $start_pos;

    $continue_url = 'cycles.php?' . $nextfile . 'start=' . $next_start_pos;

    $meta_continue = '<meta http-equiv="refresh" content="0; url=' . $continue_url . '">';

    //$a_continue='<p><a href="'.$continue_url.'">Click here to continue</a></p>';

    $pls_wait = '<br />Please wait... <img src="img/waiting.gif" height="20"/>';
} else
    $pls_wait = '';

$cycle_report = '<p>Cycles done: ' . $count . ' of ' . $set['num_of_cycles'] . $pls_wait . ' or <a href="./" target="_parent">refresh the page to cancel</a>.</p>';


include $file_fore_search; ?>