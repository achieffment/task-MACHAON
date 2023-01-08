<?php
    if (isset($_POST["link"]) && $_POST["link"]) {
        $link->createShortLink($_POST["link"]);
        echo $link->returnResponse();
    }
?>
<p>Введите ссылку для создания короткой: </p>
<form action="<?=$redirectLink_visual?>" method="POST">
    <p>Ссылка: <input type="text" name="link"></p>
    <p><input type="submit"></p>
</form>
