<?php

class Auth {

    public $response = [
        "status" => true,
        "message" => ""
    ];

    public $authResponse = [
        "status" => false,
        "message" => ""
    ];

    private $redirectLink;

    public function __construct($table_names, $db_conn, $redirectLink)
    {
        $this->table_names = $table_names;
        $this->db_conn = $db_conn;
        $this->redirectLink = $redirectLink;
    }

    public function authorize($login, $password)
    {
        $query = "SELECT login FROM {$this->table_names["admins"]} WHERE login = '{$login}' AND password = '{$password}'";
        $result = $this->db_conn->query($query);
        if (!$result)
            $this->setResponse(false, "Произошла ошибка базы данных при обработке запроса для авторизации: " . $this->db_conn->error);
        else if ($result->num_rows > 0) {
            $_SESSION["auth"] = $login;
            $this->setResponse(true);
            header("Location: " . $this->redirectLink);
        } else
            $this->setResponse(false, "Логин или пароль не верны");
    }

    public function checkToken($token) {
        $token = $this->validate($token, "токен");
        if ($token !== false) {
            $query = "SELECT id FROM {$this->table_names["admins"]} WHERE token = '{$token}'";
            $result = $this->db_conn->query($query);
            if (!$result)
                $this->setResponse(false, "Произошла ошибка базы данных при обработке запроса для проверки существования токена: " . $this->db_conn->error);
            else if ($result->num_rows > 0) {
                $this->setResponse(true);
            } else
                $this->setResponse(false, "Переданный токен не существует");
        }
        $this->setAuthResponse($this->response["status"], $this->response["message"]);
    }

    public function registry($login, $password, $auth = true) {
        $query = "SELECT id FROM {$this->table_names["admins"]} WHERE login = '{$login}'";
        $result = $this->db_conn->query($query);
        if (!$result)
            $this->setResponse(false, "Произошла ошибка базы данных при обработке запроса для проверки существования логина: " . $this->db_conn->error);
        else if ($result->num_rows > 0)
            $this->setResponse(false, "Такой логин уже используется!");
        else {
            $token = md5($login . $password);
            $query = "INSERT INTO {$this->table_names["admins"]} (`login`, `password`, `token`) VALUES('{$login}', '{$password}', '{$token}')";
            $result = $this->db_conn->query($query);
            if ($result) {
                if ($auth) {
                    $_SESSION["auth"] = $login;
                    $this->setResponse(true);
                    header("Location: " . $this->redirectLink);
                }
            } else {
                $this->setResponse(false, "Произошла ошибка базы данных при обработке запроса для регистрации: " . $this->db_conn->error);
            }
        }
    }

    public function deleteUser($userId)
    {
        $query = "SELECT login FROM {$this->table_names["admins"]} WHERE id = '{$userId}'";
        $result = $this->db_conn->query($query);
        if (!$result)
            $this->setResponse(false, "Произошла ошибка базы данных при обработке запроса для проверки существования логина: " . $this->db_conn->error);
        else {
            if ($result->num_rows > 0) {
                $login = $result->fetch_assoc();
                print_r($login);
                $query = "DELETE FROM {$this->table_names["admins"]} WHERE id = {$userId}";
                $result = $this->db_conn->query($query);
                if ($result) {
                    $this->setResponse(true, "Пользователь успешно удален");
                    if ($login && isset($login["login"]) && $login["login"] == $_SESSION["auth"]) {
                        unset($_SESSION["auth"]);
                        header("Location: " . $this->redirectLink);
                    }
                }
                else
                    $this->setResponse(false, "Произошла ошибка базы данных при обработке запроса удаления: " . $this->db_conn->error);
            } else
                $this->setResponse(false, "Не найден пользователь с заданным айди для удаления");
        }
    }

    public function logout()
    {
        unset($_SESSION["auth"]);
        $this->setResponse(true, "Вы успешно вышли из личного кабинета");
    }

    public function validate($field, $fieldName)
    {
        $field = htmlspecialchars(strip_tags($field));
        if (!$field)
            $this->setResponse(false, "Поле " . $fieldName . " не заполнено, или были введены некорректные данные");
        else if (strlen($field) > 32)
            $this->setResponse(false, "Длина поля " . $fieldName . " не должна превышать 32 символа");
        else {
            $this->setResponse(true);
            return $field;
        }
        return false;
    }

    public function getAdminList()
    {
        $query = "SELECT id, login FROM {$this->table_names['admins']}";
        $result = $this->db_conn->query($query);
        if ($result) {
            if ($result->num_rows > 0) {
                $admins = [];
                while ($row = $result->fetch_assoc()) {
                    $admins[] = $row;
                }
                return $admins;
            } else
                $this->setResponse(false, "На данный момент нет пользователей с правами администратора.");
        } else
            $this->setResponse(false, "Возникла ошибка при обработке запроса к бд: " . $this->db_conn->error);
        return false;
    }

    public function setAuthResponse($status, $message = "") {
        $this->authResponse["status"] = $status;
        $this->authResponse["message"] = $message;
    }

    public function setResponse($status, $message = "") {
        $this->response["status"] = $status;
        $this->response["message"] = $message;
    }

    public function returnRespone() {
        $statusClass = $this->response["status"] ? "text-success" : "text-error";
        return "<p class='" . $statusClass . "'>" . $this->response["message"] . "</p>";
    }

}