<?php //—
include 'functions.php';

$bad = 0;
foreach ($_POST as $index => $value) {
    if (!is_data_safely($value))
        $bad = 1;
    if (!$value)
        unset($_POST[$index]);
}

if (!$bad) {
    
if(isset($_POST['username'])) {
    if(trim($_POST['username'])) {
        $username = trim($_POST['username']);
        unset($_POST['username']);
    }    
}  
  
if(!isset($username)) {
    $username = exec('whoami');
}
  
    unset($_POST['num_of_cycles'], $_POST['log'], $_POST['login'], $_POST['run_interval'], $_POST['badadlist_show'], $_POST['whitelist_show'], $_POST['searchwords_show'], $_POST['display_ad_url']);
    $settings = json_encode($_POST);


$script_folder = __DIR__;

$cron_settings = <<<CRON_SET


# Check 50 new text ads:<br />
2,4,8,13,53,58  *  *  *  * $username /usr/bin/php $script_folder/search_bad_ads.php '!!!{"num_of_pages":"1","num_of_ads_per_page":"50","text":"checked","stopwords_check":"checked","whitelist":"checked","lat2cyr":"checked","ad_account":"checked","blogspot":"checked","disguised_text":"checked","too_many_spaces":"checked","check_target_url":"checked","mark_reviewed":"checked","get_stats":"checked","no_save_clear":"checked","report_words":"checked","report_disg":"checked","arc":"arc5"}'
<br /><br />
# Check 50 new media ads:<br />
1,3,6,12,52,59  *  *  *  * $username /usr/bin/php $script_folder/search_bad_ads.php '!!!{"num_of_pages":"1","num_of_ads_per_page":"50","rich_media":"checked","stopwords_check":"checked","whitelist":"checked","lat2cyr":"checked","ad_account":"checked","blogspot":"checked","too_many_spaces":"checked","check_target_url":"checked","mark_reviewed":"checked","get_stats":"checked","no_save_clear":"checked","report_words":"checked","report_disg":"checked","arc":"arc5"}'
<br /><br />
# Check 50 new image ads:<br />
12,20,25,35,40,51  *  *  *  * $username /usr/bin/php $script_folder/search_bad_ads.php '!!!{"num_of_pages":"1","num_of_ads_per_page":"50","image":"checked","stopwords_check":"checked","whitelist":"checked","ad_account":"checked","blogspot":"checked","check_target_url":"checked","mark_reviewed":"checked","get_stats":"checked","report_words":"checked","no_save_clear":"checked","arc":"arc5"}'
<br /><br />
# Check 250 (5 pages by 50 ads) already checked media ads:<br />
7,11,22,33,39,49  *  *  *  * $username /usr/bin/php $script_folder/search_bad_ads.php '!!!{"num_of_pages":"5","num_of_ads_per_page":"50","rich_media":"checked","stopwords_check":"checked","whitelist":"checked","lat2cyr":"checked","ad_account":"checked","blogspot":"checked","too_many_spaces":"checked","check_target_url":"checked","get_stats":"checked","reviewed":"checked","no_save_clear":"checked","report_words":"checked","report_disg":"checked","arc":"arc5"}'
<br /><br />
# Check 250 (5 pages by 50 ads) already checked text ads:<br />
2,17,27,38,47,54  *  *  *  * $username /usr/bin/php $script_folder/search_bad_ads.php '!!!{"num_of_pages":"5","num_of_ads_per_page":"50","text":"checked","stopwords_check":"checked","redirects_text":"checked","badadlist_check":"checked","whitelist":"checked","lat2cyr":"checked","reviewed":"checked","ad_account":"checked","blogspot":"checked","too_many_spaces":"checked","disguised_text":"checked","check_target_url":"checked","get_stats":"checked","no_save_clear":"checked","report_words":"checked","report_disg":"checked","arc":"arc5"}'
<br /><br />
# Check up to 50 ads by each word from searchword list:<br />
15,30,45  *  *  *  * $username /usr/bin/php $script_folder/search_bad_ads.php '!!!{"num_of_pages":"5","num_of_ads_per_page":"50","text":"checked","stopwords_check":"checked","redirects_text":"checked","badadlist_check":"checked","whitelist":"checked","lat2cyr":"checked","reviewed":"checked","ad_account":"checked","blogspot":"checked","too_many_spaces":"checked","disguised_text":"checked","check_target_url":"checked","get_stats":"checked","no_save_clear":"checked","report_words":"checked","report_disg":"checked","arc":"arc5"}'


CRON_SET;

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
* { font-size: 16px;  font-family: Calibri, Verdana, Arial; }
body { background: #fff; }
</style>
</head>
<body>

<?= $cron_settings ?>


<br /><br />
<p>Just put this strings to your crontab file.</p>


</body>
</html>
