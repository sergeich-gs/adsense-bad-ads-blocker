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
<?php include 'functions.php';
include 'login_functions.php';

if ($_POST['Pin'])
    if (is_data_safely($_POST['Pin'])) {

        $sms_form = file($GLOBALS['temp_folder'] . '2authsmsform', FILE_IGNORE_NEW_LINES);
        $url = array_shift($sms_form);
        $ref_url = array_shift($sms_form);

        foreach ($sms_form as $input) {

            if ($input == 'Pin=')
                $inputs[] = $input . $_POST['Pin'];
            else
                $inputs[] = $input;
        }
        $inputs = implode('&', $inputs);

        $result_auth3 = curl_post($url, $inputs, $ref_url, '');


        for($i = 1; $i <= 10; $i++) {
            if (isset($set['log']))
                create_log($result_auth3, 'answer7_' . $i . '_res_auth');
            $result_auth3 = redirect_check($result_auth3);
        }
        if (isset($set['log']))
            create_log($result_auth3, 'answer7_11_res_auth');

        $forms = get_forms($result_auth3); // check remind form
        if (isset($forms['remind'])) {
            $result_auth3 = remind_me_later($forms['remind']); //submit remind form
        }

        for($i = 1; $i <= 10; $i++) {
            if (isset($set['log']))
                create_log($result_auth3, 'answer8_' . $i . '_rem_fo_auth');
            $result_auth3 = redirect_check($result_auth3);
        }
        if (isset($set['log']))
            create_log($result_auth3, 'answer8_11_rem_fo_auth');

        $result_auth3 = hex_repl($result_auth3);

        $to_out = log_in_check($result_auth3);

        if ($to_out)
            echo $to_out;
        else
            echo 'Something went wrong.';

    }
//—
 ?>
</body>
</html>
