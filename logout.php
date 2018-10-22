<?php include 'functions.php';

$url = 'https://www.google.com/adsense/signout';
$logout_result = curl_get($url, '', '');
//—
 ?>

<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<style>
* { font-size: 20px;  font-family: Calibri, Verdana, Arial; }
body { background: #fff; }
</style>
</head>
<body onload="top.document.location.href='./';">
We are logged out! <a href="./" target="_top">Please refresh the page</a>.
</body>
</html>
