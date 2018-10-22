<?php //—

include 'functions.php';

foreach ($_POST as $value)
    if (!is_data_safely($value))
        die();


foreach ($_POST as $index => $value) {

    $wordlist = explode("\n", $value);

    foreach ($wordlist as $word) {
        $word = trim($word);
        if ($word)
            $words_arr[] = mb_strtolower($word, 'UTF-8');
    }

    $words_arr = array_unique($words_arr);

    $words = '';
    foreach ($words_arr as $word)
        $words .= $word . "\n";

    unset($words_arr);
    $words = trim($words);
    $filename = $index . '.txt';
    file_put_contents($GLOBALS['settings_folder'] . $filename, $words);
}

header("Location: " . $_SERVER['HTTP_REFERER']);
