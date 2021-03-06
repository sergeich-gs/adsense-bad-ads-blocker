<?php
header('Access-Control-Allow-Origin: *');
header("Content-type: text/html; charset=utf-8");

include 'functions.php';
if (is_data_safely($_SERVER['HTTP_USER_AGENT']))
    file_put_contents($GLOBALS['temp_folder'] . 'useragent.txt', $_SERVER['HTTP_USER_AGENT']);
@$stopwords_text = file_get_contents($GLOBALS['settings_folder'] . 'stopwords_text.txt');
@$stopwords_media = file_get_contents($GLOBALS['settings_folder'] . 'stopwords_media.txt');


if(isset($_GET['cron_settings'])) {
    if (is_data_safely($_GET['cron_settings'])) {

        $number_cr = (int) $_GET['cron_settings'];

        $for_cron_settings  = '<input type="hidden" name="cron_settings" value="' . $number_cr . '" />';
        $cron_warning  = "<h4 style=\"color: red;\">This settings only for cron set №$number_cr <a href=\"./\" title=\"Go to main settings\">main</a></h4>";

        $suffix = 'cron' . $number_cr;

        if (file_exists($GLOBALS['settings_folder'] . "settings.$suffix.ini")) {
            $set = file_get_contents($GLOBALS['settings_folder'] . "settings.$suffix.ini");
            $set = json_decode($set, 1);
        }

        if(isset($set['set_name'])) {
            $set['set_name'] = base64_decode($set['set_name']);
            $set['set_name'] = htmlspecialchars($set['set_name']);
        }
        $height_expand = '340';
    }
} else {
    $for_cron_settings = $cron_warning = '';
    $height_expand = '570';
}
/** Make cron buttons */

$cron_buttons = 'Cron settings: ';
for($i = 1; $i <= 10; $i++) {

    $cron_buttons .= '<a href="?cron_settings=' . $i . '">' . $i . '</a> ';

    if(!file_exists($GLOBALS['settings_folder'] . "settings.cron$i.ini"))
    break;
}

$settings_folder = basename($GLOBALS['settings_folder']) . '/';
$cron_folder = dirname($GLOBALS['settings_folder']);
$html_sep = '';
?>
<!DOCTYPE html>
<html>
<head>
<title>AdSense bad ads Blocker</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="referrer" content="same-origin" />
<meta name="robots" content="noindex, nofollow" />
<link href="img/favicon.ico" rel="shortcut icon" type="image/x-icon" />
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=4, minimum-scale=0.1" />

<link rel="stylesheet" type="text/css" href="style.css?v=<?= @$v ?>"/>

<?php if (!isset($set['b_unb_buttons'])) { ?>
<link rel="stylesheet" type="text/css" href="b_unb.css?v=<?= @$v ?>"/>
<?php }

if (!isset($set['display_ad_url'])) { ?>
<style>
.ad p.ad_url { display: none !important; }
</style>
<?php } ?>

<script type="text/javascript" src="script.js?v=<?= @$v ?>"></script>



</head>
<body>



<?= $html_sep ?>
<?php /* 1/*sep*/ ?>

<div class="second settings">

<div class="first" id="first" style="right: 0%; ">



<?= $html_sep ?>
<?php /* settings/*sep*/ ?>

<div class="left_colulmn colulmns">
	<form method="post" action="settings_update.php" >

    <?= $for_cron_settings ?>

	<h3>Main Settings (User Manual <a href="http://www.howgadget.com/adsense/kak-zablokirovat-musornye-obyavleniya-v-adsense.html" target="_blank" rel="noreferrer" >here</a>)</h3>

    <?= $cron_warning ?>

<?php if(!$cron_warning) { ?>
	<label>
		Number of cycles:
		<input class="" type="number" name="num_of_cycles" value="<?= @$set['num_of_cycles'] ?>" /> (Only for web, not for cron)
	</label>
	<br />
<?php } else {?>
	<label>
		Set name:
		<input type="text" name="set_name" maxlength="148" value="<?= @$set['set_name'] ?>" /> (Only for you)
	</label>
	<br />
<?php }?>
	<label>
		Number of pages:
		<input class="" type="number" name="num_of_pages" value="<?= @$set['num_of_pages'] ?>" />
	</label>
	<br />

	<label>
		Ads per page:
		<input class="" type="number" name="num_of_ads_per_page" value="<?= @$set['num_of_ads_per_page'] ?>" />
	</label>
	<br />

	<label title="Show ads only for some last days. Google offers only 1, 3 and 7 days, here you can try any value" >
		New ads in last:
		<input class="" type="number" name="last_of_days" value="<?= @$set['last_of_days'] ?>" />
	</label>
	<br />


	Ad types:
	<label>
		Text:<input type="checkbox" value="checked" <?= @$set['text'] ?> name="text" />
	</label>

	<label>
		Rich media:<input type="checkbox" value="checked" <?= @$set['rich_media'] ?> name="rich_media" />
	</label>

	<label>
		Image:<input type="checkbox" value="checked" <?= @$set['image'] ?> name="image" />
	</label>

<?php if (@$set['arc'] == 'adx') { ?>

	<label title="Real-time bidding">
		RTB:<input type="checkbox" value="checked" <?= @$set['rtb'] ?> name="rtb" />

	</label>
<?php } ?>
	<br /><br />

	<label  title="Separate list for each type of ads" >Check by stopwords list: <input name="stopwords_check" type="checkbox" value="checked" <?= @$set['stopwords_check'] ?> /></label>

	<br />

	<label title="Use ARC search by all words from the list to find bad ads"><span class="red_arrow" >Check by <a href="search_words.php" target="_blank">Searchwords</a>: </span><input name="searchwords_check" type="checkbox" value="checked" <?= @$set['searchwords_check'] ?> /> </label><input title="Check to show the list here" name="searchwords_show" type="checkbox" value="checked" <?= @
$set['searchwords_show'] ?> />

	<br />
	<label title="If enable whitelisted ads (if ad contains at least one string from whitelist) will never be blocked, also whitelisted ads will not appear at «clear» list. Second checkbox to show the list here">Use <a href="whitelist.php" target="_blank">Whitelist</a>: <input name="whitelist" type="checkbox" value="checked" <?= @
$set['whitelist'] ?> /></label><input title="Check to show the list here" name="whitelist_show" type="checkbox" value="checked" <?= @
$set['whitelist_show'] ?> />

	<br />
	<label title="This filter enable additional check by stopwords with replaced some latin symbols (visually similar) to cyrillic in ad text">Replace lat2cyr: <input name="lat2cyr" type="checkbox" value="checked" <?= @$set['lat2cyr'] ?> /></label>

	<br />

	<label>Check for redirects: Text:<input title="Only for Text ads" name="redirects_text" type="checkbox" value="checked" <?= @$set['redirects_text'] ?>/></label><label title="Only for Rich Media ads"> &nbsp; &nbsp; Media:<input name="redirects_media" type="checkbox" value="checked" <?= @
$set['redirects_media'] ?>/></label>

	<br />

	<label><span class="red_arrow" >Check reviewed ads: </span><input name="reviewed" type="checkbox" value="checked" <?= @$set['reviewed'] ?>/></label>

	<br />

	<label>Block AdWords account: <input name="ad_account" type="checkbox" value="checked" <?= @$set['ad_account'] ?>/></label>

	<br />

	<label title="Block URL of each blocked ad e.g. badsite.com/bad/page/download.jhtml. Will not work if «Block domain» enabled">Block URL: <input name="ad_url" type="checkbox" value="checked" <?= @$set['ad_url'] ?>/></label>

	<br />

	<label title="Block domain of each blocked ad e.g. badsite.com">Block domain: <input name="ad_domain" type="checkbox" value="checked" <?= @$set['ad_domain'] ?>/></label>

	<br />




<!--
	<label title="">Check domain register date: <input name="check_domain_date" type="checkbox" value="checked" <?= @$set['check_domain_date'] ?>/></label>

	<br />

	<label title="">Block ads with domains under: <input name="block_ads_domain_under" type="number" value="checked" <?= @$set['block_ads_domain_under'] ?>/> days</label>

	<br />

	<label title="">Block domains under: <input class="" type="number" name="block_domain_under" value="<?= @$set['block_domain_under'] ?>" /> days</label>

	<br />
-->




	<label title="Block ads with «blogspot» in URL">Check «blogspot»: <input name="blogspot" type="checkbox" value="checked" <?= @$set['blogspot'] ?>/></label>

	<br />

	<label title="Block ads with disguised latin symbols in cyrillic ads">Check disguised: Text:<input title="Only for Text ads" name="disguised_text" type="checkbox" value="checked" <?= @$set['disguised_text'] ?>/></label><label title="Only for Rich Media ads"> &nbsp; &nbsp; Media:<input name="disguised_media" type="checkbox" value="checked" <?= @$set['disguised_media'] ?>/></label>

	<br />

	<label title="Block ads with too many spaces in ad, e. g. «D O O M E D»">Check too many spaces:<input title="" name="too_many_spaces" type="checkbox" value="checked" <?= @$set['too_many_spaces'] ?>/></label>

	<br />

	<label title="Search for stop word in target URL too">Check target URL: <input name="check_target_url" type="checkbox" value="checked" <?= @$set['check_target_url'] ?>/></label>

	<br />

	<label title="Some advertisers has a name. If adv. has name it will be checked">Check adv. name: <input name="check_adv_name" type="checkbox" value="checked" <?= @$set['check_adv_name'] ?>/></label>

	<br />

	<label><span class="red_arrow" >Check only «Prioritized for review»: </span><input name="predicted" type="checkbox" value="checked" <?= @$set['predicted'] ?>/></label>

	<br />

	<label title="Do not block ads if its advertiser has a name" >Do not block advs with name: </label>
    <label>Text: <input name="do_not_with_adv_name_txt" type="checkbox" value="checked" <?= @$set['do_not_with_adv_name_txt'] ?>/></label>
    <label>Media: <input name="do_not_with_adv_name_media" type="checkbox" value="checked" <?= @$set['do_not_with_adv_name_media'] ?>/></label>

	<br />

	<label>Mark reviewed as reviewed: <input name="mark_reviewed" type="checkbox" value="checked" <?= @$set['mark_reviewed'] ?>/></label>

	<br />

	<label>Get ad stats: <input name="get_stats" type="checkbox" value="checked" <?= @$set['get_stats'] ?>/></label>

	<br />

	<label title="Block every 20 minutes domains from BlockedAds.ru site">Block from «<a target="_blank" href="https://blockedads.ru/">BlockedAds</a>»: <input name="blockedads_site" type="checkbox" value="checked" <?= @$set['blockedads_site'] ?>/></label>

    <?php
    if($set['arc'] != 'arc5')
        $hidden_reports = 'style="display: none;" ';
       else
        $hidden_reports = '';
    ?>

	<p <?=$hidden_reports?> title="Equal button «REPORT AD» you can see after ad blocking. Works only with new ARC!" >Report ad blocked by:<br />
	<label title="Blocked for any word of any list">Words: <input name="report_words" type="checkbox" value="checked" <?= @$set['report_words'] ?>/></label>
	<label title="Blocked for disguised symbols">Disguised: <input name="report_disg" type="checkbox" value="checked" <?= @$set['report_disg'] ?>/></label>
	<label title="Blocked for any kind of redirect">Redirect: <input name="report_redir" type="checkbox" value="checked" <?= @$set['report_redir'] ?>/></label>
	</p>
	<br />


	<h3 onclick="expand_close('debug', <?=$height_expand?>);" >Debug, login and other...</h3>

	<div id="debug" style="height: <?php if($cron_warning) echo $height_expand; else echo '0'; ?>px;" >

	<button class="cron_settings" formtarget="working_frame" formaction="cron_settings.php" tabindex="-1" >Show settings for cron</button>

    <input class="cron_settings_input" name="username" type="text" placeholder="username for cron settings" value="<?php echo exec('whoami'); ?>" />

	<label><span class="red_arrow" >Enable logs: </span><input name="log" type="checkbox" value="checked" <?= @$set['log'] ?>/></label>

	<br />

	<label>Disable utf8_decode: <input name="utf8_off" type="checkbox" value="checked" <?= @$set['utf8_off'] ?>/></label>

	<br />

<?php if(!$cron_warning) { ?>
	<label title="Block and unblock buttons under ads listed below" >Show block/unblock buttons: <input name="b_unb_buttons" type="checkbox" value="checked" <?= @$set['b_unb_buttons'] ?>/></label>

	<br />
<?php } ?>

	<label>Don't save clear ads: <input name="no_save_clear" type="checkbox" value="checked" <?= @$set['no_save_clear'] ?>/></label>

	<br />

	<label><span class="red_arrow" >Don't save any ads: </span><input name="no_save_any" type="checkbox" value="checked" <?= @$set['no_save_any'] ?>/></label>

	<br />

<?php if(!$cron_warning) { ?>
	<label title="If your server sends «x-frame-options: DENY» and you can not disable it" >Frames do not work: <input name="frames_off" type="checkbox" value="checked" <?= @$set['frames_off'] ?>/></label>

	<br />

	<label title="Display full ad URL after ad text" >Display ad URL: <input name="display_ad_url" type="checkbox" value="checked" <?= @$set['display_ad_url'] ?>/></label>

	<br />
<?php } ?>

<?php $arc_checked[$set['arc']] = 'checked'; ?>
	Use ARC:
	<label title="Get ads from new Ads Review Center" for="adsense_new">New:</label><input name="arc" type="radio" value="arc5" <?= @$arc_checked['arc5'] ?> id="adsense_new" />
	<label title="Get ads from old Ads Review Center" for="adsense_old">Old:</label><input name="arc" type="radio" value="old_arc" <?= @$arc_checked['old_arc'] ?> id="adsense_old" disabled />
	<label title="Get ads from AdX Review Center" for="arc_adx">AdX:</label><input name="arc" type="radio" value="adx" <?= @$arc_checked['adx'] ?> id="arc_adx"/>

	<label class="nc" title="Type here your AdX Pub Id">
		<br />
		AdX Pub Id (only for AdX):<br />
		<input name="pub_id_adx" type="text" placeholder="pub-0000000000000000" value="<?= @$set['pub_id_adx'] ?>" />
	</label>
	<label class="nc_adsense" title="Type here your AdSense Pub Id">
		<br />
		AdSense Pub Id (only for AdSense):<br />
		<input name="pub_id_adsense" type="text" placeholder="pub-0000000000000000" value="<?= @$set['pub_id_adsense'] ?>" />
	</label>


	<br /><br />

	<label title="Set how long autoblocked accounts will be blocked. After AdWords accounts will be unblocked" >Unblock accs blocked: <input name="acc_age" type="number" value="<?= @$set['acc_age'] ?>" /> days ago</label>

	<br /><br />

<?php if(!$cron_warning) { ?>
	<label>
		Login (e-mail):<br />
		<input class="login" name="login" type="text"  value="<?= @$set['login'] ?>" placeholder="Will be saved" />
	</label>
<?php } ?>

<?php /*
	<p title="Or just add the string to crontab file to run each 10 minutes. If ypu don't see a full path to file use full path instead.">*  *  *  *  * <?php echo exec('whoami'); ?> /usr/bin/php <?= dirname(__file__); ?>/search_bad_ads.php</p>
*/ ?>

<?php if(!$cron_warning) { ?>
	<h3><a href="separate.php" >Setting Separate Version</a></h3>
<?php } ?>

    <?= $cron_buttons ?>

	</div>

<?= $html_sep ?>
<?php /* settings_run_interval/*sep*/ ?>

<?php if(!$cron_warning) { ?>
	<label title="0 or empty disables autorun">
	Run every <input type="number" class="run_interval" name="run_interval" id="run_interval" value="<?= @$set['run_interval'] ?>" /> minutes.
	</label>
<?php } ?>

<?= $html_sep ?>
<?php /* settings_after_run_interval/*sep*/ ?>

	<br /><br />

<?php if($cron_warning)
          $button_text = "Update settings for cron set №" . $number_cr;
      else
          $button_text = "Update settings";
?>

	<input class="submit" type="submit" value="<?= $button_text ?>" />


	</form>

</div>


<div class="center_colulmn colulmns">

	<h3>List of Stop Words</h3>
	<form method="post" action="list_update.php" >

	<h4>For text and image ads</h4>
	<textarea name="stopwords_text" id="stopwords_text" class="wordlist" wrap="off" ><?= $stopwords_text . "\n\n\n\n\n\n\n\n\n\n\n\n" ?></textarea>

	<h4>Only for media ads</h4>
	<textarea name="stopwords_media" id="stopwords_media" class="wordlist" wrap="off" ><?= $stopwords_media . "\n\n\n\n\n\n\n\n\n\n\n\n" ?></textarea>

	<?php if (isset($set['searchwords_show'])) {
    $search_words = file_get_contents($GLOBALS['settings_folder'] . 'search_words.txt'); ?>
	<h4>List of search words</h4>
	<textarea name="search_words" id="search_words" class="wordlist" wrap="off" ><?= $search_words . "\n\n\n\n\n\n\n\n\n\n\n\n" ?></textarea>
	<script>
	document.getElementById("search_words").scrollTop=document.getElementById("search_words").scrollHeight;
	</script>
	<?php } ?>

	<?php if (isset($set['badadlist_show'])) {
    $bad_ads_text = file_get_contents($GLOBALS['settings_folder'] . 'bad_ads_text.txt'); ?>
	<h4>List of bad ads words</h4>
	<textarea name="bad_ads_text" id="bad_ads_text" class="wordlist" wrap="off" ><?= $bad_ads_text . "\n\n\n\n\n\n\n\n\n\n\n\n" ?></textarea>
	<script>
	document.getElementById("bad_ads_text").scrollTop=document.getElementById("bad_ads_text").scrollHeight;
	</script>
	<?php } ?>

	<?php if (isset($set['whitelist_show'])) {
    $whitelist = file_get_contents($GLOBALS['settings_folder'] . 'whitelist.txt'); ?>
	<h4>Whitelist</h4>
	<textarea name="whitelist" id="whitelist" class="wordlist" wrap="off" ><?= $whitelist . "\n\n\n\n\n\n\n\n\n\n\n\n" ?></textarea>
	<script>
	document.getElementById("whitelist").scrollTop=document.getElementById("whitelist").scrollHeight;
	</script>
	<?php } ?>



	<br /><br />

	<input class="submit" type="submit" value="Update lists" />

	</form>

	<script>
	document.getElementById("stopwords_text").scrollTop=document.getElementById("stopwords_text").scrollHeight;
	document.getElementById("stopwords_media").scrollTop=document.getElementById("stopwords_media").scrollHeight;
	</script>

</div>

<?= $html_sep ?>
<?php /* right_colulmn_top/*sep*/ ?>

<div class="right_colulmn colulmns">
<?= $html_sep ?>
<?php /* auth/*sep*/ ?>
	<h3 onclick="expand_close('auth_form', 430);" >Google Auth</h3>


	<form method="post" action="login.php" id="auth_form" class="auth_form" target="working_frame" style="height: 0px;" onsubmit="wait('working_frame');">

<?php     if (!is_still_log_in()) { ?>

<?php
            if (@$set['login']) {
?>

	<label>
		Password:<br />
		<input class="password" type="password" name="password" placeholder="Will not be saved" />
	</label>
	<br />
<?php     if (!isset($set['frames_off'])) { ?>
	<input class="submit" type="submit" value="Login to Google"  />
<?php     } else { ?>
	<input class="submit" type="submit" value="Login to Google (in new tab)" formtarget="_blank" />
<?php     } ?>
    <br />
<?php
            } else {
    echo '<p>You should enter and save login first for auth by password. Left bottom under «Debug and other...» </p>';
            }
?>


    Or just paste here your Google cookies and save:<br />
    <textarea class="g_cookie" name="g_cookie"></textarea>
    <input class="submit" type="submit" name="save_cookie_button" value="Save cookie"<?php if (isset($set['frames_off'])) { ?> formtarget="_blank"<?php } ?> />
    Extention for Chrome to get cookies: <a href="https://chrome.google.com/webstore/detail/get-cookiestxt/bgaddhkoddajcdgocldbbfleckgcbcid/related" target="_blank" >cookies.txt</a><br />
    Don't forget to fill your pub-id! Left bottom under «Debug and other...».
<?php     } ?>

<?php     if (is_still_log_in()) { ?>
	<a href="logout.php" target="working_frame" onclick="wait('working_frame');" >Log out</a>
<?php     } ?>



	</form>

<?= $html_sep ?>
<?php /* 4/*sep*/ ?>

	<br />

<?= $html_sep ?>
<?php /* working_frame_with_buttons/*sep*/ ?>
	<div class="timer"><span id="timer"></span></div>
<?php if (is_still_log_in()) { ?>

<?php     if (!isset($set['frames_off'])) { ?>
	<a href="search_bad_ads.php" target="working_frame" onclick="start_searching('search_bad_ads.php', 'working_frame');" id="search_bad_ads_link" ><button>Start searching</button></a>
<?php     } else { ?>
	<a href="search_bad_ads.php" target="_blank" onclick="start_searching('search_bad_ads.php', '_blank');" ><button>Start searching (in new tab)</button></a>
<?php     } ?>

	<?php     if (@$set['num_of_cycles']) { ?>
	<div class="cycles" >
		<a href="cycles.php" onclick="start_searching('cycles.php', 'working_frame');" target="working_frame"  ><button><?= @$set['num_of_cycles'] ?>  search cycles</button></a>
		<?php /*<a href="cycles.php?searcher=blocked.php" onclick="start_searching('cycles.php?searcher=blocked.php', 'working_frame');" target="working_frame"  ><button><?= @$set['num_of_cycles'] ?>  blocked cycles</button></a> */ ?>
	</div>
	<?php     } ?>

<?php } else { ?>
	<p>You should log in to Google first. If you are already logged in <a href="./" target="_top">refresh the page</a>.</p>
<?php } ?>


<?php if (!isset($set['frames_off'])) { ?>

	<br />

	<iframe class="working_frame" name="working_frame" id="working_frame" ></iframe>

	<br /><br />

<?php } else { ?>
	<p>All results will be in new tabs.</p>
<?php } ?>







<?php if (is_still_log_in()) { ?>

	<form method="post" target="working_frame" action="blocker_url.php" class="form_for_urls" onsubmit="start_searching('blocker_url.php', 'working_frame');">

	<textarea name="urls" class="area_for_urls_main" placeholder="Put here list of domains or URLs for block (with or without http://). Each on new line."><?="\n\n\n\n\n\n\n\n\n\n\n\n\n\n"?></textarea>

	<input class="submit" type="submit" value="Block URLs (max. 200)" />

	</form>

<?php } ?>

<?= $html_sep ?>
<?php /* access_pass/*sep*/ ?>

	<h3 onclick="expand_close('auth_form2');" >Access Here Password</h3>

	<form method="post" action="set_pass.php" id="auth_form2" class="auth_form" target="working_frame" style="height: 0px;">

	<label>
		<input class="password" type="password" name="pass" placeholder="New password" /><br />
		If password is empty password promt will be disabled
	</label>
	<br />

	<input class="submit" type="submit" value="Change password" />

	</form>

<?= $html_sep ?>
<?php /* donate/*sep*/ ?>

	<h3 onclick="expand_close('donate', 390);" >Donate</h3>

	<div id="donate" style="height: 0px;" >
	<p>WMR: R907169982089<br />
	WMZ: Z117653446838</p>
	<iframe src="https://yoomoney.ru/quickpay/shop-widget?writer=seller&targets=Thanks%20for%20the%20Blocker&targets-hint=&default-sum=1000&button-text=14&payment-type-choice=on&hint=&successURL=&quickpay=shop&account=410011462510851" width="350" height="198" frameborder="0" allowtransparency="true" scrolling="no"></iframe>
	<br />
    Так же в благодарность буду рад принять ссылки с Ваших ресурсов (на любую страницу).
	</div>
<?= $html_sep ?>
<?php /* right_column_bottom/*sep*/ ?>

<a href="earnings.php" target="working_frame" onclick="start_searching('earnings.php', 'working_frame');" title="Today, yesterday, last 7, 28 days and this month earnings">Earnings</a> <br /><br />
<a title="With headers of blocked ads by the account" href="advertisers.php" target="_blank" >Advertisers account list</a> <br />
<a title="List of blocked URLs in your AdSense account" href="blocked_urls.php" target="_blank" >Blocked advertiser URLs</a> <br />
<a title="Tool for automated viewing pages by your URL list. Can help to improve your ads coverage" href="go-round/" target="_blank" >Go Round</a>


<?php if (@$set['arc'] != 'adx') { ?>

	<h3 onclick="expand_close('adsenselinks', 250);" >AdSense Links</h3>

	<div id="adsenselinks" style="height: 0px;" >
	<a href="https://www.google.com/adsense/new/u/0/<?= $GLOBALS['pub_id'] ?>/home" target="_blank" rel="noreferrer" >Home</a> <br />
	<a href="https://www.google.com/adsense/new/u/0/<?= $GLOBALS['pub_id'] ?>/main/allowAndBlockAds?webPropertyCode=ca-<?= $GLOBALS['pub_id'] ?>" target="_blank" rel="noreferrer" >Blocked advertisers (old)</a> <br />
	<a href="https://www.google.com/adsense/new/u/0/<?= $GLOBALS['pub_id'] ?>/arc/ca-<?= $GLOBALS['pub_id'] ?>" target="_blank" rel="noreferrer" >New ARC</a> <br />

	</div>

<?php } else { ?>

	<h3 onclick="expand_close('adsenselinks', 150);" >AdX Links</h3>

	<div id="adsenselinks" style="height: 0px;" >
	<a href="<?= $GLOBALS['arc_tab_req_string'] ?>" target="_blank" rel="noreferrer" >Home</a> <br />
	<a href="https://www.google.com/adsense/new/u/0/<?= $GLOBALS['pub_id'] ?>/main/allowAndBlockAds?webPropertyCode=ca-<?= $GLOBALS['pub_id'] ?>&tab=arcTab" target="_blank" rel="noreferrer" >ARC</a> <br />
	<a href="https://www.google.com/adsense/new/u/0/<?= $GLOBALS['pub_id'] ?>/main/allowAndBlockAds?webPropertyCode=ca-<?= $GLOBALS['pub_id'] ?>&tab=urlsTab" target="_blank" rel="noreferrer" >Blocked URLs</a> <br />
	<a href="https://www.google.com/adsense/new/u/0/<?= $GLOBALS['pub_id'] ?>/main/allowAndBlockAds?webPropertyCode=ca-<?= $GLOBALS['pub_id'] ?>&tab=gcbTab" target="_blank" rel="noreferrer" >General Categories</a> <br />
	<a href="https://www.google.com/adsense/new/u/0/<?= $GLOBALS['pub_id'] ?>/main/allowAndBlockAds?webPropertyCode=ca-<?= $GLOBALS['pub_id'] ?>&tab=scbTab" target="_blank" rel="noreferrer" >Sensitive Categories</a> <br />
	<a href="https://www.google.com/adsense/new/u/0/<?= $GLOBALS['pub_id'] ?>/main/allowAndBlockAds?webPropertyCode=ca-<?= $GLOBALS['pub_id'] ?>&tab=adnetworksTab" target="_blank" rel="noreferrer" >Ad Networks</a> <br />
	<a href="https://www.google.com/adsense/new/u/0/<?= $GLOBALS['pub_id'] ?>/main/allowAndBlockAds?webPropertyCode=ca-<?= $GLOBALS['pub_id'] ?>&tab=adServingTab" target="_blank" rel="noreferrer" >Ad Serving</a> <br />
	<a href="https://www.google.com/adsense/new/u/0/<?= $GLOBALS['pub_id'] ?>/opt/experiments" target="_blank" rel="noreferrer" >Experiments</a> <br />
	<a href="https://www.google.com/adsense/new/u/0/<?= $GLOBALS['pub_id'] ?>/myads/adbalance" target="_blank" rel="noreferrer" >Ad Balance</a> <br />

	</div>

<?php } ?>

</div>


</div>
</div>

<div class="positoin2 nav_top">

	<div class="left_right" onclick="move('first', 'left');" >
	⇐
	</div>

	<div class="left_right" onclick="move('first', 'right');" >
	⇒
	</div>

</div>

<div class="positoin3 nav_top">

	<div class="left_right" onclick="move('first', '1');" >
	Main Settings
	</div>

	<div class="left_right" onclick="move('first', '2');" >
	Stop Words
	</div>

	<div class="left_right" onclick="move('first', '3');" >
	Auth and Other
	</div>

</div>


<div class="ver">v<?= $ver ?></div>
<br class="clear" />

<?= $html_sep ?>
<?php /* reports/*sep*/ ?>

<div class="second reports">

<div class="first_bot" id="first_bot" style="right: 0%; ">


<div class="b_left_colulmn colulmns_bot">

	<h3>Blocked for Disguised <a onclick="close_ad_column('disguised');" href="delete.php?folder=disguised" target="working_frame" title="Delete all ads in this group" ><img src="img/trash.gif" /></a></h3>

    <div id="ads_list_disguised">
<?php echo get_ad_list('disguised'); ?>
    </div>


	<h3>Blocked for Redirect <a onclick="close_ad_column('redirect');" href="delete.php?folder=redirect" target="working_frame" title="Delete all ads in this group" ><img src="img/trash.gif" /></a></h3>

    <div id="ads_list_redirect">
<?php echo get_ad_list('redirect'); ?>
    </div>

	<h3>Blocked for Blogspot <a onclick="close_ad_column('blogspot');" href="delete.php?folder=blogspot" target="working_frame" title="Delete all ads in this group" ><img src="img/trash.gif" /></a></h3>

    <div id="ads_list_blogspot">
<?php echo get_ad_list('blogspot'); ?>
    </div>



</div>

<div class="b_center_colulmn colulmns_bot">

	<h3>Blocked for bad Word <a onclick="close_ad_column('word');" href="delete.php?folder=word" target="working_frame" title="Delete all ads in this group" ><img src="img/trash.gif" /></a></h3>

    <div id="ads_list_word">
<?php echo get_ad_list('word'); ?>
    </div>

</div>


<div class="b_right_colulmn colulmns_bot">

	<h3>Clear <a onclick="close_ad_column('clear');" href="delete.php?folder=clear" target="working_frame" title="Delete all ads in this group" ><img src="img/trash.gif" /></a></h3>

    <div id="ads_list_clear">
<?php echo get_ad_list('clear'); ?>
    </div>

</div>


</div>
</div>

<div class="positoin2 nav_bottom">

	<div class="left_right" onclick="move('first_bot', 'left_bot');" >
	⇐
	</div>

	<div class="left_right" onclick="move('first_bot', 'right_bot');" >
	⇒
	</div>

</div>

<div class="positoin3 nav_bottom">

	<div class="left_right" onclick="move('first_bot', '1_bot');" >
	Blocked for Other Reasons
	</div>

	<div class="left_right" onclick="move('first_bot', '2_bot');" >
	Blocked for bad Word
	</div>

	<div class="left_right" onclick="move('first_bot', '3_bot');" >
	Clear
	</div>

</div>

<?= $html_sep ?>
<?php /* footer/*sep*/ ?>



</body>
</html>

