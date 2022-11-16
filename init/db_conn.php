<?php
    session_start();

    // Редирект нужен, для локалки опенсервера, т.к. вставляет путь к диску, который заменяю
    $redirectLink = $server_path = $_SERVER["DOCUMENT_ROOT"];
    if ($redirectLink[1] == ":")
        $redirectLink = "http://" . $_SERVER["HTTP_HOST"] . "/";

    $db_hostname = "localhost";
    $db_login    = "root";
    $db_pass     = "";
    $db_name     = "taskmdchieff";

    $table_names = [
        "admins" => "admins",
        "links" => "links"
    ];

    $db_conn = new mysqli($db_hostname, $db_login, $db_pass, $db_name);
    if ($db_conn->connect_error)
        die("Произошла ошибка подключения к базе данных: " . $db_conn->connect_error);
?>