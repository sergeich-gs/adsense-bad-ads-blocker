<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<style>
* { font-size: 20px;  font-family: Calibri, Verdana, Arial; }
body { background: #fff; }
</style>
</head>
<body>
<?php

include 'functions.php';

if (isset($_POST['save_cookie_button'])) {
    if($_POST['save_cookie_button'] == 'Save cookie') {
        $_POST['g_cookie'] = trim($_POST['g_cookie']);
        if($_POST['g_cookie']) {
            if (is_data_safely($_POST['g_cookie'])) {
                if(file_put_contents($GLOBALS['cookie_file'], $_POST['g_cookie'])) {
                    echo '<p>Your Google cookies are saved. It seems you are logged in.</p>';
                    echo '<p>You can <a href="' . $_SERVER['HTTP_REFERER'] . '" target="_parent">refresh the page</a> to check it.</p>';
                } else {
                    echo '<p> Something went wrong with saving your cookies to file "' . $GLOBALS['cookie_file'] . '".</p>';
                }
                goto end_of_file;
            }
        }
    }
}

include 'login_functions.php';


if (!isset($_POST['password']))
    die('Password required.');
if ($_POST['password'] == '')
    die('Password required.');
//if(!is_data_safely($_POST['password']))	  die();

$GLOBALS['result_tmp'] = '';

if(isset($_GET['captcha']))
    goto captcha_input;

/*
if (file_exists($GLOBALS['cookie_file']))
    unlink($GLOBALS['cookie_file']); //delete prev cookie
if (file_exists($GLOBALS['temp_folder'] . 'pub_id.txt'))
    unlink($GLOBALS['temp_folder'] . 'pub_id.txt'); //delete prev pub id
*/

$login_files = scandir($GLOBALS['temp_folder']);
unset($login_files[0], $login_files[1]); //removes . and ..
foreach ($login_files as $login_file) {
    if (mb_strpos($login_file, '.cron.', null, 'UTF-8') !== false) {
        unlink($GLOBALS['temp_folder'] . '/' . $login_file);
    }
}


/**
 * —*	Get login form
 */
$url = 'https://accounts.google.com/ServiceLogin?continue=https%3A%2F%2Fwww.google.com%2Fadsense%2Fgaiaauth2%3Fhl%3Den%26sourceid%3Daso%26noaccount%3Dfalse%26marketing%3Dtrue&rip=1&nojavascript=1&followup=https%3A%2F%2Fwww.google.com%2Fadsense%2Fgaiaauth2%3Fhl%3Den%26sourceid%3Daso%26noaccount%3Dfalse%26marketing%3Dtrue&service=adsense&rm=hide&ltmpl=adsense&hl=en_US&alwf=true';
$first_result = curl_get($url, 'https://google.com/adsense/', $GLOBALS['myheaders']);

if (isset($set['log']))
    create_log($first_result, 'answer1.');

if (trim($first_result) == '') {
    die('Error: empty first answer. It could be DNS fail...');
}

sleep(rand(2, 3));
/**
 * Get password form
 */

$login_result = password_form($first_result, $set['login'], $url);

if (isset($set['log']))
    create_log($login_result, 'answer2res.');

if (trim($login_result) == '') {
    die('Error: empty login answer. It could be DNS fail...');
}

sleep(rand(2, 3));

if (stripos($login_result, 'errormsg_0_Email') !== false) {
    die('<p>Sorry, Google doesn\'t recognize that emai. Please check email and try again.</p>');
}

/**
 *  Password send
 */

$result_auth = password_send($login_result, $_POST['password']);

if (isset($set['log'])) {
    create_log($result_auth, 'answer3_res_auth.');
    $result_tmp = $result_auth;
}

if (trim($result_auth) == '') {
    die('Error: empty auth answer. It could be DNS fail...');
}

if (stripos($result_auth, 'or has JavaScript turned off') !== false) {
    die('<p>This account requres enabled JavaScript. It seems there is no way to log you in.<br />Use spare way with export cookies and put it in «cookie.txt» file. Put your publisher id (pub-2315...) to «pub_id.txt» file.</p>');
}




$result_auth = redirect_check($result_auth);
for($i = 1; $i <= 10; $i++) {
    if (isset($set['log']))
        create_log($result_auth, 'answer3_' . $i . '_res_auth');
    $result_auth = redirect_check($result_auth);
}
if (isset($set['log']))
    create_log($result_auth, 'answer3_11_res_auth');



if (stripos($result_auth, 'Wrong password') !== false) {
    die('<p>Wrong password. Please check input language and try again.</p>');
}

if (stripos($result_auth, 'name="logincaptcha"') !== false) {      /** Captch processing here */

    $forms = get_forms($result_auth);

    if (isset($forms['captcha'])) {
        captcha_form_save($forms['captcha']);
        die();   // Waiting for user input captcha and password again
    }
    die('<p>Something went wrong with captcha form.</p>');

    captcha_input:

    if ($_POST['logincaptcha'])
        if (is_data_safely($_POST['logincaptcha'])) {

            $captcha_form = file($GLOBALS['temp_folder'] . 'captchaform', FILE_IGNORE_NEW_LINES);
            $url = array_shift($captcha_form);
            $ref_url = array_shift($captcha_form);

            foreach ($captcha_form as $input) {

                if ($input == 'logincaptcha=')
                    $input_temp = $input . $_POST['logincaptcha'];
                elseif ($input == 'Passwd=')
                    $input_temp = $input . $_POST['password'];
                else
                    $input_temp = $input;

                $inputs[] = $input_temp;
            }
            $inputs = implode('&', $inputs);


            if (isset($GLOBALS['set_gl']['log']))
                create_log($inputs, 'answer3a_postfields_captcha.');


            $myheaders_captcha = array('Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8',
            'accept-language: en-US,en;q=0.8,en-US;q=0.6,en;q=0.4',
            'content-type: application/x-www-form-urlencoded',
            'Cache-Control: max-age=0',
            'origin: https://accounts.google.com',
            'upgrade-insecure-requests: 1');

            $result_auth = curl_post($url, $inputs, $ref_url, $myheaders_captcha);

            for($i = 1; $i <= 10; $i++) {
                if (isset($set['log']))
                    create_log($result_auth, 'answer3a_' . $i . '_captcha.');
                $result_auth = redirect_check($result_auth);
            }
            if (isset($set['log']))
                create_log($result_auth, 'answer3a_11_captcha.');


            $forms = get_forms($result_auth); // check remind form
            if (isset($forms['remind'])) {
                $result_auth = remind_me_later($forms['remind']); //submit remind form

                echo 'Remind form is!<br />';
            }

            if (stripos($result_auth, '/signin/v1/lookup') !== false) {      /** Login form again */

                die('<p>Something went wrong. Login form is. Try again.</p>');

            }
        }

    //die('<p>Captcha is. Try to add phone number to your Google account.</p>');
}


$to_out = log_in_check($result_auth);



if ($to_out)
    echo $to_out;
else {

    /**
     * 2-stage auth
     */
    $protect_counter = 0;

    auth2stage : $result_auth = redirect_check($result_auth);
    $forms = get_forms($result_auth);

    if (isset($set['log']))
        create_log($result_auth, 'answer4res_auth.');

    after_first_page : if ($protect_counter >= 3)
        exit('too many jumps');

    if (isset($forms['sms'])) { //we have SMS-code input form

        sms_form_save($forms['sms']);

    } else
        if (isset($forms['pre_sms'])) { //we have button "Send SMS-code"

            sleep(rand(2, 3));
            $result_auth = pre_sms_press($forms['pre_sms']);

            for($i = 1; $i <= 10; $i++) {
                if (isset($set['log']))
                    create_log($result_auth, 'answer5_' . $i . '_pre_sms');
                $result_auth = redirect_check($result_auth);
            }
            if (isset($set['log']))
                create_log($result_auth, 'answer5_11_pre_sms');

            $protect_counter++;
            goto auth2stage;

        } else
            if (isset($forms['change'])) { //we have button to page of changing auth type

                sleep(rand(1, 2));
                $forms = change_method($forms['change']);
                $protect_counter++;
                goto after_first_page;
            }

    /**
     * 2-stage auth end
     */
}
end_of_file:

?>
</body>
</html>