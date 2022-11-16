<?php
    require_once $_SERVER["DOCUMENT_ROOT"] . "/init/db_conn.php";
    require_once $server_path . "/classes/link.php";
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Link creator</title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="/assets/css/index.css">
</head>
<body>
    <header>
        <?php if (isset($_SESSION["auth"]) && $_SESSION["auth"]): ?>
            <p>Вы авторизовались под логином: <?=$_SESSION["auth"]?> <a class="button" href="<?=($redirectLink . "auth/?logout=1")?>">Выйти</a></p>
        <?php else: ?>
            <a class="button" href="<?=($redirectLink . "auth/")?>">Авторизоваться</a>
            <a class="button" href="<?=($redirectLink . "auth/?registry=1")?>">Зарегистрироваться</a>
        <?php endif;
            // Делаем кнопки доступными только для админа
            if (isset($_SESSION["auth"]) && $_SESSION["auth"]):
        ?>
            <nav class="w-100">
                <a class="button" href="/">Главная</a>
                <a class="button" href="<?=($redirectLink . "?getall")?>">Просмотреть все</a>
                <a class="button" href="<?=($redirectLink . "?get")?>">Узнать о ссылке</a>
                <a class="button" href="<?=($redirectLink . "?change")?>">Изменить</a>
                <a class="button" href="<?=($redirectLink . "?remove")?>">Удалить</a>
                <a class="button" href="<?=($redirectLink . "?list")?>">Список администраторов</a>
            </nav>
        <?php endif; ?>
    </header>
    <section>
        <?php
            $link = new Link($table_names, $db_conn, $redirectLink);
            // Проверяем, есть ли параметр для перехода, если есть, то ищем, редиректим
            if (isset($_GET["l"]) && $_GET["l"]) {
                $shortLink = urldecode($_GET["l"]);
                $link->getLinkAndRedirect($shortLink);
                echo $link->returnResponse();
            } else {
                if ((!isset($_GET["getall"]) && !isset($_GET["get"]) && !isset($_GET["change"]) && !isset($_GET["remove"]) && !isset($_GET["list"])) || !isset($_SESSION["auth"]))
                    // Если человек не авторизован и пытается делать запросы, то выводим только страницу создания ссылки
                    require_once $server_path . "/pages/create.php";
                else {
                    // Если человек авторизован, то выводим страницы в соответствии с запросом
                    if (isset($_GET["getall"])) {
                        require_once $server_path . "/pages/getall.php";
                    } else if (isset($_GET["get"])) {
                        require_once $server_path . "/pages/get.php";
                    } else if (isset($_GET["change"])) {
                        require_once $server_path . "/pages/change.php";
                    } else if (isset($_GET["remove"])) {
                        require_once $server_path . "/pages/remove.php";
                    } else if (isset($_GET["list"])) {
                        require_once $server_path . "/pages/admin_list.php";
                    }
                }
            }
        ?>
    </section>
</body>
</html>