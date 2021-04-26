<?php //—
$start_time = time();
include_once 'functions.php';
$GLOBALS['CURLOPT_TIMEOUT'] = 30; //Adjust it if you need more (If you can not see bloked accounts).

if (!is_still_log_in()) {
    die('<p>You should log in to Google first</p>');
}

foreach ($_GET as $value)
    if (!is_data_safely($value))
        die();

/*if (isset($_GET['arc'])) {
    if ($_GET['arc'] == 'arc5')
        $GLOBALS['set_gl']['arc'] = $set['arc'] = 'arc5';
    if ($_GET['arc'] == 'old_arc')
        $GLOBALS['set_gl']['arc'] = $set['arc'] = 'old_arc';
}*/

$first_result = curl_get($GLOBALS['new_arc_tab_req_string'], '', '');

if (isset($set['log']))
    create_log($first_result, 's1_adv.');


unset($first_result);

/**
 * Get first access tokens:
**/
get_xsrf_token_new();


$advertisers_list = get_advertisers_list();

//if ($GLOBALS['set_gl']['arc'] == 'arc5')
    $result_keyword = 'default';
/*else
    $result_keyword = 'result';
*/

/**
 * Get list of already blocked accounts:
**/


$autoblocked_accs = scandir($GLOBALS['temp_folder'] . 'accs_ads/');
unset($autoblocked_accs[0], $autoblocked_accs[1]); //removes «.» and «..»

foreach ($autoblocked_accs as $acc_file_name) {

    $blocked_time = filectime($GLOBALS['temp_folder'] . 'autoblocked_accs/' . $acc_file_name);
    $blocked_time = date("d.m.Y G:i:s", $blocked_time);
    $blocked_accs['time'][$acc_file_name] = $blocked_time;
    $adv_title = file_get_contents($GLOBALS['temp_folder'] . 'accs_ads/' . $acc_file_name);
    $blocked_accs['ads_texts'][$acc_file_name] = $adv_title;
}

$out = '';
$i = 0;
if (@$_POST['confirmation'] == 'agree') {

    if(isset($_POST['my_limit']))
        if(!$_POST['my_limit'])
            unset($_POST['my_limit']);

    if($_POST['my_limit'])
        $unblock_limit = (int)$_POST['my_limit'];
    else
        $unblock_limit = 100;
    foreach ($advertisers_list as $adv_obj) {
        
        if ($i >= $unblock_limit) 
            break;
        
        $adv_id = $adv_obj->{1}->{1}->{1};
        $result = unblock_adwords_account($adv_id);
        if (is_object($result->error))
            die('<p>' . $result->error->code . ' ' . $result->error->message . '</p>');

        $result = $result->$result_keyword->{1};
        $result = $result[0]->{1};
        if ($result) {
            $result = ' unblocked';
            @$adv_name = trim($adv_obj->{2} . ' ' . $adv_obj->{3});
            $out .= $adv_name . $result . "<br>\n";
            //unlink($GLOBALS['temp_folder'] . 'autoblocked_accs/' . $adv_obj->{3});
            //unlink($GLOBALS['temp_folder'] . 'accs_ads/' . $accs_ads_filename);
        }

        $i++;
    }

} else {

    foreach ($advertisers_list as $adv_obj) {


        $adv_id = $adv_obj->{1}->{1}->{1};
        @$adv_name = trim($adv_obj->{2} . ' ' . $adv_obj->{3});
        if (@$adv_obj->{2})
            $accs_ads_filename = md5($adv_obj->{2});
        else {
            $accs_ads_filename = $adv_obj->{3} . '_' . $GLOBALS['set_gl']['arc'];    
            $accs_ads_filename_old = $adv_obj->{3};   //temp
        }
        


        $adv_title = $blocked_accs['ads_texts'][$accs_ads_filename];
            if(!$adv_title) $adv_title = $blocked_accs['ads_texts'][$accs_ads_filename_old];    //temp
        $list = explode("\n", $adv_title, 2);
        $adv_title = ' title="' . $adv_title . '"';
        $ads_first_line = ' ' . mb_substr($list[0], 0, 38, 'UTF-8');

        $blocked_time = $blocked_accs['time'][$accs_ads_filename];
            if(!$blocked_time) $blocked_time = $blocked_accs['time'][$accs_ads_filename_old];    //temp
        $blocked_time = ' <span title="First time blocked">' . $blocked_time . '</span>';

        unset($blocked_accs['time'][$accs_ads_filename]);

        $out .= "<span$adv_title>$adv_name</span> <a href=\"blocker.php?type=adwords_acc&act=unblock&ad_id=" . rawurlencode($adv_id) .
        "\" target=\"working_frame\" class=\"unblock unblock_acc\" title=\"Unblock AdWords account\" ><img src=\"img/unblock.png\" />Unblock</a> <a href=\"blocker.php?type=adwords_acc&act=block&ad_id=" .
        rawurlencode($adv_id) . "\" target=\"working_frame\" class=\"unblock unblock_acc\" title=\"Block AdWords account\" ><img src=\"img/block.png\" />Block</a>$ads_first_line $blocked_time<br>\n";

        $i++;

    }
    $to_output = '';
    if(count($blocked_accs['time'])>1) {
        foreach ($blocked_accs['time'] as $file_name => $time) {
            if( ((stripos($file_name, $GLOBALS['set_gl']['arc']) !== false) && (stripos($file_name, 'adv-') !== false)) || 
            (stripos($file_name, 'adv-') === false) ) {
                
                unlink($GLOBALS['temp_folder'] . 'autoblocked_accs/' . $file_name);
                unlink($GLOBALS['temp_folder'] . 'accs_ads/' . $file_name);
                $to_output .= 'old file <i>' . $file_name . "</i> was deleted. <br />\n";
            }                
        }
    }
}



/* It's old. When was 2 ARCs: Old and New.
if ($GLOBALS['set_gl']['arc'] == 'arc5')
    $check_another_arc = '<a href="advertisers.php?arc=old_arc">Check Accounts from old ARC</a>';
else
    $check_another_arc = '<a href="advertisers.php?arc=arc5">Check Accounts from new ARC</a>';
*/

/*
if(@$_POST['confirmation']=='agree') {		// Acc folder cleaning

$acc_files=scandir($GLOBALS['temp_folder'].'autoblocked_accs');

foreach($acc_files as $acc_file) {
if($acc_file!='.'&&$acc_file!='..')
unlink($GLOBALS['temp_folder'].'autoblocked_accs/'.$acc_file);
}
}
*/

$exe_time = time() - $start_time;
$exe_time = date("i:s", $exe_time); ?>
<!DOCTYPE html>
<html>
<head>

<title>Advertisers account list</title>
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


</style>

</head>
<body>
<?php if (!isset($_POST['confirmation'])) { ?>

	<form method="post" target="working_frame" >

	<label>
		Do you want to unblock all accounts?<br>
		You should type «agree» to confirm.<br>
		There is 100 accs per time limitation.<br>
		<input type="text" name="confirmation" placeholder="Type «agree»" required autocomplete="off"/><br><br>
        If you want to unblock more or less than<br>
        100 accs please type quantity here:<br>
		<input type="tel" name="my_limit" placeholder="Are you sure?" autocomplete="off"/><br> 
	</label>
	<br>

	<input class="submit" type="submit" value="Start unblocking process" />

	</form>
<h3>Total <?= $i ?> Account<?php if($i > 1) echo "s"; ?> blocked</h3>

<p><?= $to_output ?></p>

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