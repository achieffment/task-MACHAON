<?php

class Link {

    public $response = [];
    private $link;
    private $shortLinkMaxLen = 8;
    private $shortLinkMaxCount;
    private $alphabet = [];
    private $alphabetCount;
    private $table_names = [];

    public function __construct($table_names, $db_conn)
    {
        $this->table_names = $table_names;
        $this->db_conn = $db_conn;
        $this->response["status"] = false;
        $this->response["message"] = "";
        $this->alphabet = array_merge(range('A', 'Z'), range('a', 'z'), range(0, 9));
        $this->alphabetCount = count($this->alphabet);
        $this->shortLinkMaxCount = $this->gmp_fact($this->alphabetCount) / ( $this->gmp_fact($this->alphabetCount - $this->shortLinkMaxLen) - $this->gmp_fact($this->shortLinkMaxLen) ); // Максимально возможное число коротких ссылок (факториал по общему количеству элементов делим на факториал разницы общего числа элементов с максимальным числом, умноженным на факториал максимального числа ( n! / (n-k)! * k! ))
    }

    // Создание короткой ссылки
    public function createShortLink($link) {
        $this->encodeLink($link);
        if ($this->response["status"] == "true") {
            $shortLink = $this->generateShortLink();
            $i = 0;
            $this->response["status"] = "true"; // Устанавливаем статус в true, чтобы можно было отловить момент неудачной операции с бд при проверке
            // Обходим циклом до тех пор, пока ссылка не будет валидной, пока установлен положительный статус (получается подключаться к бд), и пока не переберется максимально возможное число ссылок для заданной длины
            while (!$this->checkShortLink($shortLink) && $this->response["status"] == "true" && $i < ($this->shortLinkMaxCount - 1)) {
                $shortLink = $this->generateShortLink();
                $i++;
            }
            if ($i == $this->shortLinkMaxCount - 1)
                $this->setResponce("false", "Достигнуто максимально доступное количество ссылок, обратитесь к администратору");
            else {
                $this->saveShortLink($this->link, $shortLink);
                if ($this->response["status"] == "true")
                    $this->setResponce("true", "Созданная короткая ссылка: " . $this->getHTMLLink($shortLink));
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
            else
                return false;
        } else
            $this->setResponce("false", "Произошла ошибка базы данных при обработке запроса проверки наличия: " . $this->db_conn->error);
    }

    // Сохранение полученной короткой ссылки
    private function saveShortLink($link, $shortLink) {
        $query = "INSERT INTO {$this->table_names["links"]} (link, short_link) VALUES('{$link}', '{$shortLink}')";
        $result = $this->db_conn->query($query);
        if ($result)
            $this->setResponce("true");
        else
            $this->setResponce("false", "Произошла ошибка базы данных при обработке запроса сохранения: " . $this->db_conn->error);
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
                $this->setResponce("false", "Список пуст.");
        } else
            $this->setResponce("false", "Произошла ошибка базы данных при обработке запроса получения: " . $this->db_conn->error);
        return false;
    }

    // Получение полной ссылки
    public function getFullLink($short_link) {
        if ($short_link) {
            $query = "SELECT link FROM {$this->table_names["links"]} WHERE short_link = '{$short_link}'";
            $result = $this->db_conn->query($query);
            if ($result) {
                if ($result->num_rows > 0) {
                    $result = $result->fetch_assoc();
                    $result = $result["link"];
                    $this->setResponce("true", "Ваша полная ссылка: " . $result);
                } else
                    $this->setResponce("false", "Введенной ссылки не существует");
            } else
                $this->setResponce("false", "Произошла ошибка базы данных при обработке запроса получения: " . $this->db_conn->error);
        } else
            $this->setResponce("false", "Короткая ссылка пуста");
    }

    // Смена ссылки по её короткому коду
    public function changeLink($link, $shortLink) {
        $query = "UPDATE {$this->table_names['links']} SET link = '{$link}' WHERE short_link = '{$shortLink}'";
        $result = $this->db_conn->query($query);
        if ($result) {
            // Почему-то affected_rows не определялся
//            $changedRowsCount = $result->num_rows();
//            if ($changedRowsCount > 0)
                $this->setResponce("true", "Запись успешна обновлена, новое значение - " . $link . ", по короткой ссылке - " . $shortLink);
//            else
//                $this->setResponce("false", "Не найдена информация по данному запросу");
        } else
            $this->setResponce("false", "Произошла ошибка базы данных при обработке запроса изменения: " . $this->db_conn->error);
    }

    // Удаление ссылки (Можно было бы использовать affected_rows, но он не работает
    public function removeLink($id) {
        $query = "DELETE FROM {$this->table_names["links"]} WHERE id = {$id}";
        $result = $this->db_conn->query($query);
        if ($result)
            $this->setResponce("true", "Запись успешно удалена");
        else
            $this->setResponce("false", "Произошла ошибка базы данных при обработке запроса удаления: " . $this->db_conn->error);
    }

    // Для удобства возврата ответа
    private function setResponce($status, $message = "") {
        $this->response["status"] = $status;
        $this->response["message"] = $message;
    }

    // Вычисление факториала (на опенсервере не было модуля, описал отдельно)
    private function gmp_fact($number) {
        $factorial = 1;
        for ($i = 1; $i < $number + 1; $i++)
            $factorial *= $i;
        return $factorial;
    }

    // Валидатор
    public function validate($link) {
        $link = htmlspecialchars(strip_tags($link));
        return $link;
    }

    // Валидатор и преобразование
    private function encodeLink($link) {
        $link = $this->validate($link);
        if ($link) {
            $this->link = urlencode($link);
            $this->setResponce("true");
        } else
            $this->setResponce("false", "Не верно задана ссылка");
    }

    // Получение готовой ссылки для перехода с версткой
    public function getHTMLLink($shortLink) {
        $location = $_SERVER["DOCUMENT_ROOT"] . "/?l=" . $shortLink;
        return "<a href='$location'>{$location}</a>";
    }

}