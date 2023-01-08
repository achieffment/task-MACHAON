<?php
if ($_GET["getlink"]) {
    $info = $link->getLinkInfo($_GET["getlink"]);
    echo $link->returnResponse();
}
?>
<p>Введите id, ссылку или короткую ссылку для получения информации: </p>
<form action="<?=$redirectLink_visual?>" method="GET">
    <p>Запрос: <input type="text" name="getlink"></p>
    <p><input type="submit"></p>
</form>