<?php
    if (isset($_GET["remove"]) && $_GET["remove"]) {
        $link->removeLink($_GET["remove"]);
        echo $link->returnResponse();
    }
?>
<p>Введите номер ссылки, которую требуется удалить: </p>
<form action="<?=$redirectLink_visual?>" method="GET">
    <p>id: <input type="text" name="remove"></p>
    <p><input type="submit"></p>
</form>
