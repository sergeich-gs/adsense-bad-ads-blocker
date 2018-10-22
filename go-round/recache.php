<!DOCTYPE html>
<html lang=ru-RU>
<head>
<meta charset=UTF-8>
<title>Обходчик</title>
</head>

<body>

<? if ($_GET['action'] == 'next') {

    if ($_GET['start'] == 'yes') {
        file_put_contents("to_cache.txt", file_get_contents('list_of_pages.txt'));
    }

    $url_array = file("to_cache.txt"); // Считывание файла адресов в массив

    if (count($url_array) > '0') {

        $current_page = $url_array[0];

        $url_array[0] = '';

        $urls = '';
        foreach ($url_array as $k) {
            if (trim($k) != '')
                $urls .= trim($k) . '
';
        } //создаём переменную со списком адресов без первого адреса.

        file_put_contents("to_cache.txt", $urls); //обновляем базу адресов для отправки (без первого адреса.)

        $script_to_cache = 'document.getElementById("the_site").onload = function() { setTimeout(function(){ location.href=\'recache.php?action=next\';  }, ' . rand(500, 2000) . ');  };';
    } else
        echo 'Обход завершён.<br />';

} ?>


Текущий адрес:<br />
<? echo '<a href="' . $current_page . '" target="_blank" >' . $current_page . '</a>'; ?>
<br />

<iframe width="500" height="450" name="the_site" id="the_site" src="<?= $current_page ?>"></iframe>

<script type="text/javascript">
<? echo $script_to_cache; ?>
</script>


</body>
</html>





