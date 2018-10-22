<?php //—
include 'functions.php';

foreach ($_GET as $value)
    if (!is_data_safely($value))
        die();

foreach ($_POST as $value)
    if (!is_data_safely($value))
        die();

$renewed = false;
again : if (isset($_GET['url'])) {

    $result = block_unblock_url($_GET['url'], $_GET['act']);

    if (!$renewed) {
        if (@is_object($result->error))
            if ($result->error->code == '-32000' || $result->error->code == '-32001') { // XSRF token validation
                get_xsrf_token();
                $renewed = true;
                goto again;
            }
    }

    if (@is_object($result->error))
        die('<p>' . $result->error->code . ' ' . $result->error->message . '</p>');

    @$result = $result->result->{1};
    @$result = $result[0]->{4};

    if ($result)
        $result = ' blocked';
    else
        $result = ' unblocked';

    $out = $_GET['url'] . $result;
}

if (isset($_POST['urls'])) {
    if (!$_POST['urls'])
        die();

    $result = add_blocked_url($_POST['urls']);

    if (!$renewed) {
        if (@is_object($result->error))
            if ($result->error->code == '-32000' || $result->error->code == '-32001') { // XSRF token validation
                get_xsrf_token();
                $renewed = true;
                goto again;
            }
    }

    if (@is_object($result->error))
        die('<p>' . $result->error->code . ' ' . $result->error->message . '</p>');

    foreach ($GLOBALS['blocked_urls'] as $blocked_url) {
        $out .= $blocked_url . "<br />\n";
    }


    $out .= "<br />It should be blocked, please don't worry. ;)";
}

if (isset($_GET['url_to_add'])) {
    if (!$_GET['url_to_add'])
        die();

    $result = add_blocked_url($_GET['url_to_add']);

    if (!$renewed) {
        if (@is_object($result->error))
            if ($result->error->code == '-32000' || $result->error->code == '-32001') { // XSRF token validation
                get_xsrf_token();
                $renewed = true;
                goto again;
            }
    }

    if (@is_object($result->error))
        die('<p>' . $result->error->code . ' ' . $result->error->message . '</p>');

    @$result = $result->result->{1};
    @$result = $result[0]->{4};

    if ($result)
        $result = ' blocked';
    else
        $result = ' unblocked';

    $out = $_GET['url_to_add'] . $result;

    if (isset($_GET['add2list'])) {
        if (!$_GET['add2list'])
            die();

        file_put_contents('ad_stats_excl_dom.txt', $_GET['url_to_add'] . "\n", FILE_APPEND);
        $out .= '. Included to list.';

    }
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
* { font-size: 17px;  font-family: Calibri, Verdana, Arial; }
body { background: #fff; }
</style>
</head>
<body>

<?= $out ?>

</body>
</html>


