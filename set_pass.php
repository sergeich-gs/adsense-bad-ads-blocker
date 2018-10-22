<?php include 'functions.php';

if (isset($_POST['pass']))
    if (is_data_safely($_POST['pass'])) {
        @unlink($GLOBALS['temp_folder'] . 'auth_key');
        if ($_POST['pass']) {
            file_put_contents($GLOBALS['temp_folder'] . 'pass', md5($_POST['pass']));
            echo '<p>New password set</p>';
        } else {
            file_put_contents($GLOBALS['temp_folder'] . 'pass', '');
            echo '<p>Password promt disabled</p>';
        }
    }

//—
 ?>