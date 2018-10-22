<?php //—
$start_time = time();
include_once 'functions.php';

if (!is_still_log_in()) {
    die('<p>You should log in to Google first</p>');
}


$first_result = curl_get($GLOBALS['arc_tab_req_string'], '', '');

if (isset($set['log']))
    file_put_contents($GLOBALS['temp_folder'] . 'logs/d1' . time(), $first_result);

unset($first_result);


/**
 * Get first access tokens:
 **/

get_xsrf_token();

$list = get_blocked_urls_list();

foreach ($list->result->{1} as $url_obj)
    $urls[] = $url_obj->{3}->{1};

sort($urls);

$out = '';
$i = 0;

if (@$_POST['confirmation'] == 'agree') {

    foreach ($urls as $url) {

        $result = block_unblock_url($url, 'unblock');
        if (is_object($result->error))
            die('<p>' . $result->error->code . ' ' . $result->error->message . '</p>');

        @$result = $result->result->{1};
        @$result = $result[0]->{4};
        if ($result == 0) {
            $result = ' unblocked';
            $out .= $url . $result . "<br><br>\n";
        }
        if ($i >= 100)
            break;

        $i++;
    }

} else {

    $files_with_timestamp = scandir($GLOBALS['temp_folder'] . 'autoblocked_urls/');
    unset($files_with_timestamp[0], $files_with_timestamp[1]);      //removes «.» and «..»

    foreach ($files_with_timestamp as $file_with_timestamp) {
        $blocked_time = file_get_contents($GLOBALS['temp_folder'] . 'autoblocked_urls/' . $file_with_timestamp);
        $blocked_time = date("j.m.Y G:i:s", $blocked_time);
        $blocked_urls[$file_with_timestamp] = $blocked_time;
    }

    foreach ($urls as $url) {

        $md5_url = md5($url);
        $blocked_time = ' <span title="First time blocked">' . $blocked_urls[$md5_url] . '</span>';
        unset($blocked_urls[$md5_url]);

        $out .= "<a href=\"http://nullrefer.com/?http://$url\" target=\"_blank\">$url</a> <a href=\"blocker_url.php?act=unblock&url=" . rawurlencode($url) . 
        "\" target=\"working_frame\" class=\"unblock unblock_acc\" title=\"Unblock URL\" ><img src=\"img/unblock.png\" />Unblock</a> <a href=\"blocker_url.php?act=block&url=" .
        rawurlencode($url) . "\" target=\"working_frame\" class=\"unblock unblock_acc\" title=\"Block URL\" ><img src=\"img/block.png\" />Block</a>$blocked_time<br>\n";

        $i++;
    }

    if(count($blocked_urls)>0) {
        foreach ($blocked_urls as $file_with_timestamp => $time) {
            unlink($GLOBALS['temp_folder'] . 'autoblocked_urls/' . $file_with_timestamp);            
            echo 'old file ' . $file_with_timestamp . " was deleted. <br />\n";
        }
    echo "<br>\n";
    }
}



$exe_time = time() - $start_time;
$exe_time = date("i:s", $exe_time); ?>
<!DOCTYPE html>
<html>
<head>

<title>Blocked Advertiser URLs</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="referrer" content="same-origin" />
<meta name="robots" content="noindex, nofollow" />
<link href="img/favicon.ico" rel="shortcut icon" type="image/x-icon" />
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=4, minimum-scale=0.1" />
<link rel="stylesheet" type="text/css" href="style.css"/>
<style>
* { font-size: 16px;  font-family: Calibri, Verdana, Arial; }
body { background: #fff; }
.working_frame { max-width: 400px; height:700px; }

span[title] { color:#999; }

a.block, a.unblock {
	font-size: 14px;
    line-height: 12px;
	border: solid 1px #c6c6c6;
    color: #444;
    display: inline-block;
    padding: 5px 3px;
    margin: 5px 0px;
    font-family: Arial,Helvetica,sans-serif;
    text-decoration: none;
	background-image: -webkit-gradient(linear,left top,left bottom,from(#f6f6f6),to(#f1f1f1));
    background-image: -webkit-linear-gradient(top,#f6f6f6,#f1f1f1);
    -webkit-box-shadow: inset 0 1px 2px rgba(0,0,0,0.1);
    box-shadow: inset 0 1px 2px rgba(0,0,0,0.1);
}

a.block:active, a.unblock:active {
	background-image: -webkit-gradient(linear,left top,left bottom,from(#f6f6f6),to(#f1f1f1));
    background-image: -webkit-linear-gradient(top,#f6f6f6,#f1f1f1);
    background-color: #f6f6f6;
}

a.block:hover, a.unblock:hover {
    background-image: -webkit-gradient(linear,left top,left bottom,from(#f8f8f8),to(#f1f1f1));
    background-image: -webkit-linear-gradient(top,#f8f8f8,#f1f1f1);
    background-color: #f8f8f8;
    border-color: #c6c6c6;
    color: #222;
    -webkit-box-shadow: 0 1px 1px rgba(0,0,0,0.1);
}
input[type="submit"], button { max-width: 200px; margin: 3px; }

.form_for_urls { position: absolute; top: 10px; right: 10px; }
.area_for_urls { width: 300px; height: 150px;}

@media screen and (max-width: 600px) {

	.form_for_urls { position: relative; top: 20px; left: 10px; margin-bottom: 40px; }

}

</style>

</head>
<body>
<?php if (!isset($_POST['confirmation'])) { ?>

	<form method="post" target="working_frame" >

	<label>
		Do you want to unblock all URLs?<br>
		You should type «agree» to confirm.<br>
		There is 100 URLs per time limitation.<br>
		<input type="text" name="confirmation" placeholder="Type «agree»" required autocomplete="off"/><br>
	</label>
	<br>

	<input class="submit" type="submit" value="Start unblocking process" />

	</form>

	<form method="post" target="working_frame" action="blocker_url.php" class="form_for_urls">

	http://<br>
	<textarea name="urls" class="area_for_urls"></textarea>

	<input class="submit" type="submit" value="Block URLs" />

	</form>

<h3>Total <?= $i ?> URLs blocked</h3>

<?php } ?>

<p>Execution time: <?= $exe_time ?>.</p>
<p class="adv_ids">

<?= $out ?>
</p>

<?php if (!isset($_POST['confirmation'])) { ?>
<iframe class="working_frame" name="working_frame" id="working_frame" ></iframe>
<?php } ?>


</body>
</html>