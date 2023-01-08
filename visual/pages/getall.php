<?php
    $sort = $link->makeArSort();
    $links = $link->getAll($sort);
    if ($links === false):
        echo $link->returnResponse();
    else:
?>
    <p>Кликните по столбцу, чтобы произвести сортировку: </p>
    <table>
        <thead>
            <th class="pointer" onclick="window.location.replace('<?=($redirectLink_visual."?getall&sort=id&order=")?><?=((isset($sort["sort"]) && $sort["sort"] == "id" && $sort["order"] == "asc") ? "desc" : "asc")?>')">
                id
            </th>
            <th class="pointer" onclick="window.location.replace('<?=($redirectLink_visual."?getall&sort=link&order=")?><?=((isset($sort["sort"]) && $sort["sort"] == "link" && $sort["order"] == "asc") ? "desc" : "asc")?>')">
                link
            </th>
            <th class="pointer" onclick="window.location.replace('<?=($redirectLink_visual."?getall&sort=short_link&order=")?><?=((isset($sort["sort"]) && $sort["sort"] == "short_link" && $sort["order"] == "asc") ? "desc" : "asc")?>')">
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
                <td><a href="<?=$row["link"]?>"><?=$row["link"]?></a></td>
                <td><?=$row["short_link"]?></td>
                <td><?=$link->getLinkHtml($row["short_link"])?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>