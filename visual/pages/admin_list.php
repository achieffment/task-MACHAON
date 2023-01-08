<?php
    require_once $classes_path . "auth.php";

    $auth = new Auth($table_names, $db_conn, $redirectLink_visual);

    // Добавление
    if (isset($_GET["add"]) && !isset($_POST["login"]) && !isset($_POST["password"])): ?>
        <form action="<?=$redirectLink_visual?>?list&add" method="POST">
            <p>Введите имя пользователя и пароль, чтобы создать нового</p>
            <p>Имя пользователя: <input type="text" required name="login"></p>
            <p>Пароль: <input type="password" required name="password"></p>
            <input type="submit">
        </form>
    <? elseif (isset($_GET["add"]) && isset($_POST["login"]) && $_POST["login"]):
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
                    $auth->registry($login, $password, false);
                    echo $auth->returnRespone();
                }
            }
        }
    endif;

    // Удаление
    if (isset($_GET["delete"]) && $_GET["delete"] && !isset($_GET["deleteConfirm"])):
        $deleteId = htmlspecialchars(strip_tags($_GET["delete"]));
    ?>
       <form method="GET">
           <p>Вы действительно хотите удалить пользователя с id = <?=$deleteId?>?</p>
           <input type="hidden" name="list">
           <input type="hidden" name="delete" value="<?=$deleteId?>">
           <input type="hidden" name="deleteConfirm" value="1">
           <input type="submit">
       </form>
    <? elseif (isset($_GET["delete"]) && isset($_GET["deleteConfirm"])):
        $deleteId = htmlspecialchars(strip_tags($_GET["delete"]));
        $auth->deleteUser($deleteId);
        echo $auth->returnRespone();
    endif;

    // Список
    $admins = $auth->getAdminList();
    if ($admins !== false): ?>
        <p>Список действующих администраторов</p>
        <table>
            <thead>
            <th>id</th>
            <th colspan="3">login</th>
            </thead>
            <tbody>
            <?php foreach ($admins as $admin): ?>
                <tr>
                    <td><?=$admin["id"]?></td>
                    <td><?=$admin["login"]?></td>
                    <td style="text-align: right; width: 50px"><a href="<?=$redirectLink_visual?>?list&delete=<?=$admin["id"]?>">Удалить</a></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <p><br></p>
    <?php else:
        echo $auth->returnRespone();
    endif;
?>
<a class="button" href="<?=$redirectLink_visual?>?list&add">Добавить</a>
