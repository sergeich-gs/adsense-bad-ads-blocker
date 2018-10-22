<!DOCTYPE html>
<html lang=ru-RU>
<head>
<meta charset=UTF-8>
<title>Обход страниц</title>
</head>

<body>


<form action="list_update.php" method="POST">

	<textarea style="width: 97%; height: 380px;" name="list_of_pages" ><?php echo file_get_contents('list_of_pages.txt'); ?></textarea>
	
	<input type="submit" value="Сохранить список адресов" />

</form>

<p>

	<a href="recache.php?action=next&start=yes" target="frame_for_recache">Начать обход</a>
	<a href="stop.php" target="frame_for_recache">Остановить</a>
	<a href="recache.php?action=next" target="frame_for_recache">Продолжить</a>
</p>

<br /><br />

<iframe width="550" height="650" name="frame_for_recache" sandbox="allow-scripts allow-same-origin allow-popups allow-pointer-lock allow-forms" ></iframe>


</body>
</html>

