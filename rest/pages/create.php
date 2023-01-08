<!doctype html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Link creator</title>
</head>
<body>
<a href="/">Вернуться</a>
<?php
if (isset($_POST["link"]) && $_POST["link"]) {
    $link->createShortLink($_POST["link"]);
    echo $link->returnResponse();
} ?>
<p>Введите ссылку для создания короткой: </p>
<form action="<?=$redirectLink_rest?>" method="POST">
    <p>Ссылка: <input type="text" name="link"></p>
    <p><input type="submit"></p>
</form>
<a href="<?=$redirectLink_rest?>/testRequests.php">Тестирование запросов</a>
</body>
</html>