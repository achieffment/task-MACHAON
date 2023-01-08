<?php

    require_once $_SERVER["DOCUMENT_ROOT"] . "/init/db_conn.php";
    require_once $classes_path . "link.php";
    require_once $classes_path . "link_rest.php";
    require_once $classes_path . "auth.php";

    if ($_SERVER["REQUEST_METHOD"] == "PUT")
        $_PUT = json_decode(file_get_contents('php://input'), true);

    if ($_SERVER["REQUEST_METHOD"] == "DELETE")
        $_DELETE = json_decode(file_get_contents('php://input'), true);

    $link = new LinkRest($table_names, $db_conn, $redirectLink_rest);
    $auth = new Auth($table_names, $db_conn, $redirectLink_rest);

    $headers = getallheaders();

    if (isset($headers["Authorization"]) && $headers["Authorization"])
        $auth->checkToken($headers["Authorization"]);
    else
        $auth->authResponse["message"] = "Не передан токен авторизации";

    // редирект
    if (isset($_GET["l"]) && $_GET["l"]) {

        $shortLink = urldecode($_GET["l"]);
        $link->getLinkAndRedirect($shortLink);
        echo $link->returnResponse();

    } else {

        // Получение ссылки по короткой
        if (isset($_GET["link"]) && $_GET["link"]) {
            $fullLink = $link->getFullLink($_GET["link"], false);
            $link->returnResultJson($link->setResultJson("link", $fullLink));

        // Создание короткой ссылки
        } elseif ($_SERVER["REQUEST_METHOD"] == "PUT" && isset($_PUT["link"]) && $_PUT["link"]) {
            $shortLink = $link->createShortLink($_PUT["link"], false);
            $link->returnResultJson($link->setResultJson("shortLink", $shortLink));

        // Изменение ссылки по её коду
        } elseif (isset($_POST["change"]) && $_POST["change"] && isset($_POST["changeLink"]) && $_POST["changeLink"]) {

            // Проверка токена
            if ($auth->authResponse["status"] === false)
                $link->returnResultJson($auth->authResponse);
            else {
                $redirectLink = $link->changeLink($_POST["change"], $_POST["changeLink"], false);
                $link->returnResultJson($link->setResultJson("redirectLink", $redirectLink));
            }

        // Удаление
        }  elseif ($_SERVER["REQUEST_METHOD"] == "DELETE" && isset($_DELETE["id"]) && $_DELETE["id"]) {

            // Проверка токена
            if ($auth->authResponse["status"] === false)
                $link->returnResultJson($auth->authResponse);
            else {
                $link->removeLink($_DELETE["id"]);
                $link->returnResultJson($link->response);
            }

        // Получить список ссылок и переходов с возможностью сортировки
        } elseif (isset($_GET["getall"])) {

            // Проверка токена
            if ($auth->authResponse["status"] === false)
                $link->returnResultJson($auth->authResponse);
            else {
                $sort = $link->makeArSort();
                $links = $link->getAll($sort);
                if ($links !== false)
                    $link->returnResultJson($links);
                else
                    $link->returnResultJson($link->response);
            }

        // Получить данные конкретной ссылки
        } elseif (isset($_GET["getlink"]) && $_GET["getlink"]) {

            // Проверка токена
            if ($auth->authResponse["status"] === false)
                $link->returnResultJson($auth->authResponse);
            else {
                $result = $link->getLinkInfo($_GET["getlink"], false);
                if ($result !== false && is_array($result)) {
                    $keys = [];
                    $values = [];
                    foreach ($result as $key => $value) {
                        $keys[] = $key;
                        $values[] = $value;
                    }
                    $link->returnResultJson($link->setResultJson($keys, $values));
                } else
                    $link->returnResultJson($link->response);
            }

        // Создание ссылки в веб-интерфейсе
        } else {
            require_once $rest_path . "pages/create.php";
        }

    }
?>