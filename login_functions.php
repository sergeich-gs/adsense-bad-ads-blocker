<?php $GLOBALS['url_ref'] = $GLOBALS['temp_folder'] . 'url_ref.txt';


function clean_old_cron_cookie()
{
    $login_files = scandir($GLOBALS['temp_folder']);
    unset($login_files[0], $login_files[1]); //removes . and ..
    foreach ($login_files as $login_file) {
        if (mb_stripos($login_file, '.cron.', 0, 'UTF-8') !== false) {
            unlink($GLOBALS['temp_folder'] . '/' . $login_file);
        }
    }
}



function password_form($input_html, $login, $ref_url)
{

    $doc = new DOMDocument();
    @$doc->loadHTML($input_html);
    $forms = $doc->getElementsByTagName('form');
    $inputs = $forms->item(0)->getElementsByTagName('input');
    $url = $forms->item(0)->getAttribute('action');

    if (isset($GLOBALS['set_gl']['log']))
        create_log($url, 'answer2_url_pass_form.');

    unset($doc);

    $postfields = '';
    foreach ($inputs as $input) {

        if ($input->getAttribute('name')) {
            if ($input->getAttribute('name') == 'Email')
                $input->setAttribute('value', $login);
            $postfields .= $input->getAttribute('name') . '=' . $input->getAttribute('value') . '&';
        }
    }

    $postfields = trim($postfields, '&');

    if (isset($GLOBALS['set_gl']['log']))
        create_log($postfields, 'answer2_postfields_pass_form.');

    $login_result = curl_post($url, $postfields, $ref_url, '');
    return $login_result;
}


/**
 * — **
 **/


function password_send($input_html, $pass)
{

    $doc = new DOMDocument();
    @$doc->loadHTML($input_html);
    $forms = $doc->getElementsByTagName('form');
    unset($doc);
    $inputs = $forms->item(0)->getElementsByTagName('input');
    $url = $forms->item(0)->getAttribute('action');

    if (isset($GLOBALS['set_gl']['log']))
        create_log($url, 'answer3_url_pass_send.');

    $postfields = '';
    foreach ($inputs as $input) {

        if ($input->getAttribute('name')) {
            if ($input->getAttribute('name') == 'Passwd')
                $input->setAttribute('value', $pass);
            if ($input->getAttribute('name') == 'bgresponse') {
                $postfields .= $input->getAttribute('name') . '=' . 'js_enabled&';
                continue;
            }
            $postfields .= $input->getAttribute('name') . '=' . $input->getAttribute('value') . '&';
        }
    }

    $postfields = trim($postfields, '&');


    if (isset($GLOBALS['set_gl']['log'])) {
        $log_postfields = str_replace($pass, '<<user_password>>', $postfields);
        create_log($log_postfields, 'answer3_postfields_pass_send.');
    }

    $ref_url = 'https://accounts.google.com/signin/v1/lookup';

    $myheaders = array('Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8', 'accept-language: ru-RU,ru;q=0.8,en-US;q=0.6,en;q=0.4', 'content-type: application/x-www-form-urlencoded', 'Cache-Control: max-age=0',
        'origin: https://accounts.google.com', 'upgrade-insecure-requests: 1');

    file_put_contents($GLOBALS['url_ref'], $url);

    $result_auth = curl_post($url, $postfields, $ref_url, $myheaders);
    return $result_auth;
}


/**
 **
 **/


function redirect_check($input_html)
{

    $doc = new DOMDocument();
    @$doc->loadHTML($input_html);

    foreach ($doc->getElementsByTagName('meta') as $meta) {
        if ($meta->getAttribute('http-equiv') == 'refresh') {
            $nextlink = $meta->getAttribute('content');
            break;
        }
    }

    $link = false;
    if (!isset($nextlink))
        if (stripos($input_html, 'The document has moved') !== false) {
            foreach ($doc->getElementsByTagName('a') as $a) {
                if ($a->textContent == 'here')
                    $nextlink = $a->getAttribute('href');
                break;
            }
            if (@$nextlink)
                $link = true;
        }

    unset($doc);

    if (isset($nextlink)) {
        if (!$link) {
            $nextlink = explode('url=', $nextlink);
            $nextlink = $nextlink[1];
        }

        $ref_url = file_get_contents($GLOBALS['url_ref']);

        $myheaders = array('Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8', 'accept-language: ru-RU,ru;q=0.8,en-US;q=0.6,en;q=0.4', 'upgrade-insecure-requests: 1');
        if (stripos($nextlink, '://') === false) {
            $nextlink = 'https://www.google.com' . $nextlink;
        }

        $result = curl_get($nextlink, $ref_url, $myheaders);

        file_put_contents($GLOBALS['url_ref'], $nextlink);

        return $result;

    } else
        return $input_html;

    return $result;
}


/**
 **
 **/


function get_forms($input_html)
{
    $doc = new DOMDocument();
    @$doc->loadHTML($input_html);

    foreach ($doc->getElementsByTagName('form') as $form) {

        if (stripos($form->getAttribute('action'), '/signin/challenge/ipp/') !== false) {
            $sms_form_found = 0;
            foreach ($form->getElementsByTagName('input') as $input) {
                if ($input->getAttribute('name') == 'Pin') {
                    $forms['sms'] = $form;
                    $sms_form_found = 1;
                }
            }
            if (!$sms_form_found) {
                foreach ($form->getElementsByTagName('input') as $input) {
                    if ($input->getAttribute('name') == 'SendMethod' && $input->getAttribute('value') == 'SMS') {
                        $forms['pre_sms'] = $form;
                    }
                }

            }
        }
        if ($form->getAttribute('action') == '/signin/challenge/skip') {
            $forms['change'] = $form;
        }
        if ($form->getAttribute('action') == 'SmsAuthInterstitial') {
            $forms['remind'] = $form; //last form before finish 2stage auth
        }
        if (mb_stripos($form->getAttribute('action'), '/signin/challenge/sl', 0 , 'UTF-8') !== false) {

            foreach ($form->getElementsByTagName('img') as $img_tag) {
                if ($img_tag->getAttribute('alt') == 'Visual verification') {
                    $forms['captcha'] = $form;
                    $GLOBALS['captcha_img_url'] = $img_tag->getAttribute('src');
                    break;
                }
            }
        }
    }
    if(!isset($forms)) return false;
    return $forms;
}


/**
 **
 **/


function sms_form_save($sms_form)
{
    $nl = '
';
    $ref_url = file_get_contents($GLOBALS['url_ref']);

    if(mb_stripos($sms_form->getAttribute('action'), 'https://', 0, 'UTF-8') !== false)           //will be requested url
        $forsave = $sms_form->getAttribute('action') . $nl;
    else
        $forsave = 'https://accounts.google.com' . $sms_form->getAttribute('action') . $nl;

    $forsave .= $ref_url . $nl; //Will be ref. url

    foreach ($sms_form->getElementsByTagName('input') as $input) {
        if ($input->getAttribute('name')) {
            if ($input->getAttribute('type') == 'checkbox')
                $input->setAttribute('value', 'on');

            if ($input->getAttribute('name') == 'bgresponse') {
                $forsave .= $input->getAttribute('name') . '=' . 'js_enabled' . $nl;
                continue;
            }

            $forsave .= $input->getAttribute('name') . '=' . $input->getAttribute('value') . $nl;
        }
    }

    file_put_contents($GLOBALS['temp_folder'] . '2authsmsform', $forsave); ?>
<form method="post" action="login2auth.php">
SMS-code:<br />
<input type="tel" pattern="[0-9 ]*" name="Pin" dir="ltr" autocomplete="off" placeholder="Enter 6-digit SMS-code" autofocus ><br />
<input style="margin-top: 4px;" type="submit" value="Login" >
</form>
<?php     return true;

}


/**
 **
 **/


function captcha_form_save($captcha_form)
{
    $nl = '
';

    $ref_url = file_get_contents($GLOBALS['url_ref']);

    if(mb_stripos($captcha_form->getAttribute('action'), 'https://', 0, 'UTF-8') !== false)           //will be requested url
        $forsave = $captcha_form->getAttribute('action') . $nl;
    else
        $forsave = 'https://accounts.google.com' . $captcha_form->getAttribute('action') . $nl;

    $forsave .= $ref_url . $nl; //Will be ref. url

    foreach ($captcha_form->getElementsByTagName('input') as $input) {
        if ($input->getAttribute('name')) {

            if ($input->getAttribute('name') == 'bgresponse') {
                $forsave .= $input->getAttribute('name') . '=' . 'js_enabled' . $nl;
                continue;
            }

            $forsave .= $input->getAttribute('name') . '=' . urlencode($input->getAttribute('value')) . $nl;
        }
    }

    $img_data = curl_get($GLOBALS['captcha_img_url'], '', '');
    //$audio_data = curl_get($GLOBALS['captcha_img_url'] . '&kind=audio', '', '');

    file_put_contents($GLOBALS['temp_folder'] . 'captcha_img.jpg', $img_data);       // content-type: image/jpeg
    //file_put_contents($GLOBALS['temp_folder'] . 'captcha_audio.wav', $audio_data);     //  content-type: audio/wav
    $img_base64 = base64_encode($img_data);
    //$audio_base64 = base64_encode($audio_data);

    file_put_contents($GLOBALS['temp_folder'] . 'captchaform', $forsave); ?>
<form method="post" action="login.php?captcha=1">
<label>
<img src="data:image/jpeg;base64,<?=$img_base64?>" /><!--<audio controls src="data:audio/wav;base64,<?=$audio_base64?>" ></audio>--><br />
<input type="text" name="logincaptcha" autocomplete="off" placeholder="Enter the letters above" autofocus title="Type the characters you see or numbers you hear. Letters are not case-sensitive" required><br />
</label>
<br />
<label>
Password again, please:<br />
<input class="password" type="password" name="password" placeholder="Will not be saved" value="<?=$_POST['password']?>" required />
</label>
<br />
<input style="margin-top: 4px;" type="submit" value="Next" >
</form>
<?php     return true;

}


/**
 **
 **/


function pre_sms_press($pre_sms_form)
{

    $ref_url = file_get_contents($GLOBALS['url_ref']);

    if(mb_stripos($pre_sms_form->getAttribute('action'), 'https://', 0, 'UTF-8') !== false)           //will be requested url
        $url = $pre_sms_form->getAttribute('action');
    else
        $url = 'https://accounts.google.com' . $pre_sms_form->getAttribute('action');

    foreach ($pre_sms_form->getElementsByTagName('input') as $input) {
        if ($input->getAttribute('name')) {

            if ($input->getAttribute('type') == 'checkbox')
                $input->setAttribute('value', 'on');
            $inputs[] = $input->getAttribute('name') . '=' . $input->getAttribute('value');
        }
    }
    $inputs = implode('&', $inputs);

    $result = curl_post($url, $inputs, $ref_url, '');

    file_put_contents($GLOBALS['url_ref'], $url);

    return $result;
}


/**
 **
 **/


function change_method($change_form)
{

    $ref_url = file_get_contents($GLOBALS['url_ref']);

    if(mb_stripos($change_form->getAttribute('action'), 'https://', 0, 'UTF-8') !== false)           //will be requested url
        $url = $change_form->getAttribute('action');
    else
        $url = 'https://accounts.google.com' . $change_form->getAttribute('action');

    foreach ($change_form->getElementsByTagName('input') as $input) {
        if ($input->getAttribute('name')) {
            $inputs[] = $input->getAttribute('name') . '=' . $input->getAttribute('value');
        }
    }
    $inputs = implode('&', $inputs);

    $result_change = curl_post($url, $inputs, $ref_url, ''); //data with link to auth method change page
    unset($inputs);

    $auth_method_change_page = redirect_check($result_change); //auth method change page

    if (isset($set['log']))
        create_log($auth_method_change_page, 'answer_re_ch.');

    $doc = new DOMDocument();
    @$doc->loadHTML($auth_method_change_page);

    foreach ($doc->getElementsByTagName('form') as $form) {
        if (stripos($form->getAttribute('action'), '/signin/challenge/ipp/') !== false) {
            $forms['pre_sms'] = $form;
        }
    } //Here we should get form. Submitting the form will start SMS auth process

    return $forms;


}

/**
 **
 **/


function log_in_check($input_html)
{

    $success_message = 'Login successful! <a href="./" target="_top">Please refresh the page</a>';

    $doc = new DOMDocument();
    @$doc->loadHTML($input_html);

    $publink = '';
    foreach ($doc->getElementsByTagName('a') as $link) {
        if (stripos($link->getAttribute('href'), 'pub-') !== false) {
            $publink = $link->getAttribute('href');
            break;
        }
    }
    unset($doc);

    if (stripos($publink, '/adsense/new/u/0/pub') !== false) {

        $publink = explode('/', $publink);

        foreach ($publink as $item) {
            if (stripos($item, 'pub-') !== false) {
                $pub_id = $item;
                break;
            }
        }
        file_put_contents($GLOBALS['temp_folder'] . 'pub_id.txt', $pub_id);
        $to_out = $success_message;

    } elseif (preg_match('/pub-\d{16}/iu', $input_html, $pub)) {
        file_put_contents($GLOBALS['temp_folder'] . 'pub_id.txt', $pub[0]);
        $to_out = $success_message;
    } else {
        $to_out = false;
    }

    return $to_out;
}


/**
 **
 **/


function remind_me_later($remind_form)
{

    $ref_url = file_get_contents($GLOBALS['url_ref']);

    if(mb_stripos($remind_form->getAttribute('action'), 'https://', 0, 'UTF-8') !== false)           //will be requested url
        $url = $remind_form->getAttribute('action');
    else
        $url = 'https://accounts.google.com/signin/challenge/ipp/' . $remind_form->getAttribute('action');

    foreach ($remind_form->getElementsByTagName('input') as $input) {
        if ($input->getAttribute('name')) {
            if ($input->getAttribute('type') != 'submit') {
                $inputs[] = $input->getAttribute('name') . '=' . $input->getAttribute('value');
            } else {
                if ($input->getAttribute('name') == 'remind')
                    $inputs[] = $input->getAttribute('name') . '=' . $input->getAttribute('value');
            }
        }
    }
    $inputs = implode('&', $inputs);

    $result = curl_post($url, $inputs, $ref_url, '');

    file_put_contents($GLOBALS['url_ref'], $url);

    return $result;
} ?>