<!doctype html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Тестовое задание для MACHAON</title>
    <style>
        body {
            margin: 0;
            height: 100vh;
        }
        .container {
            width: 100%;
            height: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .button-container {
            display: flex;
        }
        a {
            display: flex;
            justify-content: center;
            align-items: center;
            min-width: 175px;
            min-height: 35px;
            background: #2e2e2e;
            color: #ffffff;
            margin: 5px;
            text-decoration: none;
            transition: 0.15s all ease-in-out;
        }
        a:hover {
            background: #575757;
        }
        a:visited {
            color: #ffffff;
            outline: none;
            border: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="button-container">
            <a href="/visual/">С визуальной частью</a>
            <a href="/rest/">REST</a>
            <a href="/init/db_create.php">Создать бд</a>
        </div>
    </div>
</body>
</html>