<?php

    session_start();

    $visual_path = $rest_path = $classes_path = $_SERVER["DOCUMENT_ROOT"];
    $classes_path .= "/classes/";
    $visual_path .= "/visual/";
    $rest_path .= "/rest/";
    $auth_path = "/visual/auth/";

    $redirectLink_visual = "http" . (($_SERVER["HTTPS"] == "on") ? "s" : "") . "://" . $_SERVER["HTTP_HOST"] . "/visual/";
    $redirectLink_rest = "http" . (($_SERVER["HTTPS"] == "on") ? "s" : "") . "://" . $_SERVER["HTTP_HOST"] . "/rest/";

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