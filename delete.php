<?php include 'functions.php';

$bad = 0;
foreach ($_GET as $value) {
    if (!is_data_safely($value))
        $bad = 1;
}

if (!$bad) {
    if (isset($_GET['folder'])) {
        $folder = $_GET['folder'];
        if (isset($_GET['ad_file'])) {
            if (preg_match('/^\d{10,11}\.\d{1,3}.\d{1,2}$/', $_GET['ad_file']))
                $ad_file = $_GET['ad_file'];
        } else
            $ad_file = '';
        $done = remove_ad_files($folder, $ad_file);
    }
    if ($done)
        $out = "Deleted!";
    else
        $out = "Something went wrong, or no ads.";
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
<style>
* { font-size: 20px;  font-family: Calibri, Verdana, Arial; }
body { background: #fff; }
</style>
</head>
<body>

<p>
<?= $out ?>
</p>

</body>
</html>