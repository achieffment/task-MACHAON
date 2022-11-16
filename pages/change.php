<?php
    if (isset($_GET["change"]) && $_GET["change"] && isset($_GET["changeLink"]) && $_GET["changeLink"]) {
        $link->changeLink($_GET["changeLink"], $_GET["change"]);
        echo $link->returnResponse();
    }
?>
<p>Введите короткую ссылку и новое значение полной ссылки для неё: </p>
<form action="/" method="GET">
    <p>Введите короткую ссылку: <input type="text" name="change"></p>
    <p>Введите новую ссылку: <input type="text" name="changeLink"></p>
    <p><input type="submit"></p>
</form>
