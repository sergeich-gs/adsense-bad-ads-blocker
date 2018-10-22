<?php     include 'functions.php';
if (is_data_safely($_GET['new_ad']))
    file_put_contents($GLOBALS['settings_folder'] . 'whitelist.txt', "\n" . $_GET['new_ad'], FILE_APPEND);

$meta_continue = $waiting = '';
if (isset($_GET['ad_id'])) {
    if (is_data_safely($_GET['ad_id'])) {
        $meta_continue = '<meta http-equiv="refresh" content="0.8; url=blocker.php?next=acc&type=ad&act=unblock&ad_id=' . $_GET['ad_id'] . '">';
        $waiting = '... <img src="img/waiting.gif" height="20"/>';
    }
}

//—
 ?>
<!DOCTYPE html>
<html>
<head>
<title>Whitelist ad add</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="shortcut icon" href="/favicon.ico" type="image/x-icon" />
<meta name="referrer" content="none" />
<meta name="robots" content="noindex, nofollow" />
<?= $meta_continue ?>
<style>
* { font-size: 16px;  font-family: Calibri, Verdana, Arial; }
body { background: #fff; margin: 0px 8px; }
p { margin: 0px; }
</style>
</head>
<body>

<p>Whitelisted.<br />
Don't save whitelist before <a href="./" target="_top">refreshing the page</a><?= $waiting ?>
</p>

</body>
</html>