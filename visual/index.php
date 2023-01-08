<?php
    require_once $_SERVER["DOCUMENT_ROOT"] . "/init/db_conn.php";
    require_once $classes_path . "link.php";
?>
<!doctype html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Link creator</title>
    <link rel="stylesheet" href="./assets/css/style.css">
    <link rel="stylesheet" href="./assets/css/index.css">
</head>
<body>
    <header>
        <?php if (isset($_SESSION["auth"]) && $_SESSION["auth"]): ?>
            <p>Вы авторизовались под логином: <?=$_SESSION["auth"]?> <a class="button" href="<?=($redirectLink_visual . "auth/?logout=1")?>">Выйти</a></p>
        <?php else: ?>
            <a class="button" href="<?=($redirectLink_visual . "auth/")?>">Авторизоваться</a>
            <a class="button" href="<?=($redirectLink_visual . "auth/?registry=1")?>">Зарегистрироваться</a>
        <?php endif;
            // Делаем кнопки доступными только для админа
            if (isset($_SESSION["auth"]) && $_SESSION["auth"]):
        ?>
            <nav class="w-100">
                <a class="button" href="<?=$redirectLink_visual?>">Главная</a>
                <a class="button" href="<?=($redirectLink_visual . "?getall")?>">Просмотреть все</a>
                <a class="button" href="<?=($redirectLink_visual . "?get")?>">Получить полную ссылку</a>
                <a class="button" href="<?=($redirectLink_visual . "?getlink")?>">Получить информацию о ссылке</a>
                <a class="button" href="<?=($redirectLink_visual . "?change")?>">Изменить</a>
                <a class="button" href="<?=($redirectLink_visual . "?remove")?>">Удалить</a>
                <a class="button" href="<?=($redirectLink_visual . "?list")?>">Список пользователей</a>
                <a class="button" href="/">Вернуться</a>
            </nav>
        <?php endif; ?>
    </header>
    <section>
        <?php
            $link = new Link($table_names, $db_conn, $redirectLink_visual);
            // Проверяем, есть ли параметр для перехода, если есть, то ищем, редиректим
            if (isset($_GET["l"]) && $_GET["l"]) {
                $shortLink = urldecode($_GET["l"]);
                 $link->getLinkAndRedirect($shortLink);
                echo $link->returnResponse();
            } else {
                if ((!isset($_GET["getall"]) && !isset($_GET["getlink"]) && !isset($_GET["get"]) && !isset($_GET["change"]) && !isset($_GET["remove"]) && !isset($_GET["list"])) || !isset($_SESSION["auth"]))
                    // Если человек не авторизован и пытается делать запросы, то выводим только страницу создания ссылки
                    require_once $visual_path . "pages/create.php";
                else {
                    // Если человек авторизован, то выводим страницы в соответствии с запросом
                    if (isset($_GET["getall"])) {
                        require_once $visual_path . "pages/getall.php";
                    } else if (isset($_GET["get"])) {
                        require_once $visual_path . "pages/get.php";
                    } else if (isset($_GET["change"])) {
                        require_once $visual_path . "pages/change.php";
                    } else if (isset($_GET["remove"])) {
                        require_once $visual_path . "pages/remove.php";
                    } else if (isset($_GET["list"])) {
                        require_once $visual_path . "pages/admin_list.php";
                    } else if (isset($_GET["getlink"])) {
                        require_once $visual_path . "pages/getlink.php";
                    }
                }
            }
        ?>
    </section>
</body>
</html>