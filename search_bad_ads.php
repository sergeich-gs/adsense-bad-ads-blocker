<?php //—
$start_time = time();
include_once 'functions.php';

if (!is_still_log_in()) {
    die('You should log in to Google first');
}

if (isset($argv)) {

    if (strpos($argv[1], '!!!') !== false)
        $override = 1;
    $argv[1] = str_replace('!!!', '', $argv[1]);

    $json_check = substr($argv[1], 0, 1);

    if ($json_check == '{') {
        $new_set = json_decode($argv[1], 1);
        if ($override)
            $set = $new_set;
        else
            foreach ($new_set as $index => $value)
                $set[$index] = $value;

    } else {
        if ($argv[1]) {
            $set['reviewed'] = '1';
            $set['no_save_clear'] = '1';
        }
        //else 			{ unset($set['reviewed']); }
        if ($argv[2])
            $set['num_of_ads_per_page'] = $argv[2];
        if ($argv[3])
            $set['num_of_pages'] = $argv[3];
    }

    $GLOBALS['set_gl'] = $set;

    $md5string = '';
    foreach ($set as $value)
        $md5string .= $value;

    $md5string = md5($md5string);
    $md5string = substr($md5string, 5, 5);

    $cookie = file_get_contents($GLOBALS['cookie_file']);
    $GLOBALS['cookie_file'] = $GLOBALS['temp_folder'] . 'cookie.cron.' . $md5string . '.txt';
    if (!file_exists($GLOBALS['cookie_file']))
        file_put_contents($GLOBALS['cookie_file'], $cookie);
    $GLOBALS['xsrftoken_file'] = $GLOBALS['temp_folder'] . 'xsrftoken.cron.' . $md5string . '.txt';

    if (!is_still_log_in()) {
        unlink($GLOBALS['cookie_file']);
        die();
    }
}

if ($set['arc'] == 'arc5')
    $GLOBALS['arc_tab_req_string'] = $GLOBALS['new_arc_tab_req_string'];

$first_result = curl_get($GLOBALS['arc_tab_req_string'], '', '');

if (isset($set['log']))
    file_put_contents($GLOBALS['temp_folder'] . 'logs/s1.' . time(), $first_result);

unset($first_result);

/**
 * Get first access tokens:
 **/

if ($set['arc'] == 'arc5') {
    get_xsrf_token_new();
} else {
    get_xsrf_token();
}


if (isset($set['searchwords_check'])) {
    @$search_words = file($GLOBALS['settings_folder'] . 'search_words.txt', FILE_IGNORE_NEW_LINES);
    $GLOBALS['set_gl']['reviewed'] = $set['reviewed'] = true; //search not only new ads
    if (isset($set['badadlist_check']))
        unset($set['badadlist_check']);
    if (isset($set['stopwords_check']))
        unset($set['stopwords_check']);
    if (isset($set['mark_reviewed']))
        unset($set['mark_reviewed']);
    if ($set['arc'] == 'arc5')
        $set['reviewed'] = true;
} else
    $search_words[] = '';

/*
if(isset($set['badadlist_check']))
if(file_exists($GLOBALS['settings_folder'].'bad_ads_text.txt')){
$badadlistwords=file($GLOBALS['settings_folder'].'bad_ads_text.txt', FILE_IGNORE_NEW_LINES);
}
*/

if (isset($set['stopwords_check'])) {
    if (isset($set['text']) || isset($set['image']))
        $stopwords_text = file($GLOBALS['settings_folder'] . 'stopwords_text.txt', FILE_IGNORE_NEW_LINES);
    if (isset($set['rich_media']))
        $stopwords_media = file($GLOBALS['settings_folder'] . 'stopwords_media.txt', FILE_IGNORE_NEW_LINES);
    /*
    if(isset($badadlistwords))
    if(is_array($badadlistwords))
    foreach($badadlistwords as $badadlistword)
    $stopwords_text[]=$badadlistword;

    */
}

/*
if( isset($set['stopwords_check']) && isset($set['badadlist_check']) )
$stopwords_text=array_unique($stopwords_text);
*/

if ($set['arc'] == 'arc5') {
    @unlink($GLOBALS['temp_folder'] . 'some_long_token.txt');
    @unlink($GLOBALS['temp_folder'] . 'some_digi_token.txt');
}

$checked = 0; //Count of checked ads
$blocked = 0; //Count of blocked ads
$no_ads = '';

foreach ($search_words as $search_word)
    for ($i = 0; $i < $set['num_of_pages']; $i++) {

        if (isset($set['text']))
            $type['text'] = '0';
        if (isset($set['image']))
            $type['image'] = '1';
        if (isset($set['rich_media']))
            $type['media'] = '2';
        if (isset($set['rtb']))
            $type['rtb'] = '4';
        $media_types = '[' . implode(',', $type) . ']';

        if ($set['arc'] != 'arc5') { // Old ARC
            /**
             * Get temporary access tokens from old ARC:
             **/

            $params = new stdClass();
            @$params->{1} = 'ca-' . $GLOBALS['pub_id'];
            $params = json_encode($params);
            $result = creative_review('getWebPropertyMetricsToken', $params);
            if (@is_object($result->error))
                die('<p>' . $result->error->code . ' ' . $result->error->message . '</p>');
            $some_long_token = $result->result->{1}->{1}->{1}; // some auth token...
            unset($params);

            /**
             * Get ads list from old ARC:
             **/
            $params = new stdClass();
            @$params->{1} = 'ca-' . $GLOBALS['pub_id']; // Publisher Id ca-pub-...
            if (isset($set['reviewed']))
                @$params->{2}->{1} = $start_pos + ($i * $set['num_of_ads_per_page']); // Start position
            else
                if (!isset($set['mark_reviewed']))
                    @$params->{2}->{1} = $start_pos + ($i * $set['num_of_ads_per_page']); // Start position
                else
                    @$params->{2}->{1} = 0; // Start position
            @$params->{2}->{2} = (int)$set['num_of_ads_per_page']; // Ads qty
            @$params->{2}->{3} = 0; // 0 - Shown ads; 1 - already blocked ads
            @$params->{2}->{4}->{1}->{1} = $some_long_token;
            if ($search_word)
                @$params->{2}->{5}->{1} = $search_word; //Search word (ARC word input)
            if (!isset($set['reviewed']))
                @$params->{2}->{5}->{2} = 1; //Reviewed ads
            if (isset($set['predicted']))
                @$params->{2}->{5}->{3} = 1; //Predicted block
            if (isset($set['last_of_days']))
                if ($set['last_of_days'])
                    @$params->{2}->{5}->{6} = $set['last_of_days']; //Show ads for last some days.
            if (!$search_word)
                @$params->{2}->{5}->{16} = $media_types; // 0 - Text; 1 - graphics; 2 - Media; If checked all types the parameter is absent.
            if (!$search_word)
                if (!isset($set['rich_media']))
                    @$params->{2}->{5}->{17} = 0; //Disappears at turning on media type
            //$params->{3}='-6928690776790362';						// It seems we and Google do not need it
            $params = json_encode($params, JSON_UNESCAPED_UNICODE);
            $params = str_replace('"[', '[', $params);
            $params = str_replace(']"', ']', $params);

            $result = creative_review('searchArcApprovals', $params);
            $result_keyword = 'result';

        } else { // New ARC

            /**
             * Get ads list from new ARC:
             **/
            $params = new stdClass();
            @$params->{1} = 'ca-' . $GLOBALS['pub_id']; // Publisher Id ca-pub-...  (same as old ARC)
            if (isset($set['reviewed']))
                @$params->{2}->{1} = $start_pos + ($i * $set['num_of_ads_per_page']); // Start position (same as old ARC)
            else
                if (!isset($set['mark_reviewed']))
                    @$params->{2}->{1} = $start_pos + ($i * $set['num_of_ads_per_page']); // Start position (same as old ARC)
            if (@$params->{2}->{1} == 0)
                unset($params->{2}->{1});
            @$params->{2}->{2} = (int)$set['num_of_ads_per_page']; // Ads qty (same as old ARC)
            if (isset($set['reviewed']))
                @$params->{2}->{3} = 10;
            else
                @$params->{2}->{3} = 11; // 10 - Reviewed ads; 11 - not rewieved ads; 1 - stats checked and blocked ads. Usless info.
            if (isset($set['last_of_days']))
                if ($set['last_of_days'])
                    @$params->{2}->{5}->{6} = $set['last_of_days']; //Show ads for last some days  (same as old ARC)
            if (!$search_word)
                @$params->{2}->{5}->{16} = $media_types; // 0 - Text; 1 - graphics; 2 - Media;
            if ($search_word)
                @$params->{2}->{5}->{24} = array($search_word); //Search word (ARC word input)
            $params->{2}->{7} = '';
            if (file_exists($GLOBALS['temp_folder'] . 'some_long_token.txt'))
                $params->{2}->{7} = file_get_contents($GLOBALS['temp_folder'] . 'some_long_token.txt'); // Some long token from prev. request response to continue paging ads.
            if (file_exists($GLOBALS['temp_folder'] . 'some_digi_token.txt'))
                $params->{3} = file_get_contents($GLOBALS['temp_folder'] . 'some_digi_token.txt'); // Some digit token from prev. request response
            $params->{5} = true; // Just true
            $params = json_encode($params, JSON_UNESCAPED_UNICODE);
            $params = str_replace('"[', '[', $params);
            $params = str_replace(']"', ']', $params);

            $result = creative_review_new('SearchApprovals', $params);
            $result_keyword = 'default';

        }

        if (@is_object($result->error))
            die('<p>' . $result->error->code . ' ' . $result->error->message . '</p>');

        if (@$result->{$result_keyword}->{8} === "0") { //If no ads at server response then stop ads loading
            $by_word = '';
            if ($search_word)
                $by_word = ' by: ' . $search_word;
            $no_ads .= "No ads left$by_word<br>\n";
            break;
        }

        $digikey_for_req = $result->{$result_keyword}->{5}; // Some digits required to control requests.
        //var_dump($result);
        foreach ($result->{$result_keyword}->{1} as $key => $node) {
            $ad_req_urls = $node->{5}->{13}; // url we can access ad sourse code
            $ad_type = get_ad_type($node->{5}->{6}); //get type of ad (Text, Rich Media, etc)
            $ad[$key] = get_ad($ad_req_urls, $ad_type); //Indexes of returned array: fulltext, header1, header2, body
            $ad[$key]['adv_name'] = $node->{5}->{17}; // advertiser name
            $ad[$key]['adv_id'] = $node->{5}->{20}; // advertiser id
            $ad[$key]['url'] = $node->{5}->{14};
            $ad[$key]['url_displayed'] = $node->{5}->{15};
            $ad_id[] = $ad[$key]['ad_id'] = $node->{4}->{1}; // Some sequence required to control requests of each ad; long ad id
            $ad[$key]['adv_long_id'] = $node->{10}->{1}; // Some sequence required to control requests of ad acconut; long adv id
            $ad[$key]['digikey'] = $digikey_for_req;
            if ($ad_type == 'Image')
                $ad[$key]['header2'] = $ad[$key]['url_displayed'];
            //set_time_limit(900);		//Renew time limit for each ad download
        }

        unset($result, $ad_req_urls);

        foreach ($ad as $index => $adunit) {

            if ($adunit['fulltext']) {

                if (isset($set['whitelist']))
                    if (is_ad_whitelisted($adunit['fulltext'] . ' ' . $adunit['adv_name'] . ' ' . $adunit['adv_id'] . ' ' . $adunit['url']))
                        continue;

                if ($search_word) {
                    $found['word'] = 1;
                    $adunit['stopword'] = 'S->' . $search_word;
                    $adunit['filter'] = 'words';
                    goto list_ad;
                }

                if ($adunit['type'] == 't') {
                    $stopwords = $stopwords_text;
                    if (isset($set['disguised_text']))
                        $set['disguised'] = true;
                    if (isset($set['redirects_text']))
                        $set['redirects'] = true;
                } else {
                    $stopwords = $stopwords_media;
                    if (isset($set['disguised_media']))
                        $set['disguised'] = true;
                    if (isset($set['redirects_media']))
                        $set['redirects'] = true;
                }
                if ($adunit['type'] == 'Img') {
                    $stopwords = $stopwords_text;
                }

                $found['blogspot'] = 0;
                if (isset($set['blogspot'])) {
                    if (stripos($adunit['url'], 'blogspot.com') !== false || stripos($adunit['url'], 'blogspot.ru') !== false) { //if we can find blogspot
                        $found['blogspot'] = 1;
                        $adunit['filter'] = 'blogspot';
                        goto list_ad;
                    }
                }

                $found['disguised'] = 0;
                if (isset($set['disguised'])) {
                    $stopword = find_disguised_latin($adunit);
                    if ($stopword) { //if we can find any another domain redirect
                        $found['disguised'] = 1;
                        $adunit['stopword'] = $stopword;
                        $adunit['filter'] = 'disguised';
                        goto list_ad;
                    }
                }

                $found['word'] = 0;
                if (isset($set['stopwords_check']) || isset($set['badadlist_check'])) {

                    foreach ($stopwords as $stopword) {
                        $fulltext = $adunit['fulltext'];
                        if (isset($set['lat2cyr']))
                            $fulltext .= ' ' . lat_replace($fulltext);
                        if (isset($set['check_target_url']))
                            $fulltext .= ' ' . $adunit['url'];

                        if (mb_stripos($stopword, '!!!', 0, 'UTF-8') === 0) {
                            $stopword = str_replace('!!!', '', $stopword);
                            if (preg_match('/[ .!?"\',;:–—‒―1-9-]' . $stopword . '[ .!?"\',;:–—‒―1-9-]/iu', $fulltext)) {
                                $found['word'] = 1;
                                $adunit['stopword'] = '!' . $stopword;
                                $adunit['filter'] = 'word';
                                goto list_ad;
                            }
                        } else {
                            if (mb_stripos($fulltext, $stopword, 0, 'UTF-8') !== false) { //if we can find any bad word
                                $found['word'] = 1;
                                $adunit['stopword'] = $stopword;
                                $adunit['filter'] = 'word';
                                goto list_ad;
                            }
                        }
                    }
                }

                $found['redirect'] = 0;
                if (isset($set['redirects'])) {
                    $redirect_reason = count_redirects($adunit['url']);
                    if ($redirect_reason) { //if we can find any another domain redirect
                        $found['redirect'] = 1;
                        $adunit['stopword'] = $redirect_reason;
                        $adunit['filter'] = 'redirect';
                        goto list_ad;
                    }
                }


                list_ad : if ($found['word'] || $found['redirect'] || $found['blogspot'] || $found['disguised']) {
                    block_ad($ad_id[$index], $digikey_for_req, 0);
                    if (isset($set['ad_account']))
                        block_ad_account($ad_id[$index], 0, $adunit['header1'] . ' ' . $adunit['header2'], $adunit['adv_id'], $adunit['adv_name']);
                    if (isset($set['ad_domain'])) {
                        $ad_domain = parse_url($adunit['url'], PHP_URL_HOST);
                        if ($ad_domain != 'play.google.com')
                            add_blocked_url($ad_domain);
                    } elseif (isset($set['ad_url']))
                        add_blocked_url($adunit['url']);

                    if ($set['arc'] == 'arc5') {
                        if (isset($set['report_words']))
                            if ($adunit['filter'] == 'word')
                                 ReportPolicyViolation($ad_id[$index], rand(1,12));
                        if (isset($set['report_disg']))
                            if ($adunit['filter'] == 'disguised')
                                 ReportPolicyViolation($ad_id[$index], rand(1,12));
                        if (isset($set['report_redir']))
                            if ($adunit['filter'] == 'redirect')
                                 ReportPolicyViolation($ad_id[$index], rand(1,12));
                    }

                    list_ad($adunit, $index, $found);
                    $blocked++;
                } else {
                    if (!isset($set['no_save_clear']))
                        list_ad($adunit, $index, 0);
                }
                $checked++;
                unset($set['disguised'], $set['redirects'], $found);

            }
        }

        if (isset($set['mark_reviewed']))
            mark_ads_reviewed($ad_id);
        unset($ad, $ad_id);
    }

if ($no_ads) {
    unset($meta_continue, $cycle_report);
}

$unblocked_count = '';
if (isset($set['acc_age']))
    if ($set['acc_age'])
        $unblocked_count = unblock_old_accounts($set['acc_age']);
if ($unblocked_count)
    $unblocked_count = "<p>Unblocked $unblocked_count AdWords accounts by age.</p>";

$old_files_removed = '';
$old_files_removed = remove_old_files(90); //90 days
if ($old_files_removed)
    $old_files_removed = "<p>Removed $old_files_removed old files.</p>";


$body_onload = '';
if (!isset($meta_continue)) {
    $meta_continue = '';
    if (@mb_stripos($_SERVER['HTTP_REFERER'], 'separate.php', 0, 'UTF-8') !== false)
        $body_onload = ' onload="parent.document.location.href=\'' . $_SERVER['HTTP_REFERER'] . '\';"';
}

if (!isset($a_continue))
    $a_continue = '';
if (!isset($cycle_report))
    $cycle_report = '';


$exe_time = time() - $start_time;
$exe_time = date("i:s", $exe_time); ?>
<!DOCTYPE html>
<html>
<head>

<title>Bad ads searcher</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="referrer" content="same-origin" />
<meta name="robots" content="noindex, nofollow" />
<link href="img/favicon.ico" rel="shortcut icon" type="image/x-icon" />
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=4, minimum-scale=0.1" />
<?= $meta_continue ?>
<style>
* { font-size: 20px;  font-family: Calibri, Verdana, Arial; }
body { background: #fff; }
</style>
</head>
<body<?= $body_onload ?>>

<p>
<?= $no_ads ?>
Execution time: <?= $exe_time ?>.<br>
Checked: <?= $checked ?>.<br>
Blocked: <?= $blocked ?>.
</p>
<?= $cycle_report ?>
<?= $a_continue ?>
<?= $unblocked_count ?>
<?= $old_files_removed ?>

<p>You can <a href="<?= $_SERVER['HTTP_REFERER'] ?>" target="_parent">refresh the page</a> to view results.</p>

<p title="memory_get_peak_usage()" >Memory used: <?php echo round(memory_get_peak_usage(true) / 1024 / 1024, 2) . ' MB'; ?></p>


</body>
</html>