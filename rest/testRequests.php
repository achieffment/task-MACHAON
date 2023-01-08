<?php

require_once $_SERVER["DOCUMENT_ROOT"] . "/classes/test_requests.php";
$testRequests = new TestRequests();

if (isset($_GET["sendpost"])) {
    $postParams = [
        "change" => "t",
        "changeLink" => "9YQbauZw"
    ];
    $result = $testRequests->sendRequestPost($postParams);
    echo $result;
}

if (isset($_GET["sendput"])) {
    $putParams = [
        "link" => "https://google.com/"
    ];
    $result = $testRequests->sendRequestPut($putParams);
    echo $result;
}

if (isset($_GET["senddelete"])) {
    $deleteParams = [
        "id" => "1"
    ];
    $result = $testRequests->sendRequestDelete($deleteParams);
    echo $result;
}

if (isset($_GET["getall"])) {
    $sort = "sort=id&order=desc";
    $result = $testRequests->sendRequestGetAll($sort);
    echo $result;
}

if (isset($_GET["sendgetlink"])) {
    $result = $testRequests->sendRequestGetLink("6");
    echo $result;
}

if (isset($_GET["sendpost"]) || isset($_GET["sendput"]) || isset($_GET["senddelete"]) || isset($_GET["getall"]) || isset($_GET["sendgetlink"])):
    header('Content-Type: application/json');
else: ?>

<!doctype html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Test Requests</title>
    <style>
        div {
            display: flex;
            flex-direction: column;
        }
        a:hover {
            color: black;
        }
    </style>
</head>
<body>
    <div>
        <a href="/rest/">Вернуться</a>
        <a href="/rest/testRequests.php?sendpost">POST (Изменение ссылки по её коду)</a>
        <a href="/rest/testRequests.php?sendput">PUT (Создание короткой ссылки)</a>
        <a href="/rest/testRequests.php?senddelete">DELETE (Удаление)</a>
        <a href="/rest/testRequests.php?getall">GET (Получить список ссылок и переходов с возможностью сортировки)</a>
        <a href="/rest/testRequests.php?sendgetlink">GET (Получить данные конкретной ссылки)</a>
    </div>
</body>
</html>

<?php endif; ?>