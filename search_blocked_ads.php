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
    create_log($first_result, 's1.');

unset($first_result);

/**
 * Get first access tokens:
 **/

if ($set['arc'] == 'arc5') {
    get_xsrf_token_new();
} else {
    get_xsrf_token();
}



if ($set['arc'] == 'arc5') {
    @unlink($GLOBALS['temp_folder'] . 'some_long_token.txt');
    @unlink($GLOBALS['temp_folder'] . 'some_digi_token.txt');
}

if(!isset($start_pos))
    if(isset($set['start_pos']))
        $start_pos = $set['start_pos'];




$checked = 0; //Count of checked ads
$blocked = 0; //Count of blocked ads
$no_ads = '';

$set['num_of_pages'] = 1;
$set['num_of_ads_per_page'] = 95;

for ($i = 0; $i < $set['num_of_pages']; $i++) {

/**
            $type['text'] = '0';
            $type['media'] = '2';
*/
        $media_types = '[0,2]';

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
            @$params->{2}->{3} = 1; // 0 - Shown ads; 1 - already blocked ads
            @$params->{2}->{4}->{1}->{1} = $some_long_token;
//            if ($search_word)
//                @$params->{2}->{5}->{1} = $search_word; //Search word (ARC word input)
//            if (!isset($set['reviewed']))
                @$params->{2}->{5}->{2} = 1; //Reviewed ads
//            if (isset($set['predicted']))
//                @$params->{2}->{5}->{3} = 1; //Predicted block
//            if (isset($set['last_of_days']))
//                if ($set['last_of_days'])
//                    @$params->{2}->{5}->{6} = $set['last_of_days']; //Show ads for last some days.
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
//            if (isset($set['reviewed']))
//                @$params->{2}->{3} = 10;
//            else
                @$params->{2}->{3} = 1; // 10 - Reviewed ads; 11 - not rewieved ads; 1 - stats checked and blocked ads. Usless info.
//            if (isset($set['last_of_days']))
//                if ($set['last_of_days'])
//                    @$params->{2}->{5}->{6} = $set['last_of_days']; //Show ads for last some days  (same as old ARC)
            if (!$search_word)
                @$params->{2}->{5}->{16} = $media_types; // 0 - Text; 1 - graphics; 2 - Media;
//            if ($search_word)
//                @$params->{2}->{5}->{24} = array($search_word); //Search word (ARC word input)
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
            //$ad[$key]['adv_name'] = $node->{5}->{17}; // advertiser name
            //$ad[$key]['adv_id'] = $node->{5}->{20}; // advertiser id
            $ad[$key]['url'] = $node->{5}->{14};
            //$ad[$key]['url_displayed'] = $node->{5}->{15};
            //$ad_id[] = $ad[$key]['ad_id'] = $node->{4}->{1}; // Some sequence required to control requests of each ad; long ad id
            //$ad[$key]['adv_long_id'] = $node->{10}->{1}; // Some sequence required to control requests of ad acconut; long adv id
           // $ad[$key]['digikey'] = $digikey_for_req;
            //if ($ad_type == 'Image')
            //    $ad[$key]['header2'] = $ad[$key]['url_displayed'];
            //set_time_limit(900);		//Renew time limit for each ad download
        }

        unset($result, $ad_req_urls);

        foreach ($ad as $index => $adunit) {

            if ($adunit['fulltext']) {


                if(mb_stripos($adunit['url'], 'google.com', 0, 'UTF-8') !== false
                || mb_stripos($adunit['url'], 'youtube.com', 0, 'UTF-8') !== false
                || mb_stripos($adunit['url'], 'itunes.com', 0, 'UTF-8') !== false
                || mb_stripos($adunit['url'], 'vk.com', 0, 'UTF-8') !== false
                || mb_stripos($adunit['url'], 'youtu.be', 0, 'UTF-8') !== false

                )
                    continue;
/**
                if(mb_stripos($adunit['url'], 'google.com', 0, 'UTF-8') !== false
                || mb_stripos($adunit['url'], 'youtube.com', 0, 'UTF-8') !== false
                || mb_stripos($adunit['url'], 'itunes.com', 0, 'UTF-8') !== false
                || mb_stripos($adunit['url'], 'tilda.ws', 0, 'UTF-8') !== false
                || mb_stripos($adunit['url'], 'vk.com', 0, 'UTF-8') !== false
                || mb_stripos($adunit['url'], 'youtu.be', 0, 'UTF-8') !== false
                || mb_stripos($adunit['url'], 'teletype.in', 0, 'UTF-8') !== false
                || mb_stripos($adunit['url'], 'myflexbe.com', 0, 'UTF-8') !== false
                || mb_stripos($adunit['url'], 'myflexbe.ru', 0, 'UTF-8') !== false
                || mb_stripos($adunit['url'], 'site123.me', 0, 'UTF-8') !== false
                || mb_stripos($adunit['url'], 'multiscreensite.com', 0, 'UTF-8') !== false

*/

               $adunit = str_replace("\n", ' ', $adunit);
               $adunit = str_replace("\r", ' ', $adunit);


              if(!isset($adunit['header1']) && !isset($adunit['header2'])) {
                    $headers = '<span title="Mf62">' . $adunit['body'] . '</span>';
                } else {
                    $headers = '<span title="' . $adunit['body'] . '">' . $adunit['header1'] . ' ' . $adunit['header1'] . '</span>';
                }



                $domain = parse_url($adunit['url'], PHP_URL_HOST);

                $domains_array[$domain] = $headers . '##!$@$!##' . $adunit['url'];

                $checked++;
            }
        }


        unset($ad, $ad_id);
    }

if(file_exists(__DIR__ . '/../../1_other/howgadget/bad_ads_blocker/domains/list.html'))
    $output_strings = file(__DIR__ . '/../../1_other/howgadget/bad_ads_blocker/domains/list.html', FILE_IGNORE_NEW_LINES);
else
    $output_strings = array();

$domains_array = array_reverse ($domains_array, TRUE);

foreach ($domains_array as $domain => $adunit_data) {

    $list = explode('##!$@$!##', $adunit_data);

    $headers = $list[0];
    $adunit_url = $list[1];

    $domain = '<span title="' . $adunit_url . '">' . $domain . '</span>';

    array_unshift($output_strings, $domain . '&nbsp;' . $headers);

}

$output_strings = array_unique($output_strings);

$limit = rand(388, 443);

if(count($output_strings) > $limit) {
    $output_strings = array_slice ($output_strings, 0, $limit);
}


$output_strings_str = implode("\n", $output_strings);

file_put_contents(__DIR__ . '/../../1_other/howgadget/bad_ads_blocker/domains/list.html', $output_strings_str);

$blocked_domains = array('.ua-shop.', '.ucraft.', '.jimdosite.', '.bitrix24.', '.sejl.', '.usluga.', '.fanana.', '.ulcraft.', '.n.', '.creatium.',
'.cloudfront.', '.ukit.', '.na4u.', '.sitelio.', '.mbmproxy.', '.webnode.', '.teletype.', '.multiscreensite.', '.doodlekit.', '.mycindr.', '.bookmark.',
'.strikingly.', '.tumblr.', '.moonfruit.', '.yolasite.', '.mozello.', '.emyspot.', '.nethouse.', '.website2.', '.plp7.', '.weblium.', '.wixsite.',
'.alltrades.', '.herokuapp.', '.mobirisesite.', '.bravesites.', '.zyrosite.', '.uflorist.', '.tilda.', '.myflexbe.', '.site123.', 'ites.google.', 
'.ugo.', '.godaddysites.', '.000webhostapp.', '.github.', '.beget.', '.blogspot.', '.webflow.', '.urest.', '.bitrix24site.');


$domain_table = $headers_table = $domain_string = '';
foreach ($output_strings as $index => $output_string) {

    $list = explode('&nbsp;', $output_string);

    $domain = $list[0];
    $headers = $list[1];

    $hidden_class = '';
    foreach ($blocked_domains as $blocked_domain) {
        if(mb_stripos($domain, $blocked_domain, 0, 'UTF-8') !== false) {
            $hidden_class = ' class="hidden"';
            break;
        }
    }

    $domain_table .= "<tr$hidden_class><td>" . $domain . "</td></tr>\n";
    $headers_table .= "<tr$hidden_class><td>" . $headers . "</td></tr>\n";
    $domain_string .= "<span$hidden_class>" . $domain . ", </span>";
    

}

$domain_table = '<table class="output_table" >' . $domain_table . "</table>\n";
$headers_table = '<table class="output_table" >' . $headers_table . "</table>\n";
$domain_string = '<div class="output_string" >' . $domain_string . "</div><br /><br />\n";


file_put_contents(__DIR__ . '/../../1_other/howgadget/bad_ads_blocker/domains/output_tables.html', $domain_string . $domain_table . $headers_table);


if ($no_ads) {
    unset($meta_continue, $cycle_report);
}




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
<?= $output_strings ?>

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
