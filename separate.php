<?php $index_php = file_get_contents('index.php');
$index_php = mb_substr($index_php, 5, null, 'UTF-8');

$index_parts_unsorted = explode('<?= $html_sep ?>', $index_php);

foreach ($index_parts_unsorted as $i => $index_part) {
    if ($i) {
        $list = explode('/*sep*/ ?>', $index_part, 2);
        $index = trim(str_ireplace('<? /*', '', $list[0]));
        $index_parts[$index] = $list[1];
    } else
        $index_parts['header'] = $index_part;
}

$list = explode('/', __file__);
$filename = $list[count($list) - 1];

$index_parts['menu'] = '<p class="menu"><a href="' . $filename . '?settings">Settings</a> <a href="' . $filename . '">Reports</a> <a href="./">Main version</a></p>';

$index_php = $index_parts['header'] . $index_parts['menu'];

if (isset($_GET['settings'])) {
    $index_parts['settings'] = $index_parts['settings'] . $index_parts['settings_run_interval'] . $index_parts['settings_after_run_interval'];
    $index_php .= $index_parts['settings'] . $index_parts['right_colulmn_top'] . $index_parts['auth'] . $index_parts['working_frame_with_buttons'] . $index_parts['access_pass'] . $index_parts['right_column_bottom'];
} else {
    $index_parts['settings_run_interval'] = str_ireplace('id="run_interval"', 'id="run_interval" disabled', $index_parts['settings_run_interval']);
    $index_php .= '<div class="sep">' . $index_parts['settings_run_interval'] . $index_parts['working_frame_with_buttons'] . '</div>' . $index_parts['reports'];
}

$index_php .= $index_parts['footer'];

eval($index_php);

/**
 * header
 * menu
 * settings
 * settings_run_interval
 * settings__after_run_interval
 * auth
 * working_frame_with_buttons
 * access_pass
 * donate
 * reports
 * footer
 */ ?>

