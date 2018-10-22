<?php include 'functions.php';

foreach ($_GET as $value)
    if (!is_data_safely($value))
        die();


if (isset($_GET['ad_id'])) {

    $renewed = false;
    again : if (isset($_GET['next'])) {
        if (is_data_safely($_GET['next']))
            $meta_continue = '<meta http-equiv="refresh" content="0.5; url=blocker.php?type=' . $_GET['next'] . '&act=unblock&ad_id=' . $_GET['ad_id'] . '">';
    } else
        $meta_continue = '';


    $from = '';
    $id_len = strlen($_GET['ad_id']);
    if ($GLOBALS['set_gl']['arc'] == 'adx') {
        $from = 'from AdX';
        $result_keyword = 'result';
    } else
        if ($id_len == 120 || $id_len == 56) {
            $from = 'from old ARC';
            $result_keyword = 'result';

        } else
            if ($id_len == 104 || $id_len == 108 || $id_len == 36 || $id_len == 40) {
                $from = 'from new ARC';
                $result_keyword = 'default';
            }

    $unblock = 0;
    if (isset($_GET['act']))
        if ($_GET['act'] == 'unblock') {
            $unblock = 1;
            $_GET['header'] = $_GET['adv_id'] = $_GET['adv_name'] = '';
        }


    if (isset($_GET['digikey']))
        $digikey = $_GET['digikey'];
    else
        $digikey = '';

    if (isset($_GET['type'])) {

        if ($_GET['type'] == 'ad') {
            $result = block_ad($_GET['ad_id'], $digikey, $unblock);
            $type = 'Ad';
        }

        if ($_GET['type'] == 'acc') {
            $result = block_ad_account($_GET['ad_id'], $unblock, $_GET['header'], $_GET['adv_id'], $_GET['adv_name']);
            $type = 'Account';
        }

        if ($_GET['type'] == 'adwords_acc') {
            if ($unblock) {
                $result = unblock_adwords_account($_GET['ad_id']);
            } else {
                $result = block_adwords_account($_GET['ad_id']);
            }
            $type = 'Account';
        }
    }

    if (!$renewed) {
        if (@is_object($result->error))
            if ($result->error->code == '-32000' || $result->error->code == '-32001') { // XSRF token validation
                get_xsrf_token();
                $renewed = true;
                goto again;
            }
        if (@$result == '-32000 XSRF token validation') {
            get_xsrf_token_new();
            $renewed = true;
            goto again;
        }
    }

    if (@is_object($result->error))
        die('<p>' . $result->error->code . ' ' . $result->error->message . '</p>');

    @$result = $result->{$result_keyword}->{1};
    @$result = $result[0]->{2};

    if ($result)
        $result = 'blocked';
    else
        $result = 'unblocked';

    $out = $type . ' ' . $from . ' ' . $result;
}
//—
 ?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="referrer" content="same-origin" />
<meta name="robots" content="noindex, nofollow" />
<link href="img/favicon.ico" rel="shortcut icon" type="image/x-icon" />
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=4, minimum-scale=0.1" />
<?= $meta_continue ?>
<style>
* { font-size: 17px;  font-family: Calibri, Verdana, Arial; }
body { background: #fff; }
</style>
</head>
<body>

<?= $out ?>

</body>
</html>