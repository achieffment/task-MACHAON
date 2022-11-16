<?php
    require_once $_SERVER["DOCUMENT_ROOT"] . "/init/db_conn.php";
    require_once $_SERVER["DOCUMENT_ROOT"] . "/classes/link.php";
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
</head>
<body>
    <?php
        $linkRedirectFlag = false; // Флаг, чтобы в нужном месте вывести ошибку, если таковая будет до совершения редиректа
        // Проверяем есть ли гет параметр с короткой ссылкой, получаем полную и редиректим на неё, если находим
        if (isset($_GET["l"]) && $_GET["l"]) {
            $linkRedirectFlag = true;
            $shortLink = urldecode($_GET["l"]);
            $query = "SELECT link FROM {$table_names['links']} WHERE short_link = '{$shortLink}'";
            $result = $db_conn->query($query);
            if ($result) {
                $result = $result->fetch_assoc();
                $result = $result["link"];
                header("Location: " . $result);
            }
        }
    ?>
    <header>
        <?php if (isset($_SESSION["auth"]) && $_SESSION["auth"]) { ?>
            <p>Вы авторизовались под логином: <?=$_SESSION["auth"]?> <a class="button" href="<?=($redirect_location . "auth/?logout=1")?>">Выйти</a></p>
        <?php } else { ?>
            <a class="button" href="<?=($redirect_location . "auth/")?>">Авторизоваться</a>
            <a class="button" href="<?=($redirect_location . "auth/?registry=1")?>">Зарегистрироваться</a>
        <?php } ?>
        <div class="w-100 header-buttons-container">
            <a class="button" href="/">Главная</a>
            <a class="button" href="<?=($redirect_location . "?getall")?>">Просмотреть все</a>
            <a class="button" href="<?=($redirect_location . "?get")?>">Узнать о ссылке</a>
            <a class="button" href="<?=($redirect_location . "?change")?>">Изменить</a>
            <a class="button" href="<?=($redirect_location . "?remove")?>">Удалить</a>
            <a class="button" href="<?=($redirect_location . "auth/?list")?>">Список администраторов</a>
        </div>
    </header>
    <section>
        <?php
            if ($linkRedirectFlag)
                echo "<p>Не удается найти указанную ссылку.</p>";

            if (isset($_POST["link"]) && $_POST["link"]) {
                $link = new Link($table_names, $db_conn);
                $link->createShortLink($_POST["link"]);
                echo $link->response["message"];
            }

            if ((!isset($_GET["getall"]) && !isset($_GET["get"]) && !isset($_GET["change"]) && !isset($_GET["remove"])) || !isset($_SESSION["auth"])): ?>
                <p>Введите ссылку для получения короткой: </p>
                <form action="/" method="POST">
                    <p>Ссылка: <input type="text" name="link"></p>
                    <p><input type="submit"></p>
                </form>
            <?php else:
                    $link = new Link($table_names, $db_conn);
                    if (isset($_GET["getall"])) {
                        $order_field = "";
                        $order = "";
                        if (isset($_GET["id"]) && $_GET["id"]) {
                            $order_field = "id";
                            $order = $_GET["id"];
                        }
                        if (isset($_GET["link"]) && $_GET["link"]) {
                            $order_field = "link";
                            $order = $_GET["link"];
                        }
                        if (isset($_GET["short_link"]) && $_GET["short_link"]) {
                            $order_field = "short_link";
                            $order = $_GET["short_link"];
                        }
                        $links = $link->getAll($order_field, $order);
                        if (!$links)
                            echo $link->response["message"];
                        else { ?>
                            <table>
                                <thead>
                                    <th class="pointer" onclick="window.location.replace('<?=($redirect_location."?getall&id=")?><?=(isset($_GET["id"]) && $_GET["id"] == "ASC") ? "DESC" : "ASC"?>')">
                                        id
                                    </th>
                                    <th class="pointer" onclick="window.location.replace('<?=($redirect_location."?getall&link=")?><?=((isset($_GET["link"]) && $_GET["link"] == "ASC") ? "DESC" : "ASC")?>')">
                                        link
                                    </th>
                                    <th class="pointer" onclick="window.location.replace('<?=($redirect_location."?getall&short_link=")?><?=((isset($_GET["short_link"]) && $_GET["short_link"] == "ASC") ? "DESC" : "ASC")?>')">
                                        short_link
                                    </th>
                                    <th>
                                        redirect_link
                                    </th>
                                </thead>
                                <tbody>
                                    <?php foreach ($links as $row): ?>
                                    <tr>
                                        <td><?=$row["id"]?></td>
                                        <td><?=$row["link"]?></td>
                                        <td><?=$row["short_link"]?></td>
                                        <td><?=$link->getHTMLLink($row["short_link"])?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php }
                    } else if (isset($_GET["get"])) {
                        if ($_GET["get"]) {
                            $fullLink = $link->validate($_GET["get"]);
                            $fullLink = $link->getFullLink($fullLink);
                            echo "<p>" . $link->response["message"] . "</p>";
                        }
                    ?>
                        <form action="/" method="GET">
                            <p>Введите короткую ссылку: <input type="text" name="get"></p>
                            <p><input type="submit"></p>
                        </form>
                    <?php } else if (isset($_GET["change"])) {
                        if ($_GET["change"] && isset($_GET["changeNew"]) && $_GET["changeNew"]) {
                            $shortLink = $link->validate($_GET["change"]);
                            $newLink = $link->validate($_GET["changeNew"]);
                            if ($shortLink && $newLink) {
                                $link->changeLink($newLink, $shortLink);
                                echo "<p>" . $link->response["message"] . "</p>";
                            } else {
                                echo "<p>Значения пусты или не прошли проверку</p>";
                            }
                        }
                    ?>
                        <form action="/" method="GET">
                            <p>Введите короткую ссылку: <input type="text" name="change"></p>
                            <p>Введите новую ссылку: <input type="text" name="changeNew"></p>
                            <p><input type="submit"></p>
                        </form>
                    <?php } else if (isset($_GET["remove"])) {
                        if ($_GET["remove"]) {
                            $id = $link->validate($_GET["remove"]);
                            if ($id) {
                                $link->removeLink($id);
                                echo "<p>" . $link->response["message"] . "</p>";
                            } else {
                                echo "<p>Значение id пустое или не прошло проверку</p>";
                            }
                        }
                    ?>
                        <form action="/" method="GET">
                            <p>Введите id ссылки для удаления: <input type="text" name="remove"></p>
                            <p><input type="submit"></p>
                        </form>
                    <?php }
                ?>
            <?php endif; ?>
    </section>
</body>
</html>