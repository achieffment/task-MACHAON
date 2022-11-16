<?php
    if ($_GET["get"]) {
        $fullLink = $link->getFullLink($_GET["get"]);
        echo $link->returnResponse();
    }
?>
<p>Введите короткую ссылку для получения полной: </p>
<form action="/" method="GET">
    <p>Введите короткую ссылку: <input type="text" name="get"></p>
    <p><input type="submit"></p>
</form>