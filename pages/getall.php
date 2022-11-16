<?php
    $order_field = "";
    $order = "";
    if (isset($_GET["id"]) && $_GET["id"]) {
        $order_field = "id";
        $order = $_GET["id"];
    }
    if (isset($_GET["link"]) && $_GET["link"]) {
        $order_field = "link";
        $order = $_GET["link"];
    }
    if (isset($_GET["short_link"]) && $_GET["short_link"]) {
        $order_field = "short_link";
        $order = $_GET["short_link"];
    }
    $links = $link->getAll($order_field, $order);
    if ($links === false):
        echo $link->returnResponse();
    else:
?>
    <p>Кликните по столбцу, чтобы произвести сортировку: </p>
    <table>
        <thead>
            <th class="pointer" onclick="window.location.replace('<?=($redirectLink."?getall&id=")?><?=(isset($_GET["id"]) && $_GET["id"] == "ASC") ? "DESC" : "ASC"?>')">
                id
            </th>
            <th class="pointer" onclick="window.location.replace('<?=($redirectLink."?getall&link=")?><?=((isset($_GET["link"]) && $_GET["link"] == "ASC") ? "DESC" : "ASC")?>')">
                link
            </th>
            <th class="pointer" onclick="window.location.replace('<?=($redirectLink."?getall&short_link=")?><?=((isset($_GET["short_link"]) && $_GET["short_link"] == "ASC") ? "DESC" : "ASC")?>')">
                short_link
            </th>
            <th>
                redirect_link
            </th>
        </thead>
        <tbody>
            <?php foreach ($links as $row): ?>
            <tr>
                <td><?=$row["id"]?></td>
                <td><?=$row["link"]?></td>
                <td><?=$row["short_link"]?></td>
                <td><?=$link->getLinkHtml($row["short_link"])?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>