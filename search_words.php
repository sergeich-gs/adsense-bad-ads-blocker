<?php //—
include 'functions.php';
@$search_words = file_get_contents($GLOBALS['settings_folder'] . 'search_words.txt');
$arr = explode("\n", $search_words);
$count = count($arr);
header("Content-type: text/html; charset=utf-8");

 ?>

<!DOCTYPE html>
<html>
<head>
<title>List of Search Words</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link href="/img/favicon.ico" rel="shortcut icon" type="image/x-icon" />
<meta name="referrer" content="same-origin" />
<meta name="robots" content="noindex, nofollow" />
<link rel="stylesheet" type="text/css" href="style.css?v=<?= $ver ?>"/>
<style>
.wordlist { height: 700px; }
</style>

</head>
<body>

	<h3>List of search words (total <?= $count ?> lines)</h3>
	<form method="post" action="list_update.php" >

	<textarea name="search_words" id="wordlist" class="wordlist" wrap="off" ><?= "\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n" . $search_words . "\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n" ?></textarea>
	<br /><br />

	<input class="submit" type="submit" value="Update list" />

	</form>

<script>
document.getElementById("wordlist").scrollTop=document.getElementById("wordlist").scrollHeight;
</script>

</body>
</html>

