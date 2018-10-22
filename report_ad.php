<?php include 'functions.php';

foreach ($_GET as $value)
    if (!is_data_safely($value))
        die();

$out = 'Something went wrong...';

if (isset($_GET['ad_id'])) {

    $id_len = mb_strlen($_GET['ad_id'], 'UTF-8');

    if ($id_len == 104 || $id_len == 108) {
        $result = ReportPolicyViolation($_GET['ad_id']);
        if (@is_object($result->error))
            die('<p>' . $result->error->code . ' ' . $result->error->message . '</p>');
        else
            $out = 'Reported.';
    }
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
* { font-size: 17px;  font-family: Calibri, Verdana, Arial; }
body { background: #fff; }
</style>
</head>
<body>

<?= $out ?>

</body>
</html>