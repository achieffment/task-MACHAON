<?php

    // For creating db and tables
    $db_hostname = "localhost";
    $db_login    = "root";
    $db_pass     = "";
    $db_name     = "taskmdchieff";

    $table_names = [
        "admins" => "'admins'",
        "links" => "'links'"
    ];

    $db_conn = new mysqli($db_hostname, $db_login, $db_pass);
    if ($db_conn->connect_error)
        die("Connection with db failed: " . $db_conn->connect_error);

    // Checking and creating database for project
    $query = "SHOW DATABASES LIKE '%{$db_name}%'";
    $result = $db_conn->query($query);
    if ($result) {
        if ($result->num_rows == 0) {
            $dbs = [];
            while ($row = $result->fetch_assoc())
                $dbs[] = $row;
            if (!in_array($db_name, $dbs)) {
                $query = "CREATE DATABASE {$db_name}";
                $result = $db_conn->query($query);
                if (!$result)
                    die("Problem with creating database: " . $db_conn->error);
            }
        }
    } else
        die("Problem with finding database: " . $db_conn->error);

    // Selecting database
    if (!$db_conn->select_db($db_name))
        die("Can not access database");

    // Taking tables
    $query = "SELECT table_name FROM information_schema.tables WHERE table_schema = '{$db_name}' AND table_name IN (" . implode(",", $table_names) . ")";
    $result = $db_conn->query($query);
    if ($result) {
        $tables = [];
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $tables[] = $row["TABLE_NAME"];
            }
        }
    } else
        die("Problem with checking tables: " . $db_conn->error);

    // Checking and creating tables
    if (!$tables || count($tables) < 2) {
        $table_admins_title = str_replace("'", "", $table_names["admins"]);
        if (!in_array($table_admins_title, $tables)) {
            $query = "CREATE TABLE {$table_admins_title} (
                    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    login VARCHAR(32) UNIQUE NOT NULL,
                    password VARCHAR(32) NOT NULL
                )";
            $result = $db_conn->query($query);
            if (!$result)
                die("Problem with creating table {$table_names["admins"]}" . $db_conn->error);
        }
        $table_links_title = str_replace("'", "", $table_names["links"]);
        if (!in_array($table_links_title, $tables)) {
            $query = "CREATE TABLE {$table_links_title} (
                    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    link VARCHAR(100) NOT NULL,
                    short_link VARCHAR(100) UNIQUE NOT NULL
                )";
            $result = $db_conn->query($query);
            if (!$result)
                die("Problem with creating table {$table_names["links"]}" . $db_conn->error);
        }
    }