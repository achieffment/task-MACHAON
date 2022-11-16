<?php require_once $_SERVER["DOCUMENT_ROOT"] . "/init/db_conn.php"; ?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Authorization</title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <style>
        body {
            display: flex;
            flex-direction: column;
            align-items: center;
        }
    </style>
</head>
<body>
    <?php
        if (isset($_GET["logout"]) && $_GET["logout"]) {
            unset($_SESSION["auth"]);
            echo "<p class='text-success'>Вы вышли из личного кабинета</p>";
        } else {
            if (isset($_SESSION["auth"]) && $_SESSION["auth"] && !isset($_GET["list"])) {
                header("Location: " . $redirect_location);
            }
        }
        if (!isset($_GET["list"])): ?>
            <div class="auth-form-container">
                <form action="/auth/index.php" method="post">
                    <p>Логин: <input required type="text" name="login"></p>
                    <p>Пароль: <input required type="password" name="password"></p>
                    <input type="hidden" name="registry" value="<?=(isset($_GET["registry"]) ? $_GET["registry"] : "")?>">
                    <p><input type="submit"></p>
                </form>
            </div>
        <?php else: ?>
            <?php
                $query = "SELECT id, login FROM {$table_names['admins']}";
                $result = $db_conn->query($query);
                if ($result) {
                    if ($result->num_rows > 0) {
                        $admins = [];
                        while ($row = $result->fetch_assoc()) {
                            $admins[] = $row;
                        } ?>
                        <a style="width: 180px; margin: 15px;" class="button" href="/">Обратно</a>
                        <table style="width: 50%;">
                            <thead>
                                <th>id</th>
                                <th>login</th>
                            </thead>
                            <tbody>
                                <?php foreach ($admins as $admin): ?>
                                    <tr>
                                        <td><?=$admin["id"]?></td>
                                        <td><?=$admin["login"]?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php } else {
                        echo "<p>Нет пользователей с правами администратора.</p>";
                    }
                } else
                    echo "<p>Возникла ошибка при обработке запроса к бд: " . $db_conn->error . "</p>"
            ?>
        <?php endif; ?>
    <?php
        if (isset($_POST["login"]) && $_POST["login"]) {
            $login = htmlspecialchars(strip_tags($_POST["login"]));
            if (!$login)
                echo "<p class='text-error'>Логин задан не верно или пуст!</p>";
            else if (strlen($login) > 32)
                echo "<p class='text-error'>Логин не может превышать 32 символа!</p>";
            else {
                if (isset($_POST["password"]) && $_POST["password"]) {
                    $password = htmlspecialchars(strip_tags($_POST["password"]));
                    if (!$password)
                        echo "<p class='text-error'>Пароль задан не верно или пуст!</p>";
                    else if (strlen($password) > 32)
                        echo "<p class='text-error'>Пароль не может превышать 32 символа!</p>";
                    else {
                        $password = md5($password);
                        if (isset($_POST["registry"]) && $_POST["registry"]) {
                            $query = "SELECT id FROM {$table_names["admins"]} WHERE login = '{$login}'";
                            $result = $db_conn->query($query);
                            if (!$result)
                                echo "Error with authorization system: " . $db_conn->error;
                            else if ($result->num_rows > 0)
                                echo "<p class='text-error'>Такой логин уже используется!</p>";
                            else {
                                $query = "INSERT INTO {$table_names["admins"]} (`login`, `password`) VALUES('{$login}', '{$password}')";
                                $result = $db_conn->query($query);
                                if (!$result)
                                    echo "<p class='text-error'>You are authorized successfully!</p>";
                                else {
                                    $_SESSION["auth"] = $login;
                                    echo "<p class='text-error'>You are registered successfully!</p>";
                                    header("Location: " . $redirect_location);
                                }
                            }
                        } else {
                            $query = "SELECT login FROM {$table_names["admins"]} WHERE login = '{$login}' AND password = '{$password}'";
                            $result = $db_conn->query($query);
                            if (!$result)
                                echo "Error with authorization system: " . $db_conn->error;
                            else if ($result->num_rows > 0) {
                                $_SESSION["auth"] = $login;
                                echo "<p class='text-error'>You are authorized successfully!</p>";
                                header("Location: " . $redirect_location);
                            } else
                                echo "<p class='text-error'>Логин или пароль не верны!</p>";
                        }
                    }
                }
            }
        }
    ?>
</body>
</html>