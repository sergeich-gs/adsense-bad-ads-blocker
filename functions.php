<?php //—
ini_set('max_execution_time', 600); //600 seconds = 10 minutes
ini_set('memory_limit', '250M');
ini_set('short_open_tag', 1);

$GLOBALS['settings_folder'] = __DIR__ . '/settings/';
$GLOBALS['temp_folder'] = __DIR__ . '/tempdata/';
@$GLOBALS['useragent'] = file_get_contents($GLOBALS['temp_folder'] . 'useragent.txt');
$GLOBALS['cookie_file'] = $GLOBALS['temp_folder'] . 'cookie.txt';
$GLOBALS['xsrftoken_file'] = $GLOBALS['temp_folder'] . 'xsrftoken.txt';
@$GLOBALS['pub_id'] = trim(file_get_contents($GLOBALS['temp_folder'] . 'pub_id.txt'));
$GLOBALS['m_useragent'] = 'Mozilla/5.0 (iPhone; CPU iPhone OS 11_0 like Mac OS X) AppleWebKit/604.1.34 (KHTML, like Gecko) Version/11.0 Mobile/15A5341f Safari/604.1';
$GLOBALS['creative_review_req_string'] = 'https://www.google.com/adsense/gp/creativeReview?ov=3&pid=' . $GLOBALS['pub_id'] . '&authuser=0&tpid=' . $GLOBALS['pub_id'] . '&hl=en';
$GLOBALS['blocking_controls_req_string'] = 'https://www.google.com/adsense/gp/blockingControls?ov=3&pid=' . $GLOBALS['pub_id'] . '&authuser=0&tpid=' . $GLOBALS['pub_id'] . '&hl=en';
$GLOBALS['arc_tab_req_string'] = 'https://www.google.com/adsense/new/u/0/' . $GLOBALS['pub_id'] . '/main/allowAndBlockAds?webPropertyCode=ca-' . $GLOBALS['pub_id'] . '&tab=arcTab&hl=en';
$GLOBALS['new_arc_tab_req_string'] = 'https://www.google.com/adsense/new/u/0/' . $GLOBALS['pub_id'] . '/arc/ca-' . $GLOBALS['pub_id'] . '?hl=en';;
$GLOBALS['myheaders'] = array('accept-language:en-US;q=1,en;q=0.4', 'content-type:application/javascript; charset=UTF-8');
$GLOBALS['myheaders_new'] = array('accept-language:en-US;q=1,en;q=0.4', 'content-type:application/json;charset=UTF-8');
$GLOBALS['creative_review_new_string'] = '/ads-publisher-controls/acx/5/proto/creativereview/';

if (!file_exists($GLOBALS['temp_folder']))
    mkdir($GLOBALS['temp_folder'], 0775);

if (!file_exists($GLOBALS['temp_folder'] . '.htaccess'))
    file_put_contents($GLOBALS['temp_folder'] . '.htaccess', "Options All -Indexes\nDeny from all");

if (!file_exists($GLOBALS['settings_folder']) || !is_writeable($GLOBALS['settings_folder'])) {
    die('Check ' . $GLOBALS['settings_folder'] . ' directory write permissions');
}

if (!file_exists($GLOBALS['temp_folder']) || !is_writeable($GLOBALS['temp_folder'])) {
    die('Check ' . $GLOBALS['temp_folder'] . ' directory write permissions');
}



if (!file_exists($GLOBALS['temp_folder'] . 'logs'))
    mkdir($GLOBALS['temp_folder'] . 'logs', 0775);

if (!file_exists($GLOBALS['temp_folder'] . 'autoblocked_accs'))
    mkdir($GLOBALS['temp_folder'] . 'autoblocked_accs', 0775);

if (!file_exists($GLOBALS['temp_folder'] . 'autoblocked_urls'))
    mkdir($GLOBALS['temp_folder'] . 'autoblocked_urls', 0775);

if (!file_exists($GLOBALS['temp_folder'] . 'accs_ads'))
    mkdir($GLOBALS['temp_folder'] . 'accs_ads', 0775);

if (!file_exists($GLOBALS['temp_folder'] . 'domains_create'))
    mkdir($GLOBALS['temp_folder'] . 'domains_create', 0775);


//if(php_sapi_name()!='cli')
if (empty($argc))
    if (file_exists($GLOBALS['temp_folder'] . 'pass'))
        if (file_get_contents($GLOBALS['temp_folder'] . 'pass') != '') {
            $keyname = substr(md5(dirname(__file__)), 0, 5) . '_key';
            if (!isset($_COOKIE[$keyname]) || $_COOKIE[$keyname] != @file_get_contents($GLOBALS['temp_folder'] . 'auth_key')) { //Autentification if password not empty
                header("Location: auth.php");
                exit;
            }
        }


if (file_exists($GLOBALS['settings_folder'] . 'settings.ini')) {
    $set = file_get_contents($GLOBALS['settings_folder'] . 'settings.ini');
    $set = json_decode($set, 1);
} else {
    $set['num_of_pages'] = 1;
    $set['num_of_ads_per_page'] = 30;
    $set['text'] = 'checked';
    $set['stopwords_check'] = 'checked';
    $set['mark_reviewed'] = 'checked';
    $set['get_stats'] = 'checked';
    $set['arc'] = 'arc5';
}

if (isset($set['log'])) {
    ini_set('error_reporting', E_ALL);
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    create_log(json_encode($set), 'settings.');
} else {
    ini_set('error_reporting', 0);
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
}

if (isset($set['arc5'])) {
    $set['arc'] = 'arc5';
} else {
    if (!isset($set['arc'])) {
        $set['arc'] = 'old_arc';
    }
}


$GLOBALS['set_gl'] = $set;
$nl = "\n";


if (isset($set['redirects_text']) || isset($set['redirects_media']))
    $GLOBALS['level3domains'] = file($GLOBALS['settings_folder'] . 'level3domains.txt', FILE_IGNORE_NEW_LINES);

if(isset($set['whitelist']))
    if ($set['whitelist'])
        $GLOBALS['whitelist'] = file($GLOBALS['settings_folder'] . 'whitelist.txt', FILE_IGNORE_NEW_LINES);

$set['pub_id_adsense'] = $GLOBALS['pub_id'];
if ($set['arc'] == 'adx') {
    //@$GLOBALS['pub_id']=file_get_contents($GLOBALS['temp_folder'].'pub_id.txt');        // pub_id_adx
    $GLOBALS['pub_id'] = $set['pub_id_adx'];
    @$GLOBALS['nc'] = file_get_contents($GLOBALS['temp_folder'] . 'nc_adx.txt');
    if ($GLOBALS['nc'] == '') {
        $GLOBALS['nc'] = get_network_code_for_adx();
        file_put_contents($GLOBALS['temp_folder'] . 'nc_adx.txt', $GLOBALS['nc']);
    }
    $GLOBALS['arc_tab_req_string'] = 'https://admanager.google.com/' . $GLOBALS['nc'];
    $GLOBALS['creative_review_req_string'] = 'https://admanager.google.com/ads-publisher-controls/drx/4/gp/creativeReview?pc=ca-' . $GLOBALS['pub_id'] . '&nc=' . $GLOBALS['nc'] . '&hl=en';
    $GLOBALS['blocking_controls_req_string'] = 'https://admanager.google.com/ads-publisher-controls/drx/4/gp/blockingControls?pc=ca-' . $GLOBALS['pub_id'] . '&nc=' . $GLOBALS['nc'] . '&hl=en';
}


/**
 *
**/


function is_data_safely($input_data)
{
    if (strpos($input_data, "<?") !== false)
        return false;
    if (strpos($input_data, "?>") !== false)
        return false;
    if (strpos($input_data, "strtolower") !== false)
        return false;
    if (strpos($input_data, "strtoupper") !== false)
        return false;
    if (strpos($input_data, "{") !== false)
        return false;
    if (strpos($input_data, "}") !== false)
        return false;
    //if (strpos($input_data, "$") !== false)
        //return false;
    if (strpos($input_data, "strip") !== false)
        return false;
    if (strpos($input_data, "decode") !== false)
        return false;
    if (strpos($input_data, "eval") !== false)
        return false;
    if (strpos($input_data, "./") !== false)
        return false;
    return true;
}


/**
 **
**/


function get_network_code_for_adx()
{
    $result = curl_all('https://admanager.google.com/', '', $GLOBALS['myheaders'], false, '', true, false, $GLOBALS['cookie_file'], $GLOBALS['useragent']);
    $result_arr = explode("\n", $result);

    foreach ($result_arr as $header_string) {
        if (mb_strpos($header_string, 'networkCode', 20, 'UTF-8') !== false) {
            $nc_string = $header_string;
            break;
        }
    }
    $nc_string_arr = explode('?', $nc_string, 2);

    parse_str($nc_string_arr[1], $result);

    return $result['networkCode'];
}


/**
 *
**/


function metagwt2array($metahtml)
{
    $dom = new DOMDocument('1.0', 'UTF-8');
    @$dom->loadHTML($metahtml);
    foreach ($dom->getElementsByTagName('meta') as $meta_node) {
        $list = explode('=', $meta_node->getAttribute('content'));
        $name = $list[0];
        $value = $list[1];
        unset($list);
        $name = str_replace('.', '_', $name);
        $gwtarray[$name] = $value;
    }
    unset($dom);
    return $gwtarray;
}


/**
 **
**/


function hex_repl($html)
{
    $i = 256;
    while ($i >= 0) {
        $hex = dechex($i);
        $html = str_ireplace("\x$hex", chr($i), $html);
        $i--;
    }
    return $html;
}


/**
 **
**/


function get_url_from_text_ad($html)
{
    $list = explode('buildAdSlot(', $html, 2);
    $script_with = $list[1];
    unset($list);
    $list = explode(');</script>', $script_with, 2);
    $script_with = $list[0];
    unset($list);
    $array = json_decode($script_with, 1);
    unset($script_with);
    $url = str_replace('\u0026', '&', $array[0][0][4]);
    unset($array);
    return $url;
}


/**
 **
**/


function get_2level_domain($url)
{
    $url = parse_url($url);
    $domain = explode('.', $url['host']);
    $last_index = count($domain) - 1;
    $level2domain = $domain[$last_index - 1] . '.' . $domain[$last_index];

    foreach ($GLOBALS['level3domains'] as $level3domainzone) {
        if ($level3domainzone == $level2domain) {
            $level2domain = $domain[$last_index - 2] . '.' . $level2domain;
            break;
        }
    }

    unset($url, $domain, $last_index);
    $list = explode('.', $level2domain);
    return $list[0];
}


/**
 **
**/


function count_redirects($url)
{
    $domains[] = mb_strtolower(get_2level_domain($url), 'UTF-8');

    $myheaders = array('Accept:text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8', 'accept-language:ru-RU,ru;q=0.9,en-US;q=0.2,en;q=0.1', 'Cache-Control:max-age=0', 'Connection:keep-alive');

    $result = redi_curl_get($url, 'https://www.googleadservices.com/', $myheaders, false, true);
    $repl = array(" ", "\n", "\r", "\t");
    $result = str_replace($repl, '', $result);
    $result = str_replace("'", '"', $result);

    if (!preg_match('/siteh/iu', $result))
        if (preg_match('/substr\(0,2\)\.to|if\(leea/iu', $result))
            return 'js';
    if (preg_match('/<script>location.replace("http.*");</script>/iu', $result))
        return 'js2';
    $result = preg_replace('/<!--.*-->/', '', $result);
    if (preg_match('/http-equiv="refresh".{8,11}0;URL=/iu', $result))
        return 'equiv';

    if (preg_match('/[а-яёА-ЯЁ]/iu', $domains[0]))
        return false;

    $nl = '
';

    $result = redi_curl_get($url, 'https://www.googleadservices.com/', $myheaders, true, false);

    $result = explode($nl, $result);

    foreach ($result as $string) {
        if (stripos($string, 'Location') !== false)
            if (stripos($string, ': /') === false) //Some location headers do not include any domain names

                if (stripos($string, 'Content-Location') === false) {
                    $url = str_ireplace('Location:', '', $string);
                    $url = trim($url);
                    $domain = mb_strtolower(get_2level_domain($url), 'UTF-8');
                    if (trim($domain))
                        $domains[] = $domain;
                }
    }

    unset($result, $myheaders, $url);
    /*
    $count=0;
    foreach ($domains as $index => $domain) {
    if($index) {
    if($domains[$index-1]!=$domain)
    $count++;
    }
    }*/
    $found = false;
    $count = (count($domains));
    if ($count > 1) {
        if ($domains[0] != $domains[($count - 1)]) {
            $found = 'Loc';
        }
    }

    return $found;
}


/**
 **
**/


function find_disguised_latin($ad)
{
    if (isset($ad['header1']))
        $text = trim($ad['header1']);
    if (isset($ad['header2']))
        $text .= ' ' . trim($ad['header2']);
    if (isset($ad['body']))
        $text .= ' ' . trim($ad['body']);

    $to_replace = array('-', ':', ';', '{', '}', '(', ')', ',', '%', '"', '+', '–', '&', '.', '/', '\\');
    $text = str_replace($to_replace, ' ', $text);
    $text = str_replace('   ', ' ', $text);
    $text = str_replace('  ', ' ', $text);

    $words = explode(' ', $text);
    unset($text);
    $found = false;
    foreach ($words as $word) {
        if (preg_match('/([а-яёА-ЯЁ]{1,})\w*([а-яёА-ЯЁ]{1,})/u', $word))
            if (mb_strlen($word, 'UTF-8') > 3)
                if (!preg_match('/(\d{1,})\w*(\d{1,})/u', $word))
                    if (preg_match('/[aceonpxuymABCEHKMOPTXYαβγδεζηθιΧΧXẊẌҲӼӾΧΥѝκλμνξοπρσςτυφχψωқҗҳңҡҝҵҷҹӂӄӆӈӊӌӎӝӟӡӣӥӭӵӷӹӻӽӿӷӻґѓғṙṛṝṟᾐᾑᾒᾓᾔᾕᾖᾗῂῃῄῆῇἠἡἢἣἤἥἦἧὴήḣḥḧḩḫĥħϺΜϻḿṁṃṕṗҏṙṛṝṟṡṣṥṧṩṫṭṯṱẗàáâãäåāăąȁȃǎǟǡǻȧẚạảấầẩẫậắằẳẵặἀἁἂἃἄἅἆἇᾰᾱᾲᾳᾴάαᾶᾷᾀᾁᾂᾃᾄᾅᾆᾇὰάḁӑӓɑᴀѐèéêëēĕėęěȅȇȩẹẻẽếềểễệөӫḕḗḙḛḝҽҿӗùúûüǔǖǘǚǜȕȗũūŭůűųưʋὺύụủứừửữựῠῡῢΰῦῧὐὑὒὓὔὕὖὗṳṵṷṹṻòóôõöøōŏőɵơȍȏǒǿȫȭȯǫǭȱṍṏṑṓὀὁὂὃὄὅọỏốồổỗộớờởỡợӧοσόὸόýÿƴɏŷȳỳỵỷỹӯӱӳẙẏўүұṅṇṉṋǹćĉċčçƈͼϲҫҁϾϹҭkⱪķĸǩḱḳḵқҝҟҡќӄκƙҗҗӝӂxẋẍҳӽӿᴄᴏᴛℂ০קϿϷ϶ϰϭϬϗϓΤϒϏϐĘᴇ]/u', $word))  //
                        if (!preg_match('/[a-z]{4,}/u', $word)) {
                            if (preg_match('/^[c]{1}/iu', $word))
                                if (!preg_match('/[aceonpxuyABCEHKMOPTXY]/u', mb_substr($word, 1, null, 'UTF-8')))
                                    continue;
                            $found = $word;
                            break;
                        }

    }
    return $found;
}


/**
 **
**/


function lat_replace($text)
{

    if(!isset($GLOBALS['lat_replace'])) {

        $replaces_unicodes['а']='aàáâαãäåāąăȁȃǎǟǡǻȧẚạảấầẩẫậắằẳẵặἀἁἂἃἄἅἆἇᾰᾱᾲᾳάᾁᾴᾉάᾶᾷᾀᾂᾃᾄᾅᾆᾇὰḁӑӓɑᴀ';
        $replaces_unicodes['б']='ϭϬ';
        $replaces_unicodes['в']='bʙϐβ';
        $replaces_unicodes['г']='ӷӻґѓғṙṛṝṟ';
        $replaces_unicodes['д']='ĝğġģ';
        $replaces_unicodes['е']='eèéêëēĕėęěȅȇȩẹẻẽếềểễệөӫḕḗḙḛḝҽҿӗѐεĘᴇ';
        $replaces_unicodes['ж']='җҗӝӂ';
        $replaces_unicodes['и']='uùúûüǔǖǘǚǜȕȗũūŭůűųưʋὺύụủứừửữựῠῡῢΰῦῧὐὑὒὓὔὕὖὗṳṵṷṹṻӥϰ';
        $replaces_unicodes['й']='ѝӣҋ';
        $replaces_unicodes['к']='kⱪķĸǩḱḳḵқҝҟҡќӄκƙϗϏ';
        $replaces_unicodes['л']='ӆ';
        $replaces_unicodes['м']='mμӎϻϺΜ';
        $replaces_unicodes['н']='hḣḥḧḩḫĥħӈӊңηᾐᾑᾒᾓᾔᾕᾖᾗῂῃῄῆῇἠἡἢἣἤἥἦἧὴήƞṅṇṉṋǹήɲҥʜ';
        $replaces_unicodes['о']='oòóôõöøōŏőσɵơȍȏǒǿȫȭȯǫǭȱṍṏṑṓὀὁὂὃὄόὅọỏốồổỗộớờởỡợӧοόὸᴏ০θOÒÓÔÕÖØŌŎŐΣƟƠȌȎǑǾȪȬȮǪǬȰṌṎṐṒὈὉὊὋὌΌὍỌỎỐỒỔỖỘỚỜỞỠỢӦΟΌῸ';
        $replaces_unicodes['п']='nπ';
        $replaces_unicodes['р']='pρṕṗῤῥҏƥþקϷ';
        $replaces_unicodes['с']='cćĉċčçƈͼϲҫҁḉᴄℂϾϹCĆĈĊČÇƇϾϹҪҀḈ';
        $replaces_unicodes['т']='tḿṁṃṫṭṯṱẗτҭţťŧʈᴛΤ';
        $replaces_unicodes['у']='yýÿƴɏŷȳỳỵỷỹӯӱӳẙẏўүұɣγϓϒΥYÝŸƳɎŶȲỲỴỶỸӮӰӲẙẎЎҮҰƔ';
        $replaces_unicodes['х']='xẋẍҳӽӿχΧXẊẌҲӼӾΧ';
        $replaces_unicodes['ч']='ҷҹӌӵ';
        $replaces_unicodes['ы']='ӹ';
        $replaces_unicodes['э']='ӭϿ϶';

        $lat = array('u`');
        $cyr = array('й');

        foreach($replaces_unicodes as $cyr_simbol => $array_unicodes) {

            $array_unicodes = preg_split('//u', $array_unicodes, null, PREG_SPLIT_NO_EMPTY);
            $array_unicodes = array_unique($array_unicodes);

            foreach($array_unicodes as $simbol_unicode) {
                 $lat[] = $simbol_unicode;
                 $cyr[] = $cyr_simbol;
            }
        }
        $GLOBALS['lat_replace']['lat'] = $lat;
        $GLOBALS['lat_replace']['cyr'] = $cyr;
    }

    $text = str_replace($GLOBALS['lat_replace']['lat'], $GLOBALS['lat_replace']['cyr'], $text);

    preg_match_all('/3{1}[а-яё]{1}/iu', $text, $matches);
    $matches = $matches[0];

    foreach ($matches as $match) {
        $match_replaced = str_replace('3', 'з', $match);
        $text = str_replace($match, $match_replaced, $text);
    }

    preg_match_all('/6{1}[а-яё]{1}/iu', $text, $matches);
    $matches = $matches[0];

    foreach ($matches as $match) {
        $match_replaced = str_replace('6', 'б', $match);
        $text = str_replace($match, $match_replaced, $text);
    }

    preg_match_all('/[а-яё]{1}0{1}[а-яё]{1}/iu', $text, $matches);
    $matches = $matches[0];

    foreach ($matches as $match) {
        $match_replaced = str_replace('0', 'о', $match);
        $text = str_replace($match, $match_replaced, $text);
    }

    return $text;
}


/**
 **
**/


function spaces_count($adunit)
{
    if($adunit['type'] == 'H51' || $adunit['type'] == 'H52') return false;

    $adunit = str_replace('   ', ' ', $adunit);
    $adunit = str_replace('  ', ' ', $adunit);

    if(mb_strlen($adunit['header1'], 'UTF-8') > 4) $ad_texts[] = $adunit['header1'];
    if(mb_strlen($adunit['header2'], 'UTF-8') > 4) $ad_texts[] = $adunit['header2'];
    if(mb_strlen($adunit['body'], 'UTF-8') > 4) $ad_texts[] = $adunit['body'];
    if(mb_strlen($adunit['displayUrl'], 'UTF-8') > 3)
        if(mb_stripos($adunit['displayUrl'], ' ', 0, 'UTF-8') !== false) {
            $ad_texts[] = $adunit['displayUrl'];
        }

    foreach ($ad_texts as $ad_text) {

        if(!preg_match('/[а-яёa-z]/iu', $ad_text))
            continue;

        $symbols_count = mb_strlen($ad_text, 'UTF-8');
        $spaces_count = mb_substr_count($ad_text, ' ', 'UTF-8');

        if(preg_match('/[а-яё]/iu', $ad_text))
            $coeff = 0.31;
        else
            $coeff = 0.34;

        if($spaces_count/$symbols_count > $coeff)
            if($spaces_count > 3)
                return $spaces_count;

        if(preg_match('/ (\D|3) (\D|3) (\D|3) /iu', $ad_text ))
            return $spaces_count;
    }
    return false;
}


/**
 **
**/


function get_domain_age ($domain)
{   //https://jsonwhoisapi.com/#pricing
    if(file_exists($GLOBALS['temp_folder'] . 'domains_create/' . $domain)) {

        $result = file_get_contents($GLOBALS['temp_folder'] . 'domains_create/' . $domain);

    } else {

        $url = 'https://madchecker.com/domain/api/' . $domain . '?properties=creation';

        $result = curl_get($url, '', '');

        $result = json_decode($result);

        if(!isset($result -> data)) {
            $age = false;
            goto end_func;
        }

        if($result -> data -> available == true){
            $age = false;
            goto end_func;
        }

        $result = $result -> data -> creation;
        $result = strtotime($result);


    }


    $age = time() - $result;
    $age = floor($age / 60 / 60 / 24);

    end_func:

    file_put_contents($GLOBALS['temp_folder'] . 'domains_create/' . $domain, $result);

    return $age;

}



/**
 **
**/


function redi_curl_get($url, $referer, $myheaders, $getheader, $getbody)
{
    return curl_all($url, $referer, $myheaders, false, false, $getheader, $getbody, false, $GLOBALS['m_useragent']);
}

function curl_get($url, $referer, $myheaders)
{
    return curl_all($url, $referer, $myheaders, false, false, false, true, $GLOBALS['cookie_file'], $GLOBALS['useragent']);
}

function curl_post($url, $postfields, $referer, $myheaders)
{
    return curl_all($url, $referer, $myheaders, true, $postfields, false, true, $GLOBALS['cookie_file'], $GLOBALS['useragent']);
}


/**
 **
**/


function curl_all($url, $referer, $myheaders, $post, $postfields, $getheader, $getbody, $cookiefile, $useragent)
{

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    if ($post) {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields);
    }
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, $getheader);
    if (!$getbody)
        curl_setopt($ch, CURLOPT_NOBODY, true);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_ENCODING, 'gzip,deflate');
    if ($myheaders)
        curl_setopt($ch, CURLOPT_HTTPHEADER, $myheaders);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    if ($useragent)
        curl_setopt($ch, CURLOPT_USERAGENT, $useragent);
    if ($cookiefile) {
        curl_setopt($ch, CURLOPT_COOKIESESSION, true);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookiefile);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookiefile);
    }
    if ($referer)
        curl_setopt($ch, CURLOPT_REFERER, $referer);
    $result = curl_exec($ch);
    curl_close($ch);

    return $result;
}


/**
 **
**/


function get_ad_type($html_string)
{
    $type = 'undefined';
    if (mb_strpos($html_string, 'Multi-format', 0, 'UTF-8') !== false)
        $type = 'Multi-format';
    if (mb_strpos($html_string, 'Rich Media', 0, 'UTF-8') !== false)
        $type = 'Rich Media';
    if (mb_strpos($html_string, 'HTML5', 0, 'UTF-8') !== false)
        $type = 'HTML5';
    if (mb_strpos($html_string, 'Text', 0, 'UTF-8') !== false)
        $type = 'Text';
    if (mb_strpos($html_string, 'Image', 0, 'UTF-8') !== false)
        $type = 'Image';

    return $type;
}


/**
 **
**/


function get_ad($url, $ad_type)
{
    $ad_html = curl_get($url, $GLOBALS['arc_tab_req_string'], '');
    if ($ad_html) {
        $ad_html = hex_repl($ad_html);

        if ($ad_type == 'Text') {

            $ad = text_ad($ad_html);
            $ad['type'] = 't';

        } elseif ($ad_type == 'Multi-format') {

            $ad = multiformat_ad($ad_html);

            if (!$ad)
                if (mb_stripos($ad_html, 'data-rh-set-type="0"', 70000, 'UTF-8') !== false) {

                    $ad = multimedia_ad0($ad_html);         // Multi-format, text ad
                    $ad['type'] = 'Mft';

                } elseif (mb_stripos($ad_html, 'data-rh-set-type="62"', 70000, 'UTF-8') !== false) {

                    $ad = multimedia_ad62($ad_html);             // Multi-format, small size ad
                    $ad['type'] = 'Mf62';
                }

            if (!$ad)
                $ad = multiformat_ad_old35($ad_html);

            if (!isset($ad['type']))
                $ad['type'] = 'Mf';

        } elseif ($ad_type == 'Rich Media') {

            if (mb_stripos($ad_html, 'data-rh-set-type="26"', 70000, 'UTF-8') !== false || mb_stripos($ad_html, 'data-rh-set-type="25"', 70000, 'UTF-8') !== false) { // File size about 156 Kb

                $ad = multimedia_ad25_26($ad_html);     // Rich Media
                $ad['type'] = 'M2';

            } elseif (mb_stripos($ad_html, 'data-rh-set-type="15"', 70000, 'UTF-8') !== false) { // File size about 156 Kb

                $ad = multimedia_ad15($ad_html);
                $ad['type'] = 'M1';

            }

        } elseif ($ad_type == 'HTML5') {

            $ad = html5_2_ad($ad_html);
            if (!$ad)
                $ad = html5_1_ad($ad_html);
            else
                $ad['type'] = 'H52';

            if (!$ad)
                $ad = text_ad($ad_html); //Some HTML5 ads have same format as text ad
            else
                $ad['type'] = 'H51';

            if (!isset($ad['type']))
                $ad['type'] = 't';

        } elseif ($ad_type == 'Image') {

            $ad['header1'] = 'Image';
            $ad['fulltext'] = '1';
            $ad['type'] = 'Img';

        } elseif (mb_stripos($ad_html, 'data-rh-set-type="53"', 70000, 'UTF-8') !== false) {

            $ad = text_ad($ad_html);
            $ad['type'] = 't';
            $ad_type = 'Text';

        } elseif (mb_stripos($ad_html, 'data-rh-set-type="15"', 70000, 'UTF-8') !== false) {

            $ad = multimedia_ad15($ad_html);
            $ad['type'] = 'M1';
            $ad_type = 'Rich Media';

        } elseif (mb_stripos($ad_html, 'data-rh-set-type="0"', 70000, 'UTF-8') !== false) {
            
            $ad = multimedia_ad0($ad_html);         // Multi-format, text ad
            $ad['type'] = 'Mft';
            $ad_type = 'Multi-format';

        } elseif (mb_stripos($ad_html, 'data-rh-set-type="26"', 70000, 'UTF-8') !== false || mb_stripos($ad_html, 'data-rh-set-type="25"', 70000, 'UTF-8') !== false) {
                
            $ad = multimedia_ad25_26($ad_html);     // Rich Media
            $ad['type'] = 'M2';
            $ad_type = 'Rich Media';

        } elseif (mb_stripos($ad_html, 'var adData = JSON.parse', 70000, 'UTF-8') !== false) {

            $ad = multiformat_ad($ad_html);
            $ad['type'] = 'Mf';
            $ad_type = 'Mf_adData_JSON';

        } elseif (mb_stripos($ad_html, 'data-rh-set-type="62"', 70000, 'UTF-8') !== false) {

            $ad = multimedia_ad62($ad_html);             // Multi-format, small size ad
            $ad['type'] = 'Mf62';
            $ad_type = 'Mf62';

        } else {

            if (isset($GLOBALS['set_gl']['log']))
                create_log($ad_html, 'ad.un.' . $ad_type . '.');        // Unknown ad
            return false;
        }
        
        $GLOBALS['ad_type'] = $ad_type;
        
        $ad['fulltext'] = trim(mb_strtolower($ad['fulltext'], 'UTF-8'));

        if (!$ad['fulltext']) {
            if (isset($GLOBALS['set_gl']['log']))
                create_log($ad_html, 'ad.unkn.' . $ad_type . '.');      // Ad without any text
            return false;
        }

        if (isset($GLOBALS['set_gl']['log']))
            create_log($ad_html, 'ad.' . $ad_type . '.');
        foreach ($ad as $index => $value) {
            $value = trim($value);
            $ad[$index] = str_replace('  ', ' ', $value);
        }

        unset($dom);

        if(isset($adunit['header1']))
            $ad['header1'] = htmlspecialchars($ad['header1']);
        if(isset($adunit['header2']))
            $ad['header2'] = htmlspecialchars($ad['header2']);
        if(isset($adunit['body']))
            $ad['body'] = htmlspecialchars($ad['body']);

        $cropped = false;
        if (@mb_strlen($ad['body'], 'UTF-8') >= 190) {
            $ad['body'] = mb_substr($ad['body'], 0, 190, 'UTF-8');
            $cropped = true;
        }
        if (@mb_strlen($ad['header1'], 'UTF-8') >= 60) {
            $ad['header1'] = mb_substr($ad['header1'], 0, 60, 'UTF-8');
            $cropped = true;
        }
        if (@mb_strlen($ad['header2'], 'UTF-8') >= 135) {
            $ad['header2'] = mb_substr($ad['header2'], 0, 135, 'UTF-8');
            $cropped = true;
        }
        if ($cropped)
            @$ad['fulltext'] = trim(mb_strtolower($ad['header1'] . ' ' . $ad['header2'] . ' ' . $ad['body'] . ' ' . $ad['displayUrl'], 'UTF-8'));

        return $ad;

    } else
        return false;

}


/**
 **
**/


function load_single_html_ad($url)
{
    $ad_html = curl_get($url, $GLOBALS['arc_tab_req_string'], '');

    $list = explode('</head>', $ad_html, 2);
    if ($list[1])
        $ad_html = $list[1];
    else
        return false;
    unset($list, $html);

    $dom = new DOMDocument('1.0', 'UTF-8');
    @$dom->loadHTML($ad_html);

    foreach ($dom->getElementsByTagName('script') as $index => $script_node)
        $script_node->parentNode->removeChild($script_node);

    foreach ($dom->getElementsByTagName('script') as $index => $script_node)
        $script_node->parentNode->removeChild($script_node);

    foreach ($dom->getElementsByTagName('script') as $index => $script_node)
        $script_node->parentNode->removeChild($script_node);

    foreach ($dom->getElementsByTagName('style') as $index => $script_node)
        $script_node->parentNode->removeChild($script_node);

    foreach ($dom->getElementsByTagName('style') as $index => $script_node)
        $script_node->parentNode->removeChild($script_node);

    unset($ad_html);
    if (trim($dom->textContent)) {

        foreach ($dom->getElementsByTagName('div') as $div_node) {

            if (mb_stripos($div_node->getAttribute('data-type'), 'headline', 0, 'UTF-8') !== false)
                $ad['header1'] = $div_node->textContent;
            if (mb_stripos($div_node->getAttribute('data-type'), 'description', 0, 'UTF-8') !== false)
                $ad['body'] = $div_node->textContent;

        }
        if (!$ad['body'])
            $ad['body'] = $dom->textContent;

        $ad['body'] = str_replace('\\\\n', '', $ad['body']);
        $fulltext = implode(' ', $ad);
        $ad['fulltext'] = $fulltext;

        if (!isset($GLOBALS['set_gl']['utf8_off']))
            foreach ($ad as $index => $value)
                $ad[$index] = utf8_decode($value);

        return $ad;
    } else
        return false;
}


/**
 **
**/


function multimedia_ad15($html)
{
    $ad62 = multimedia_ad62($html, true);

    $list = explode('</head>', $html);
    $ad_html = array_pop($list);
    unset($list, $html);


    $dom = new DOMDocument('1.0', 'UTF-8');
    @$dom->loadHTML($ad_html);
    unset($ad_html);

    $ad['body'] = $ad['header1'] = $ad['header2'] = '';

    foreach ($dom->getElementsByTagName('div') as $div_node) {
        if (stripos($div_node->getAttribute('class'), 'ads_') !== false)
            $ad['body'] .= $div_node->textContent . ' ';
    }

    foreach ($dom->getElementsByTagName('a') as $a_node) {
        if (stripos($a_node->getAttribute('class'), 'rhtitle') !== false) {
            $ad['header1'] .= $a_node->textContent . ' ';
            $ad['header2'] .= $a_node->getAttribute('title') . ' ';
        }
    }

    if(isset($ad62['body']))
        $ad['body'] .= $ad62['body'];

    if(isset($ad62['displayUrl']))
        $ad['displayUrl'] = $ad62['displayUrl'];


    $fulltext = implode(' ', $ad);
    $ad['fulltext'] = $fulltext;

    if (!isset($GLOBALS['set_gl']['utf8_off']))
        foreach ($ad as $index => $value)
            $ad[$index] = utf8_decode($value);

    return $ad;
}


/**
 **
**/


function multimedia_ad25_26($html)
{
    $list = explode('</head>', $html);
    $ad_html = array_pop($list);
    unset($list, $html);

    $dom = new DOMDocument('1.0', 'UTF-8');
    @$dom->loadHTML($ad_html);
    unset($ad_html);

    $ad['header1'] = $ad['header2'] = $ad['body'] = '';
    foreach ($dom->getElementsByTagName('span') as $span_node) {
        if (stripos($span_node->getAttribute('class'), 'rhbody') !== false)
            $ad['body'] = $span_node->textContent;
        if (stripos($span_node->getAttribute('class'), 'rhtitle') !== false)
            $ad['header1'] .= $span_node->textContent . ' ';
        if (stripos($span_node->getAttribute('class'), 'rhpromotext') !== false)
            $ad['header1'] .= $span_node->textContent . ' ';
        if (stripos($span_node->getAttribute('class'), 'rhmessage') !== false)
            $ad['header2'] .= $span_node->textContent . ' ';
    }

    foreach ($dom->getElementsByTagName('a') as $a_node) {
        if (stripos($a_node->getAttribute('class'), 'rhbutton ') !== false)
            if ($ad['displayUrl'] != $a_node->textContent . ' ')
                $ad['displayUrl'] = $a_node->textContent . ' ';
    }

    foreach ($dom->getElementsByTagName('div') as $div_node) {
        if ($div_node->getAttribute('class') == 'message')
            if ($ad['displayUrl'] != $div_node->textContent . ' ')
                $ad['displayUrl'] .= $div_node->textContent . ' ';
    }


    $fulltext = implode(' ', $ad);
    $ad['fulltext'] = $fulltext;

    if (!isset($GLOBALS['set_gl']['utf8_off']))
        foreach ($ad as $index => $value)
            $ad[$index] = utf8_decode($value);

    return $ad;
}


/**
 **
**/


function multimedia_ad0($html)
{
    $ad62 = multimedia_ad62($html, true);

    $list = explode('</head>', $html);
    $ad_html = array_pop($list);
    unset($list, $html);


    $dom = new DOMDocument('1.0', 'UTF-8');
    @$dom->loadHTML($ad_html);
    unset($ad_html);

    $ad['header1'] = '';
    $ad['header2'] = '';
    foreach ($dom->getElementsByTagName('a') as $a_node) {
        if (stripos($a_node->getAttribute('class'), 'rhtitle') !== false) {
            $ad['header1'] .= $a_node->textContent . ' ';
            $ad['header2'] .= $a_node->getAttribute('title') . ' ';
        }
    }

    $ad['body'] = '';
    foreach ($dom->getElementsByTagName('div') as $div_node) {
        if (stripos($div_node->getAttribute('class'), 'rh-body') !== false)
            $ad['body'] .= $div_node->textContent . ' ';
    }

    if(isset($ad62['body']))
        $ad['body'] .= $ad62['body'];

    if(isset($ad62['displayUrl']))
        $ad['displayUrl'] = $ad62['displayUrl'];


    $fulltext = implode(' ', $ad);
    $ad['fulltext'] = $fulltext;

    if (!isset($GLOBALS['set_gl']['utf8_off']))
        foreach ($ad as $index => $value)
            $ad[$index] = utf8_decode($value);

    return $ad;
}


/**
 **
**/


function multimedia_ad62($html, $do_not_decode = false)
{
    $list = explode('data-rh-set-type="62">', $html);
    $ad_html = array_pop($list);

    $list = explode('</body>', $ad_html);
    $ad_html = array_shift($list);
    unset($list, $html);

    $dom = new DOMDocument('1.0', 'UTF-8');
    @$dom->loadHTML($ad_html);
    unset($ad_html);


    $ad['body'] = '';
    foreach ($dom->getElementsByTagName('div') as $div_node) {
        if (stripos($div_node->getAttribute('class'), 'long-title') !== false)
            $ad['body'] .= $div_node->textContent . ' ';
    }

    if(!$ad['body'])
        unset($ad['body']);

    $ad['displayUrl'] = '';
    foreach ($dom->getElementsByTagName('div') as $div_node) {
        if (stripos($div_node->getAttribute('class'), '_clickable') !== false)
            $ad['displayUrl'] .= $div_node->textContent . ' ';
    }

    if(!$ad['displayUrl'])
        unset($ad['displayUrl']);


    $fulltext = implode(' ', $ad);
    $ad['fulltext'] = $fulltext;

    if(!$do_not_decode) {
        if (!isset($GLOBALS['set_gl']['utf8_off']))
            foreach ($ad as $index => $value)
                $ad[$index] = utf8_decode($value);
    }

    return $ad;
}


/**
 **
**/


function text_ad($html)
{
    $list = explode('</head>', $html);
    $ad_html = array_pop($list);
    unset($list, $html);

    $dom = new DOMDocument('1.0', 'UTF-8');
    @$dom->loadHTML($ad_html);
    unset($ad_html);

    foreach ($dom->getElementsByTagName('a') as $a_node) {
        //if( stripos($a_node->getAttribute('class'), 'rhbackground')!==false ) $ad['fulltext']=$a_node->textContent;
        if (stripos($a_node->getAttribute('class'), 'rhtitleline1') !== false) {
            $ad['header1'] = $a_node->textContent;
            continue;
        }

        if (stripos($a_node->getAttribute('class'), 'rhtitleline2') !== false) {
            $ad['header2'] = $a_node->textContent;
            continue;
        }

        if (stripos($a_node->getAttribute('class'), 'rhbody') !== false) {
            $ad['body'] = $a_node->textContent;
            continue;
        }

        //Old ads (with just 1 header) support
        if (stripos($a_node->getAttribute('class'), 'rhtitle ') !== false || stripos($a_node->getAttribute('class'), 'rhtitle"') !== false) {
            $ad['header1'] = $a_node->textContent;
            continue;
        }

        if (stripos($a_node->getAttribute('class'), 'rhurl ') !== false || stripos($a_node->getAttribute('class'), 'rhurl"') !== false) {
            $ad['displayUrl'] = $a_node->textContent;
            continue;
        }
    }

    $fulltext = implode(' ', $ad);
    $ad['fulltext'] = $fulltext;

    if (!isset($GLOBALS['set_gl']['utf8_off']))
        foreach ($ad as $index => $value)
            $ad[$index] = utf8_decode($value);

    return $ad;
}


/**
 **
**/


function html5_1_ad($html)
{
    $list = explode('var adData = {', $html, 2);
    if ($list[1])
        $ad_js = $list[1];
    else
        return false;
    unset($list, $html);

    $list = explode('};</script>', $ad_js, 2);
    $ad_js = $list[0];
    unset($list);

    $ad_js = str_replace('[{', "',", $ad_js);

    $list = explode("',", $ad_js);
    unset($ad_js);
    foreach ($list as $value) {
        $string = explode(":", $value, 2);
        $index = trim($string[0], "[{}]' ");
        $value = trim($string[1], "[{}]' ");
        $ad_data[$index] = $value;
    }

    if (@$ad_data['Custom_layout']) {

        $ad = load_single_html_ad($ad_data['Custom_layout']);

    } else {
        $ad['header1'] = '';
        $ad['header2'] = '';
        $ad['body'] = '';
        $ad['displayUrl'] = '';
        foreach ($ad_data as $index => $value) {

            if (mb_stripos($index, '_name', 0, 'UTF-8') !== false)
                if ($value)
                    $ad['header1'] .= $value . " ";

            if (mb_stripos($index, 'Headline_', 0, 'UTF-8') !== false) {
                if (mb_stripos($index, '_txt', 0, 'UTF-8') !== false)
                    if ($value)
                        $ad['header2'] .= $value . " ";
                if (mb_stripos($index, '_cta', 0, 'UTF-8') !== false)
                    if ($value)
                        $ad['displayUrl'] .= $value . " ";
            }

            if (mb_stripos($index, 'CUSTOM_TEXT', 0, 'UTF-8') !== false) {
                if (preg_match('/text$/iu', $index)) {
                    if (mb_stripos($index, '0', 0, 'UTF-8') !== false) {
                        if ($value)
                            $ad['header1'] .= $value . " ";
                    } elseif (mb_stripos($index, '1', 0, 'UTF-8') !== false) {
                        if ($value)
                            $ad['header2'] .= $value . " ";
                    } else {
                        if ($value)
                            $ad['body'] .= $value . " ";
                    }
                }
            }

            if (mb_stripos($index, 'CUSTOM_BUTTON', 0, 'UTF-8') !== false) {
                if (preg_match('/text$/iu', $index)) {
                    if ($value)
                        $ad['displayUrl'] .= $value . " ";
                }
            }

            if (mb_stripos($index, '_description', 0, 'UTF-8') !== false)
                if ($value)
                    $ad['body'] .= $value . " ";
        }

        $ad['body'] = str_replace('\\\\n', '', $ad['body']);
        $fulltext = implode(' ', $ad);
        $ad['fulltext'] = $fulltext;
    }
    return $ad;
}


/**
 **
**/


function html5_2_ad($html)
{
    $list = explode('previewservice.insertPreviewHtmlUrl(', $html, 2);
    if ($list[1])
        $ad_js = $list[1];
    else
        return false;
    unset($list, $html);

    $list = explode("',", $ad_js);
    unset($ad_js);
    foreach ($list as $value) {

        $value = trim($value, "' ");
        if (mb_stripos($value, 'http', 0, 'UTF-8') !== false) {
            $url = $value;
            break;
        }
    }

    $ad = load_single_html_ad($url);

    return $ad;
}


/**
 **
**/

/*
function multiformat_ad_62_long_header($html)
{
    $list = explode('data-rh-set-type="62">', $html);
    $ad_html = array_pop($list);
    unset($list, $html);

    $list = explode('<script', $ad_html, 2);
    $ad_html = $list[0];
    unset($list, $html);

    $dom = new DOMDocument('1.0', 'UTF-8');
    @$dom->loadHTML($ad_html);
    unset($ad_html);

    foreach ($dom->getElementsByTagName('a') as $a_node) {
        if ($a_node->getAttribute('class') == 'rhlongtitle')
            $long_header = $a_node->textContent;
    }

    if (!isset($GLOBALS['set_gl']['utf8_off']))
        $long_header = utf8_decode($long_header);

    return $long_header;
}
*/

/**
 **
**/


function multiformat_ad($html)
{
    $list = explode("var adData = JSON.parse('", $html, 2);
    if ($list[1])
        $ad_js = $list[1];
    else
        return false;
    unset($list, $html);

    $list = explode("');;window.ExitApi = undefined;", $ad_js, 2);
    $ad_js = $list[0];
    unset($list);

    $ad_js = str_replace('\\\\\\\\', '\\', $ad_js);
    $ad_js = str_replace('\\\\\\', '\\', $ad_js);
    $ad_js = str_replace('\\\\', '\\', $ad_js);
    $ad_js = str_replace('\"', '"', $ad_js);
    $ad_js = str_replace('\\', '', $ad_js);
    $ad_js = str_replace('"[', '[', $ad_js);
    $ad_js = str_replace(']"', ']', $ad_js);

    $ad_data = json_decode($ad_js, 1);

    if (@!$ad_js) {
        return false;
    } else {

        //$ad['destination_url']=$ad_data['destination_url'];

        $ad_data = $ad_data['google_template_data'];
        $ad_data = $ad_data['adData'];
        $ad_data = $ad_data[0];

        $ad['header1'] = $ad_data['text1TFText'];
        $ad['header2'] = '';
        $ad['body'] = $ad_data['text2TFText'];
        $ad['displayUrl'] = $ad_data['clickTFText'];
        //if($ad_data['clickTFText']) $ad['displayUrl'].=' '.$ad_data['clickTFText'];

        $fulltext = implode(' ', $ad);
        $ad['fulltext'] = $fulltext;
    }
    return $ad;
}

function multiformat_ad_old35($html)
{
    $list = explode("'adData': [", $html, 2);
    if ($list[1])
        $ad_json = $list[1];
    else
        return false;
    unset($list);

    $list = explode(",'FLAG_client", $ad_json, 2);
    $ad_json = $list[0];
    unset($list);

    $ad_json .= '}';
    $ad_json = str_replace("'", '"', $ad_json);

    $ad_data = json_decode($ad_json);

    $ad['header1'] = $ad_data->text1TFText;
    $ad['body'] = $ad_data->text2TFText;
    $ad['displayUrl'] = $ad_data->clickTFText;

    if (!$ad['displayUrl']) {
        $list = explode("visible_url: '", $html, 2);
        $vis_url = $list[1];
        unset($list, $html);

        $list = explode("',", $vis_url, 2);
        $vis_url = $list[0];
        unset($list);

        $ad['displayUrl'] = $vis_url;
    }
    $fulltext = implode(' ', $ad);
    $ad['fulltext'] = $fulltext . ' ' . $ad_data->clickTFText . ' ' . $ad_data->advertiser_name;

    return $ad;
}


/**
 **
**/


function get_stats($ad_id)
{
    $inner_params = '[{"1":"' . $ad_id . '"}]';
    $params = '{"1":' . $inner_params . '}';

    $id_len = strlen($ad_id);
    $id_len = check_ad_id_length($id_len);
    if ($GLOBALS['set_gl']['arc'] == 'adx' || $id_len == 120)
        $result = creative_review('getApprovalStats', $params);
    elseif ($id_len == 104 || $id_len == 108)
        $result = creative_review_new('GetApprovalStats', $params);

    unset($inner_params, $params);

    if ($GLOBALS['set_gl']['arc'] == 'adx' || $id_len == 120)
        $result = $result->result->{1};
    elseif ($id_len == 104 || $id_len == 108)
        $result = $result->default->{1};

    $result = $result[0]->{3};
    $result = array_sum($result);
    return $result;
}


/**
 **
**/


function get_advertisers_list()       // function for get list of all blocked AdWords acconuts.
{
    $params = '{"1":"ca-' . $GLOBALS['pub_id'] . '"}';

    if ($GLOBALS['set_gl']['arc'] == 'arc5') {
        $result = creative_review_new('GetAdWordsAdvertiserDecisions', $params);
        $result_keyword = 'default';
    }
    else {
        $result = creative_review('getAdWordsAdvertiserDecisions', $params);
        $result_keyword = 'result';
    }
    unset($params);
    $result = $result->$result_keyword->{1};
    return $result;
}


/**
 **
**/


function get_blocked_urls_list()       // function for get list of all blocked urls.
{
    $params = '{"1":[{"1":0,"2":"ca-' . $GLOBALS['pub_id'] . '"}],"2":""}';

    $result = blocking_controls('getAdvertiserUrlApprovals', $params);
    unset($params);
    return $result;
}


/**
 **
**/


function block_unblock_url($url, $unblock = 'unblock')       // block and unblock urls
{
    if ($unblock == 'unblock')
        $mean_digit = '0';
    else
        $mean_digit = '1';

    $url = trim($url);
    $url = rtrim($url, "/");

    $params = '{"1":[{"1":{"1":0,"2":"ca-' . $GLOBALS['pub_id'] . '"},"2":"' . $url . '","3":0,"4":' . $mean_digit . '}]}';

    $result = blocking_controls('setAdvertiserUrlApprovals', $params);

    unlink($GLOBALS['temp_folder'] . 'autoblocked_urls/' . md5($url));

    unset($params);
    return $result;
}


/**
 **
**/


function add_blocked_url($urls)
{
    $urls = str_replace('//', '', $urls);
    $urls = str_replace('http:', '', $urls);
    $urls = str_replace('https:', '', $urls);
    $urls = str_replace('ftp:', '', $urls);
    $urls = str_replace(',', "\n", $urls);
    $urls = str_replace(';', "\n", $urls);
    $urls = str_replace(' ', "\n", $urls);

    $list = explode("\n", $urls);

    foreach ($list as $url) {

        $url = trim($url);
        $url = rtrim($url, "/");
        if (!$url) continue;
        if (mb_strpos($url, '.', 0, 'UTF-8') === false) continue;
        $jsoned_url[] = '{"1":{"1":0,"2":"ca-' . $GLOBALS['pub_id'] . '"},"2":"' . $url . '","4":1}';

        $filename = $GLOBALS['temp_folder'] . 'autoblocked_urls/' . md5($url);
        if (!file_exists($filename))
            file_put_contents($filename, time());

        $GLOBALS['blocked_urls'][] = $url;
    }

    $GLOBALS['blocked_urls'] = array_unique($GLOBALS['blocked_urls']);

    $params = '{"1":[' . implode(',', $jsoned_url) . ']}';

    $result = blocking_controls('setAdvertiserUrlApprovals', $params);
    unset($params);


    return $result;
}


/**
 **
**/


function unblock_adwords_account($adv_id)    // unblock AdWords account function for list of blocked advertisers
{
    $inner_params = '[{"1":"' . $adv_id . '"}]';
    $params = '{"1":' . $inner_params . '}';

    $id_len = strlen($adv_id);
    $id_len = check_adv_id_length($id_len);
    if ($GLOBALS['set_gl']['arc'] == 'adx' || $id_len == 56)
        $result = creative_review('removeAdWordsAdvertiserDecisions', $params);
    elseif ($id_len == 36 || $id_len == 40)
        $result = creative_review_new('RemoveAdWordsAdvertiserDecisions', $params);

    unset($inner_params, $params);
    return $result;
}

function block_adwords_account($adv_id)    // block AdWords account function for list of blocked advertisers
{
    $inner_params = '[{"1":{"1":"' . $adv_id . '"},"2":1}]';
    $params = '{"2":' . $inner_params . '}';

    $id_len = strlen($adv_id);
    $id_len = check_adv_id_length($id_len);
    if ($GLOBALS['set_gl']['arc'] == 'adx' || $id_len == 56)
        $result = creative_review('setAdWordsAdvertiserDecisions', $params);
    elseif ($id_len == 36 || $id_len == 40)
        $result = creative_review_new('SetAdWordsAdvertiserDecisions', $params);

    unset($inner_params, $params);
    return $result;
}


/**
 **
**/


function block_ad_account($ad_id, $unblock = 0, $header = '', $adv_id = '', $adv_name = '')    // block and unblock function
{
    if ($unblock)
        $mean_digit = '0';
    else
        $mean_digit = '1';

    $inner_params = '[{"1":{"1":"' . $ad_id . '"},"2":' . $mean_digit . '}]'; // parameter "2": 1 mean block, 0 mean should unblock
    $params = '{"2":' . $inner_params . '}';

    $id_len = strlen($ad_id);
    $id_len = check_ad_id_length($id_len);
    if ($GLOBALS['set_gl']['arc'] == 'adx' || $id_len == 120) {
        $result = creative_review('setAdWordsAdvertiserDecisions', $params);
        $result_keyword = 'result';
    } elseif ($id_len == 104 || $id_len == 108) {
        $result = creative_review_new('SetAdWordsAdvertiserDecisions', $params);
        $result_keyword = 'default';
    }

    $adv_long_id = $result->{$result_keyword}->{1};
    $adv_long_id = $adv_long_id[0]->{1}->{1}; // Advertiser id

    if ($unblock) {
        unset($result);
        $result = unblock_adwords_account($adv_long_id);
    } else {
        if ($adv_name)
            $accs_ads_filename = md5($adv_name);
        else
            $accs_ads_filename = $adv_id . '_' . $GLOBALS['set_gl']['arc'];

        file_put_contents($GLOBALS['temp_folder'] . 'accs_ads/' . $accs_ads_filename, $header . "\n", FILE_APPEND);
        if(!file_exists($GLOBALS['temp_folder'] . 'autoblocked_accs/' . $accs_ads_filename))
            file_put_contents($GLOBALS['temp_folder'] . 'autoblocked_accs/' . $accs_ads_filename, $adv_long_id);
    }

    unset($inner_params, $params);
    return $result;
}


/**
 **
**/


function block_ad($ad_id, $key, $unblock = 0)     // block and unblock function
{
    if ($unblock)
        $mean_digit = '0';
    else
        $mean_digit = '1';

    $inner_params = '[{"1":{"1":"' . $ad_id . '"},"2":' . $mean_digit . '}]'; // parameter "2": 1 mean block, 0 mean unblock
    $params = '{"1":' . $inner_params . ',"2":"' . $key . '"}';

    $id_len = strlen($ad_id);
    $id_len = check_ad_id_length($id_len);
    if ($GLOBALS['set_gl']['arc'] == 'adx' || $id_len == 120)
        $result = creative_review('setCreativeDecisions', $params);
    elseif ($id_len == 104 || $id_len == 108)
        $result = creative_review_new('SetCreativeDecisions', $params);

    unset($inner_params, $params, $key);
    return $result;
}


/**
 **
**/


function list_ad($ad, $ad_index, $found)
{
    $nl = "\n";

    $header2 = $header = $stopword = '';
    if (isset($ad['header2']))
        $header2 = $ad['header2'];
    if (isset($ad['header1']))
        $header1 = $ad['header1'];
    if (isset($ad['stopword']))
        $stopword = '<span class="stopword" >Blocked by: ' . $ad['stopword'] . '</span>';


    if (!$ad['displayUrl'])
        $ad['displayUrl'] = $ad['url_displayed'];
    if (mb_strlen($ad['displayUrl'], 'UTF-8') > 70)
        $ad['displayUrl'] = parse_url($ad['displayUrl'], PHP_URL_HOST);
    if (!$ad['displayUrl'])
        $ad['displayUrl'] = parse_url($ad['url'], PHP_URL_HOST);

    $host = parse_url($ad['url'], PHP_URL_HOST);
    $ad_url = str_replace('//', '', $ad['url']);
    $ad_url = str_replace('http:', '', $ad_url);
    $ad_url = str_replace('https:', '', $ad_url);
    $ad_url = trim($ad_url, '/');
    if ($host) {
        $link_url_blocker = ' ← <a onclick="insert_result_frame(this.parentNode);" href="blocker_url.php?url_to_add=' . rawurlencode($host) . '" target="result_frame" title="Block ' . $host . '" >Block domain</a>';
        if ($ad_url != $host)
            $link_url_blocker .= ' <a onclick="insert_result_frame(this.parentNode);" href="blocker_url.php?url_to_add=' . rawurlencode($ad_url) . '" target="result_frame" title="Block ' . $ad_url . '" >Block URL</a>';
    } else
        $link_url_blocker = '';

    $ad_id = rawurlencode($ad['ad_id']);
    $digikey = rawurlencode($ad['digikey']);

    $report = $for_block_button = $for_unblock_button = $whitelist_domain = $id2white = $whitelist_ad = $whitelist_header1 = $whitelist_header2 = $whitelist_body = '';
    if ($GLOBALS['set_gl']['arc'] == 'arc5') {
        $ad_report = 'ad_report' . getmicrotime();
        $report_style = 'style="display: none;" ';
        if ($found)
            $report_style = '';

        $report = $nl . "<br>\n<a " . $report_style . 'id="' . $ad_report . '" onclick="insert_result_frame(this.parentNode);" href="report_ad.php?ad_id=' . $ad_id . '" target="result_frame" >Report ad</a>';
        $for_block_button = " document.getElementById('$ad_report').removeAttribute('style');";
        $for_unblock_button = " document.getElementById('$ad_report').style.display='none';";
    }

    if ($host)
        $whitelist_domain = '<a href="whitelist_ad.php?new_ad=' . rawurlencode($host) . '" onclick="insert_result_frame(this.parentNode);" target="result_frame" rel="noreferrer" class="whitelist whitelist_domain" title="Whitelist domain (' . $host . ')" ><img src="img/whl.png" /></a> ';

    $url = '<p class="displayurl">' . $whitelist_domain . '<a href="https://nullrefer.com/?' . $ad['url'] . '" title="' . $ad['url'] . '" target="_blank" rel="noreferrer" >' . $ad['displayUrl'] . '</a> (' . $ad['type'] . ')' . $link_url_blocker . $report . '</p>' . $nl;

    $filename = time() . '.' . $ad_index . '.' . rand(0, 9);

    if ($found) {
        $id2white = 'ad_id=' . $ad_id . '&';
        if ($GLOBALS['set_gl']['get_stats'])
            $stats = '<span class="stats" title="Total views">' . get_stats($ad['ad_id']) . '</span>';
        else
            $stats = '';
    } else
        $stats = '';

    if (@$ad['header1'])
        $whitelist_ad .= mb_strtolower($ad['header1'], 'UTF-8') . "\n";
    if (@$ad['header2'])
        $whitelist_ad .= mb_strtolower($ad['header2'], 'UTF-8') . "\n";
    if (@$ad['body'])
        $whitelist_ad .= mb_strtolower($ad['body'], 'UTF-8') . "\n";
    //if($ad['displayUrl']) $whitelist_ad.=mb_strtolower($ad['displayUrl'], 'UTF-8');
    $whitelist_ad = '<a href="whitelist_ad.php?' . $id2white . 'new_ad=' . rawurlencode($whitelist_ad) .
        '" onclick="insert_result_frame(this);" target="result_frame" rel="noreferrer" class="whitelist whitelist_ad" title="Whitelist this ad (2 headers and body), unblock ad and acc" ><img src="img/whl.png" /></a>';

    if (@$ad['header1'])
        $whitelist_header1 = '<a href="whitelist_ad.php?new_ad=' . rawurlencode($ad['header1']) . '" onclick="insert_result_frame(this.parentNode);" target="result_frame" rel="noreferrer" class="whitelist whitelist_header1" title="Whitelist this header (' . $ad['header1'] .
            ')" ><img src="img/whl.png" /></a> ';
    if (@$ad['header2'])
        $whitelist_header2 = '<a href="whitelist_ad.php?new_ad=' . rawurlencode($ad['header2']) . '" onclick="insert_result_frame(this.parentNode.parentNode);" target="result_frame" rel="noreferrer" class="whitelist whitelist_header2" title="Whitelist this header (' . $ad['header2'] .
            ')" ><img src="img/whl.png" /></a> ';
    if (@$ad['body'])
        $whitelist_body = '<a href="whitelist_ad.php?new_ad=' . rawurlencode($ad['body']) . '" onclick="insert_result_frame(this.parentNode);" target="result_frame" rel="noreferrer" class="whitelist whitelist_body" title="Whitelist ad body" ><img src="img/whl.png" /></a> ';

    $blocking_text = trim($ad['header1'] . ' ' . $ad['header2']);
    if(!$blocking_text)
        $blocking_text = $ad['body'];

    $block_ad = '<a onclick="insert_result_frame(this);' . $for_block_button . '" href="blocker.php?type=ad&act=block&ad_id=' . $ad_id . '&digikey=' . $digikey . '" target="result_frame" class="block block_ad" title="Block this ad" ><img src="img/block.png" />Ad</a>';
    $unblock_ad = '<a onclick="insert_result_frame(this);' . $for_unblock_button . '" href="blocker.php?type=ad&act=unblock&ad_id=' . $ad_id . '&digikey=' . $digikey . '" target="result_frame" class="unblock unblock_ad" title="Unblock this ad" ><img src="img/unblock.png" />Ad</a> ';
    $block_account = '<a onclick="insert_result_frame(this);" href="blocker.php?type=acc&act=block&ad_id=' . $ad_id . '&adv_id=' . rawurlencode($ad['adv_id']) . '&adv_name=' . rawurlencode($ad['adv_name']) . '&header=' . rawurlencode($blocking_text) .
        '" target="result_frame" class="block block_acc" title="Block AdWords account" ><img src="img/block.png" />Acc</a>';
    $unblock_account = '<a onclick="insert_result_frame(this);" href="blocker.php?type=acc&act=unblock&ad_id=' . $ad_id . '" target="result_frame" class="unblock unblock_acc" title="Unblock AdWords account" ><img src="img/unblock.png" />Acc</a> ';

    $ad_header = '<div class="ad_header"><span class="adv_id">' . $ad['adv_name'] . ' ' . $ad['adv_id'] . '</span> ' . $stopword . '</div>' . $nl;
    //title="'.$ad['fulltext'].'"

    $header2 = "<br>\n<span>" . $whitelist_header2 . $ad['header2'] . '</span>';
    $header = '<h2>' . $whitelist_header1 . $header1 . $header2 . '</h2>' . $nl;

    $text = '<div class="ad">' . $nl . $whitelist_ad . $nl . $ad_header . $header . '<p class="body" title="ad text">' . $whitelist_body . $ad['body'] . '</p>' . $nl . '<p class="ad_url" title="Full ad URL">' . $ad_url . '</p>' . $nl . $url . '</div><span class="block_buttons">' . $block_ad .
        $unblock_ad . $block_account . $unblock_account . $stats . '</span>' . $nl . $nl . $nl;


    if ($found) {
        foreach ($found as $index => $value) {
            if ($value) {
                if (!file_exists($GLOBALS['temp_folder'] . $index))
                    mkdir($GLOBALS['temp_folder'] . $index, 0775);
                file_put_contents($GLOBALS['temp_folder'] . $index . '/' . $filename, $text, FILE_APPEND);
            }
        }
    } else {
        if (!file_exists($GLOBALS['temp_folder'] . 'clear'))
            mkdir($GLOBALS['temp_folder'] . 'clear', 0775);
        file_put_contents($GLOBALS['temp_folder'] . 'clear/' . $filename, $text, FILE_APPEND);

    }

    return true;
}


/**
 **
**/


function is_ad_whitelisted($ad_fulltext)
{
    $ad_fulltext = mb_strtolower($ad_fulltext, 'UTF-8');
    foreach ($GLOBALS['whitelist'] as $whitestring) {
        if ($whitestring)
            if (mb_stripos($ad_fulltext, $whitestring, 0, 'UTF-8') !== false) { //if we can find any word
                return true;
                break;
            }
    }
    return false;
}


/**
 **
**/


function mark_ads_reviewed($ad_id)
{
    $first_id = $ad_id[0];
    foreach ($ad_id as $id)
        $ids[] = '{"1":"' . $id . '"}';
    unset($ad_id);

    if ($GLOBALS['set_gl']['arc'] == 'arc5')
        $append = ',"2":0';
    else
        $append = '';

    $params = '{"1":[' . implode(',', $ids) . ']' . $append . '}';

    $id_len = strlen($first_id);
    $id_len = check_ad_id_length($id_len);
    if ($GLOBALS['set_gl']['arc'] == 'adx' || $id_len == 120)
        $result = creative_review('setReviewedCreatives', $params);
    elseif ($id_len == 104 || $id_len == 108)
        $result = creative_review_new('SetReviewedCreatives', $params);

    unset($params);

    return $result;
}


/**
 **
**/


function ReportPolicyViolation($adv_id, $count)      // Report bad ads function
{
    if ($GLOBALS['set_gl']['arc'] != 'arc5')
        return 'Use new ARC!'; //Works only with new ARC

    $params = '{"2":[{"2":true,"173265508":{"1":{"1":"' . $adv_id . '"}}}]}';
    //173265508
    for($i = 1; $i <= $count; $i++)
        $result = creative_review_new('ReportPolicyViolation', $params);

    unset($params);
    return $result;
}


/**
 **
**/


function creative_review_new($method, $params)
{
    if (!isset($GLOBALS['xsrftoken_new']))
        $GLOBALS['xsrftoken_new'] = file_get_contents($GLOBALS['temp_folder'] . 'xsrftoken_new.txt');

    $myheaders = $GLOBALS['myheaders_new'];
    $myheaders[] = 'x-framework-xsrf-token:' . $GLOBALS['xsrftoken_new'];

    $query['pc'] = 'ca-' . $GLOBALS['pub_id'];
    $query['onearcClient'] = 'adsense';
    $query['hl'] = 'en_US';

    foreach ($query as $index => $value)
        $rpc[] = $index . '=' . $value;

    $append = ':1';
    $query['rpcTrackingId'] = $GLOBALS['creative_review_new_string'] . $method . '?' . implode('&', $rpc) . $append;
    $query = http_build_query($query);
    $url = 'https://www.google.com' . $GLOBALS['creative_review_new_string'] . $method . '?' . $query;

    $result = curl_post($url, $params, $GLOBALS['new_arc_tab_req_string'], $myheaders);

    if (isset($GLOBALS['set_gl']['log'])){
        create_log($url . "\n" . $params . "\n" . $GLOBALS['arc_tab_req_string'] . "\n", 'CR_new_req.' . $method . '.');
        create_log($result, 'CR_new.' . $method . '.');
    }

    if (mb_strpos($result, 'Error 400 (Not Found)', 0, 'UTF-8') !== false) {
        return '-32000 XSRF token validation';
    }

    $list = explode("\n", $result, 2);
    $result = $list[1];

    $result = json_decode($result); // decode result string

    if (@$result->default->{5})
        file_put_contents($GLOBALS['temp_folder'] . 'some_digi_token.txt', $result->default->{5}); // Renew token
    if (@$result->default->{6})
        file_put_contents($GLOBALS['temp_folder'] . 'some_long_token.txt', $result->default->{6}); // Renew token

    return $result;
}


/**
 **
**/


function creative_review($method, $params)
{
    $xsrftoken = file_get_contents($GLOBALS['xsrftoken_file']);

    $creativeReview = new stdClass(); //to make json request string
    $creativeReview->method = $method;
    $creativeReview->params = $params;
    $creativeReview->xsrf = $xsrftoken;
    $creativeReview_post_request = json_encode($creativeReview);
    unset($creativeReview);

    $result = curl_post($GLOBALS['creative_review_req_string'], $creativeReview_post_request, $GLOBALS['arc_tab_req_string'], $GLOBALS['myheaders']);

    if (isset($GLOBALS['set_gl']['log'])) {
        create_log($GLOBALS['creative_review_req_string'] . "\n" . $creativeReview_post_request . "\n" . $GLOBALS['arc_tab_req_string'], 'CR_req.' . $method . '.');
        create_log($result, 'CR.' . $method . '.');
    }

    $result = json_decode($result); // decode result string

    if ($result->xsrf)
        file_put_contents($GLOBALS['xsrftoken_file'], $result->xsrf); // Renew standard XSRF token

    return $result;
}


/**
 **
**/


function blocking_controls($method, $params)
{
    $xsrftoken = file_get_contents($GLOBALS['xsrftoken_file']);

    $creativeReview = new stdClass(); //to make json request string
    $creativeReview->method = $method;
    $creativeReview->params = $params;
    $creativeReview->xsrf = $xsrftoken;
    $creativeReview_post_request = json_encode($creativeReview);
    unset($creativeReview);

    $result = curl_post($GLOBALS['blocking_controls_req_string'], $creativeReview_post_request, $GLOBALS['arc_tab_req_string'], $GLOBALS['myheaders']);

    if (isset($GLOBALS['set_gl']['log'])) {
        create_log($GLOBALS['blocking_controls_req_string'] . "\n" . $creativeReview_post_request, 'BC_req.' . $method . '.');
        create_log($result, 'BC.' . $method . '.');
    }

    $result = json_decode($result); // decode result string

    if ($result->xsrf)
        file_put_contents($GLOBALS['xsrftoken_file'], $result->xsrf); // Renew standard XSRF token

    return $result;
}


/**
 **
**/


function get_xsrf_token()
{
    if ($GLOBALS['set_gl']['arc'] == 'adx') {
        $url = 'https://admanager.google.com/ads-publisher-controls/drx/4/creativereview?pc=ca-' . $GLOBALS['pub_id'] . '&nc=' . $GLOBALS['nc'] . '&hl=en';
        $result = curl_get($url, $GLOBALS['arc_tab_req_string'], $GLOBALS['myheaders']); // Requesting access tokens in meta tags
    } else {
        $url = 'https://www.google.com/adsense/gwt-properties?pid=' . $GLOBALS['pub_id'] . '&authuser=0&tpid=' . $GLOBALS['pub_id'] . '&ov=3';
        $result = curl_post($url, '', $GLOBALS['arc_tab_req_string'], $GLOBALS['myheaders']); // Requesting access tokens in meta tags
    }

    if (isset($GLOBALS['set_gl']['log']))
        create_log($result, 's2_gwt.');

    $gwtarray = metagwt2array($result); // Converting data to array

    if ($GLOBALS['set_gl']['arc'] == 'adx')
        file_put_contents($GLOBALS['xsrftoken_file'], $gwtarray['xsrf']); // Put XSRF new token
    else
        file_put_contents($GLOBALS['xsrftoken_file'], $gwtarray['syn_token_pb']); // Put XSRF new token

    return true;
}


/**
 **
**/


function get_xsrf_token_new()
{
    $url = 'https://www.google.com/ads-publisher-controls/acx/5/darc/loader?onearcClient=adsense&pc=ca-' . $GLOBALS['pub_id'] . '&tpid=' . $GLOBALS['pub_id'] . '&hl=en';
    $result = curl_post($url, '', $GLOBALS['new_arc_tab_req_string'], $GLOBALS['myheaders_new']); // Requesting access tokens in JS file

    if (isset($GLOBALS['set_gl']['log']))
        create_log($result, 's3_loader_new.');

    $list = explode("'token': '", $result, 2);
    $token_with = $list[1];
    unset($list, $result);
    $list = explode("','", $token_with, 2);
    $token = $list[0];
    unset($list, $token_with);

    file_put_contents($GLOBALS['temp_folder'] . 'xsrftoken_new.txt', $token); // Put XSRF new token

    return true;
}


/**
 **
**/


function get_ad_list($folder)
{
    if ($folder == 'blogspot' || $folder == 'clear' || $folder == 'redirect' || $folder == 'word' || $folder == 'disguised') {
        if (!file_exists($GLOBALS['temp_folder'] . $folder))
            mkdir($GLOBALS['temp_folder'] . $folder, 0775);
        $ad_files = scandir($GLOBALS['temp_folder'] . $folder);
        unset($ad_files[0], $ad_files[1]); //removes «.» and «..»

        $ads_text = '';
        $num_of_ads = count($ad_files);
        foreach ($ad_files as $ad_file) {

            $time = explode('.', $ad_file);
            $time = '<span title="When was checked" class="date_time">' . date("d.m.Y", $time[0]) . "<br>\n" . date("H:i:s", $time[0]) . '</span>';
            $trash = '<span><a title="Delete this ad" class="trash" onclick="insert_result_frame(this); close_ad(this);" href="delete.php?folder=' . $folder . '&ad_file=' . rawurlencode($ad_file) . '" target="result_frame"><img src="img/trash.gif" /></a></span>';
            $ads_text .= '<div class="ad_container" >' . file_get_contents($GLOBALS['temp_folder'] . $folder . '/' . $ad_file) . $time . $trash . '</div>';
        }

        return $num_of_ads . ' ads. ' . $ads_text;
    }
}


/**
 **
**/


function remove_ad_files($folder, $ad_file)
{
    if ($folder == 'blogspot' || $folder == 'clear' || $folder == 'redirect' || $folder == 'word' || $folder == 'disguised') {
        if ($ad_file) {
            if (preg_match('/^\d{10,11}\.\d{1,3}.\d{1,2}$/', $ad_file))
                $result = unlink($GLOBALS['temp_folder'] . $folder . '/' . $ad_file);
        } else {
            $ad_files = scandir($GLOBALS['temp_folder'] . $folder);
            unset($ad_files[0], $ad_files[1]);   //removes «.» and «..»
            foreach ($ad_files as $ad_file) {
                $result = unlink($GLOBALS['temp_folder'] . $folder . '/' . $ad_file);
                if (!$result)
                    $break;
            }
        }

        return $result;
    }
}


/**
 **
**/


function unblock_old_accounts($age)
{
    if (file_exists($GLOBALS['temp_folder'] . 'last_old_unblock')) {
        $last_old_unblock_time = time() - filemtime($GLOBALS['temp_folder'] . 'last_old_unblock');
        if ($last_old_unblock_time < 90000)
            return false;
    }

    $advertisers_list = get_advertisers_list();

    foreach ($advertisers_list as $adv_obj) {
        $adv_long_id = $adv_obj->{1}->{1}->{1};
        if (@$adv_obj->{2})
            $acc_name = md5($adv_obj->{2});
        else
            $acc_name = $adv_obj->{3};
        $adv_long_ids[$acc_name]=$adv_long_id;
    }

    $acc_files = scandir($GLOBALS['temp_folder'] . 'autoblocked_accs');
    unset($acc_files[0], $acc_files[1]);      //removes «.» and «..»

    $count = 0;
    foreach ($acc_files as $acc_file) {
        $acc_age = time() - filemtime($GLOBALS['temp_folder'] . 'autoblocked_accs/' . $acc_file);
        $acc_age = $acc_age / 3600 / 24;
        if ($acc_age > $age) {
            $adv_long_id = $adv_long_ids[$acc_file];
            $id_len = strlen($adv_long_id);
            if ($id_len > 100) {
                if ($id_len != 104 && $id_len != 108 && $id_len != 120)
                    $id_len = check_ad_id_length($id_len);
                block_ad_account($adv_long_id, 1);
            } else {
                if ($id_len != 36 && $id_len != 40 && $id_len != 56)
                    $id_len = check_adv_id_length($id_len);
                unblock_adwords_account($adv_long_id);
            }

            unlink($GLOBALS['temp_folder'] . 'autoblocked_accs/' . $acc_file);
            unlink($GLOBALS['temp_folder'] . 'accs_ads/' . $acc_file);
            $count++;
        }
    }

    file_put_contents($GLOBALS['temp_folder'] . 'last_old_unblock', $count);

    if ($count == 0)
        $count = '';
    return $count;
}


/**
 **
**/


function is_still_log_in()
{
    @$cookies = file_get_contents($GLOBALS['cookie_file']);
    if (stripos($cookies, 'SIDCC') !== false)
        return true;
    else
        return false;
}


/**
 **
**/


function get_paid_stats($html)
{
    $list = explode('bruschettaMetadata = \'', $html, 2);
    $script_with = $list[1];
    $list = explode('}\';', $script_with, 2);
    $script_with = $list[0] . '}';
    $res = json_decode($script_with);
    return $res;
}


/**
 **
**/


function create_log($result, $filename)
{
    if (!isset($GLOBALS['result_tmp']))
        $GLOBALS['result_tmp'] = '';
    if ($result != $GLOBALS['result_tmp']) {
        file_put_contents($GLOBALS['temp_folder'] . 'logs/' . $filename . getmicrotime(), $result);
        $GLOBALS['result_tmp'] = $result;
    }
    return true;
}


/**
 **
**/


function getmicrotime()
{
    $list = explode(" ", microtime());
    $usec = (string )round($list[0], 3) * 1000;
    $sec = (string )$list[1];
    return $sec . $usec;
}


/**
 **
**/


function remove_old_files($days)
{
    if (file_exists($GLOBALS['temp_folder'] . 'last_clean')) {
        $last_clean_time = time() - filemtime($GLOBALS['temp_folder'] . 'last_clean');
        if ($last_clean_time < 260000)
            return false;
    }

    //$folders[] = 'accs_ads'; //self-cleaning is
    //$folders[] = 'autoblocked_accs'; //self-cleaning is
    //$folders[] = 'autoblocked_urls'; //self-cleaning is
    $folders[] = 'logs';
    $folders[] = 'blogspot';
    $folders[] = 'clear';
    $folders[] = 'disguised';
    $folders[] = 'redirect';
    $folders[] = 'word';
    $folders[] = 'domains_create';



    $age = $days * 24 * 3600;
    $count = 0;

    foreach ($folders as $folder) {

        $files = scandir($GLOBALS['temp_folder'] . $folder);
        unset($files[0], $files[1]);      //removes «.» and «..»

        foreach ($files as $file) {
            $file_age = time() - filemtime($GLOBALS['temp_folder'] . $folder . '/' . $file);

            if ($file_age > $age) {
                unlink($GLOBALS['temp_folder'] . $folder . '/' . $file);
                $count++;
            }
        }
    }

    $files = scandir($GLOBALS['temp_folder']);
    foreach ($files as $file) {
        if (mb_strpos($file, '.cron.', 0, 'UTF-8') !== false) {
            $file_age = time() - filemtime($GLOBALS['temp_folder'] . $file);

            if ($file_age > $age) {
                unlink($GLOBALS['temp_folder'] . $file);
                $count++;
            }
        }
    }

    file_put_contents($GLOBALS['temp_folder'] . 'last_clean', $count);

    if ($count == 0)
        $count = '';
    return $count;
}


/**
 **
**/


function check_ad_id_length($id_len)
{
    if ($id_len != 104 && $id_len != 108 && $id_len != 120) {
        if ($GLOBALS['set_gl']['arc'] == 'arc5')
            $id_len = 104;
        else
            $id_len = 120;
    }
    return $id_len;
}


/**
 **
**/


function check_adv_id_length($id_len)
{
    if ($id_len != 36 && $id_len != 38 && $id_len != 56) {
        if ($GLOBALS['set_gl']['arc'] == 'arc5')
            $id_len = 36;
        else
            $id_len = 56;
    }
    return $id_len;
}
