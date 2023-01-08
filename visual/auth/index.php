<?php require_once $_SERVER["DOCUMENT_ROOT"] . "/init/db_conn.php"; ?>
<?php require_once $classes_path . "auth.php"; ?>
<!doctype html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Authorization</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/auth.css">
</head>
<body>
    <?php
        $auth = new Auth($table_names, $db_conn, $redirectLink_visual);
        if (isset($_GET["logout"]) && $_GET["logout"]) {
            $auth->logout();
        } else {
            if (isset($_SESSION["auth"]) && $_SESSION["auth"] && !isset($_GET["list"])) {
                header("Location: " . $redirectLink_visual);
            }
        }
    ?>
    <div class="auth-form-container">
        <?php
        if (isset($_GET["registry"]) || (isset($_POST["registry"]) && $_POST["registry"]))
            echo "<h1>Регистрация</h1>";
        else
            echo "<h1>Авторизация</h1>";
        ?>
        <form action="<?=$auth_path?>index.php<?=(isset($_GET["registry"]) ? "?registry" : "")?>" method="post">
            <p>Логин: <input required type="text" name="login"></p>
            <p>Пароль: <input required type="password" name="password"></p>
            <p><input type="submit" value="Принять"></p>
        </form>
        <?php
            if ($auth->response["message"])
                echo $auth->returnRespone();
            if (isset($_POST["login"]) && $_POST["login"]) {
                $login = $auth->validate($_POST["login"], "логин");
                if ($login === false)
                    echo $auth->returnRespone();
                else {
                    if (isset($_POST["password"]) && $_POST["password"]) {
                        $password = $auth->validate($_POST["password"], "пароль");
                        if ($password === false)
                            echo $auth->returnRespone();
                        else {
                            $password = md5($password);
                            if (isset($_GET["registry"]))
                                $auth->registry($login, $password);
                            else
                                $auth->authorize($login, $password);
                            echo $auth->returnRespone();
                        }
                    }
                }
            }
        ?>
        <a class="button" href="<?=$redirectLink_visual?>">Вернуться</a>
        <hr>
        <?php if (!isset($_GET["registry"])): ?>
            <a class="button" href="<?=$auth_path?>?registry">Зарегистрироваться</a>
        <?php else: ?>
            <a class="button" href="<?=$auth_path?>">Авторизоваться</a>
        <?php endif; ?>
    </div>
</body>
</html>