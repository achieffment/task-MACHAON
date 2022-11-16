<?php

class Link {

    public $response = [
        "status" => true,
        "message" => "",
    ];
    private $shortLinkMaxLen = 8;
    private $shortLinkMaxCount;
    private $alphabet = [];
    private $alphabetCount;
    private $table_names = [];
    private $redirectLink;

    public function __construct($table_names, $db_conn, $redirectLink)
    {
        $this->table_names = $table_names;
        $this->db_conn = $db_conn;
        $this->alphabet = array_merge(range('A', 'Z'), range('a', 'z'), range(0, 9));
        $this->alphabetCount = count($this->alphabet);
        $this->shortLinkMaxCount = $this->gmp_fact($this->alphabetCount) / ( $this->gmp_fact($this->alphabetCount - $this->shortLinkMaxLen) - $this->gmp_fact($this->shortLinkMaxLen) ); // Максимально возможное число коротких ссылок (факториал по общему количеству элементов делим на факториал разницы общего числа элементов с максимальным числом, умноженным на факториал максимального числа ( n! / (n-k)! * k! ))
        $this->redirectLink = $redirectLink;
    }

    // Создание короткой ссылки
    public function createShortLink($link) {
        $link = $this->encodeLink($link);
        if ($link !== false) {
            $i = 0;
            $shortLink = $this->generateShortLink();
            while (!$this->checkShortLink($shortLink) && $this->response["status"] !== false && $i < ($this->shortLinkMaxCount - 1)) {
                $shortLink = $this->generateShortLink();
                $i++;
            }
            if ($this->response["status"] !== false && $i == $this->shortLinkMaxCount - 1)
                $this->setResponce(false, "Достигнуто максимально доступное количество ссылок, обратитесь к администратору");
            else if ($this->response["status"] !== false) {
                if ($this->saveShortLink($link, $shortLink))
                    $this->setResponce(true, "Созданная короткая ссылка: " . $this->getLinkHtml($shortLink));
            }
        }
    }

    // Генерация короткой ссылки из заданных символов в заданную длину
    private function generateShortLink() {
        $shortLink = "";
        for ($i = 0; $i < $this->shortLinkMaxLen; $i++) {
            $shortLink .= $this->alphabet[rand(0, $this->alphabetCount - 1)];
        }
        return $shortLink;
    }

    // Проверка ссылки на наличие в бд
    private function checkShortLink($shortLink) {
        $query = "SELECT id FROM {$this->table_names["links"]} WHERE short_link = '{$shortLink}'";
        $result = $this->db_conn->query($query);
        if ($result) {
            if ($result->num_rows == 0)
                return true;
        } else
            $this->setResponce(false, "Произошла ошибка базы данных при обработке запроса проверки наличия: " . $this->db_conn->error);
        return false;
    }

    // Сохранение полученной короткой ссылки
    private function saveShortLink($link, $shortLink) {
        $query = "INSERT INTO {$this->table_names["links"]} (link, short_link) VALUES('{$link}', '{$shortLink}')";
        $result = $this->db_conn->query($query);
        if ($result) {
            $this->setResponce(true);
            return true;
        } else
            $this->setResponce(false, "Произошла ошибка базы данных при обработке запроса сохранения: " . $this->db_conn->error);
        return false;
    }

    // Получение всех записей
    public function getAll($order_field, $order) {
        $sort = "";
        if ($order_field && $order)
            $sort = "ORDER BY {$order_field} $order";
        $query = "SELECT * FROM {$this->table_names['links']} {$sort}";
        $result = $this->db_conn->query($query);
        if ($result) {
            if ($result->num_rows > 0) {
                $links = [];
                while ($row = $result->fetch_assoc()) {
                    $links[] = $row;
                }
                return $links;
            } else
                $this->setResponce(false, "Список пуст.");
        } else
            $this->setResponce(false, "Произошла ошибка базы данных при обработке запроса получения: " . $this->db_conn->error);
        return false;
    }

    // Получение полной ссылки
    public function getFullLink($short_link) {
        $short_link = $this->validate($short_link);
        if ($short_link !== false) {
            $query = "SELECT link FROM {$this->table_names["links"]} WHERE short_link = '{$short_link}'";
            $result = $this->db_conn->query($query);
            if ($result) {
                if ($result->num_rows > 0) {
                    $result = $result->fetch_assoc();
                    $result = $result["link"];
                    $this->setResponce(true, "Ваша полная ссылка: " . $result);
                } else
                    $this->setResponce(false, "Введенной ссылки не существует");
            } else
                $this->setResponce(false, "Произошла ошибка базы данных при обработке запроса получения: " . $this->db_conn->error);
        }
    }

    // Смена ссылки по её короткому коду (Почему-то метод affected_rows не определялся, сделал по простому)
    public function changeLink($link, $shortLink) {
        $link = $this->validate($link);
        if ($link !== false)
            $shortLink = $this->validate($shortLink);
                if ($shortLink !== false) {
                    $query = "UPDATE {$this->table_names['links']} SET link = '{$link}' WHERE short_link = '{$shortLink}'";
                    $result = $this->db_conn->query($query);
                    if ($result)
                        $this->setResponce(true, "Запись успешна обновлена, новое значение - " . $link . ", по короткой ссылке - " . $shortLink);
                    else
                        $this->setResponce(false, "Произошла ошибка базы данных при обработке запроса изменения: " . $this->db_conn->error);
                }
    }

    // Удаление ссылки (Почему-то метод affected_rows не определялся, сделал по простому)
    public function removeLink($id) {
        $id = htmlspecialchars(strip_tags($id));
        if (strlen($id) > 0) {
            $id = intval($id);
            if ($id >= 0) {
                $query = "DELETE FROM {$this->table_names["links"]} WHERE id = {$id}";
                $result = $this->db_conn->query($query);
                if ($result)
                    $this->setResponce(true, "Запись успешно удалена");
                else
                    $this->setResponce(false, "Произошла ошибка базы данных при обработке запроса удаления: " . $this->db_conn->error);
            } else
                $this->setResponce(false, "Число должно быть больше или равно 0");
        } else
            $this->setResponce(false, "Введено некорректное значение");
    }

    private function setResponce($status, $message = "") {
        $this->response["status"] = $status;
        $this->response["message"] = $message;
    }

    public function returnResponse() {
        $statusClass = $this->response["status"] ? "text-success" : "text-error";
        return "<p class='" . $statusClass . "'>" . $this->response["message"] . "</p>";
    }

    // Вычисление факториала (на опенсервере не было модуля, описал отдельно)
    private function gmp_fact($number) {
        $factorial = 1;
        for ($i = 1; $i < $number + 1; $i++)
            $factorial *= $i;
        return $factorial;
    }

    public function validate($link) {
        $link = htmlspecialchars(strip_tags($link));
        if (!$link)
            $this->setResponce(false, "Ссылка вернулась пустой");
        else if (strlen($link) > 100)
            $this->setResponce(false, "Ссылка не должна превышать 100 символов");
        else {
            $this->setResponce(true);
            return $link;
        }
        return false;
    }

    private function encodeLink($link) {
        $link = $this->validate($link);
        if ($link !== false) {
            $this->setResponce(true);
            return urlencode($link);
        }
        return false;
    }

    public function getLinkHtml($shortLink) {
        $link = $this->redirectLink . "?l=" . $shortLink;
        return "<a href='$link'>{$link}</a>";
    }

    public function getLinkAndRedirect($shortLink) {
        $query = "SELECT link FROM {$this->$table_names['links']} WHERE short_link = '{$shortLink}'";
        $result = $this->db_conn->query($query);
        if ($result) {
            $result = $result->fetch_assoc();
            $result = $result["link"];
            if ($result) {
                $this->setResponce(true);
                header("Location: " . $result);
            } else
                $this->setResponce(false, "Произошла ошибка, вернулась пустая ссылка");
        } else {
            $this->setResponce(false, "Произошла ошибка базы данных при обработке запроса: " . $this->db_conn->error);
        }
    }

}