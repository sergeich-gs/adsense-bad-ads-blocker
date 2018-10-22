<?php function is_data_safely($input_data) {
    $ret = true;
    if (strpos($input_data, "<?") !== false)
        $ret = false;
    if (strpos($input_data, "?>") !== false)
        $ret = false;
    if (strpos($input_data, "strtolower") !== false)
        $ret = false;
    if (strpos($input_data, "strtoupper") !== false)
        $ret = false;
    if (strpos($input_data, "{") !== false)
        $ret = false;
    if (strpos($input_data, "}") !== false)
        $ret = false;
    if (strpos($input_data, "$") !== false)
        $ret = false;
    if (strpos($input_data, "strip") !== false)
        $ret = false;
    if (strpos($input_data, "decode") !== false)
        $ret = false;
    if (strpos($input_data, "eval") !== false)
        $ret = false;
    if (strpos($input_data, "chr") !== false)
        $ret = false;
    return $ret;
}


if (isset($_POST['pass']))
    if (is_data_safely($_POST['pass'])) {

        if (md5($_POST['pass']) == @file_get_contents(__DIR__ . '/tempdata/pass')) {

            if (file_exists(__DIR__ . '/tempdata/auth_key')) {
                //if(isset($_COOKIE['key'])) {
                $key = file_get_contents(__DIR__ . '/tempdata/auth_key');
                //$key=$_COOKIE['key'];
            } else {
                $key = crypt(time());
                file_put_contents(__DIR__ . '/tempdata/auth_key', $key);
            }
            $time = time() + 24 * 3600 * 700;
            $keyname = substr(md5(dirname(__file__)), 0, 5) . '_key';
            SetCookie($keyname, $key, $time, "/", $_SERVER['HTTP_HOST']);
            header("Location: " . dirname($_SERVER['REQUEST_URI']));
        }
    }

//—
 ?>


<!DOCTYPE html>
<html>
<head>
<title>Bad ad Blocker Auth</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="referrer" content="same-origin" />
<meta name="robots" content="noindex, nofollow" />
<link href="img/favicon.ico" rel="shortcut icon" type="image/x-icon" />
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=4, minimum-scale=0.1" />
<style>
<?php include 'style.css'; ?>
* {font-size: 120%;}
</style>


</head>
<body>

<form method="post">

Your password:<br />
<input type="password" value="" name="pass"/>

<br /><br />

<input type="submit" value="Go!" />

</form>


</body>
</html>
