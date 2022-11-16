<?php
    require_once $server_path . "/classes/auth.php";
    $auth = new Auth($table_names, $db_conn, $redirectLink);
    $admins = $auth->getAdminList();
    if ($admins !== false): ?>
        <p>Список действующих администраторов</p>
        <table>
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
    <?php else:
        echo $auth->returnRespone();
    endif;
?>