<?php

class Link {

    public $response = [
        "status" => true,
        "message" => "",
    ];
    public $shortLinkMaxLen = 8;
    public $shortLinkMaxCount;
    public $alphabet = [];
    public $alphabetCount;
    public $table_names = [];
    public $redirectLink;

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
    public function createShortLink($link, $html = true) {
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
                if ($this->saveShortLink($link, $shortLink)) {
                    $this->setResponce(true, "Созданная короткая ссылка: " . (($html === true) ? $this->getLinkHtml($shortLink) : $shortLink));
                    return $shortLink;
                }
            }
        }
        return false;
    }

    // Генерация короткой ссылки из заданных символов в заданную длину
    public function generateShortLink() {
        $shortLink = "";
        for ($i = 0; $i < $this->shortLinkMaxLen; $i++) {
            $shortLink .= $this->alphabet[rand(0, $this->alphabetCount - 1)];
        }
        return $shortLink;
    }

    // Проверка ссылки на наличие в бд
    public function checkShortLink($shortLink) {
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
    public function saveShortLink($link, $shortLink) {
        $query = "INSERT INTO {$this->table_names["links"]} (link, short_link) VALUES('{$link}', '{$shortLink}')";
        $result = $this->db_conn->query($query);
        if ($result) {
            $this->setResponce(true);
            return true;
        } else
            $this->setResponce(false, "Произошла ошибка базы данных при обработке запроса сохранения: " . $this->db_conn->error);
        return false;
    }

    public function makeArSort() {
        $sort = [];
        if (isset($_GET["sort"]) && $_GET["sort"] && isset($_GET["order"]) && $_GET["order"]) {
            $sort = htmlspecialchars(strip_tags($_GET["sort"]));
            $order = htmlspecialchars(strip_tags($_GET["order"]));
            if ($sort == "id" || $sort == "link" || $sort == "short_link" || $sort == "redirectLink")
                if ($order == "asc" || $order == "desc") {
                    $sort = ["sort" => $sort, "order" => $order];
                    return $sort;
                }
        }
        return false;
    }

    // Получение всех записей
    public function getAll($sort) {
        $sort = ($sort !== false) ? "ORDER BY {$sort["sort"]} {$sort["order"]}" : "";
        $query = "SELECT * FROM {$this->table_names['links']} {$sort}";
        $result = $this->db_conn->query($query);
        if ($result) {
            if ($result->num_rows > 0) {
                $links = [];
                while ($row = $result->fetch_assoc()) {
                    $link = $row;
                    $link["link"] = urldecode($link["link"]);
                    $links[] = $link;
                }
                return $links;
            } else
                $this->setResponce(false, "Список пуст.");
        } else
            $this->setResponce(false, "Произошла ошибка базы данных при обработке запроса получения: " . $this->db_conn->error);
        return false;
    }

    public function getLinkInfo($request, $html = true) {
        $request = $this->validate($request);
        if ($request !== false) {
            $result = $this->getLinkInfoBy("link", $request, $html);
            if ($result === false && $this->response["status"] !== false) {
                $result = $this->getLinkInfoBy("short_link", $request, $html);
                if ($result === false && $this->response["status"] !== false) {
                    $result = $this->getLinkInfoBy("id", $request, $html);
                    if ($result === false)
                        $this->setResponce(false, "Не удалось получить информацию по запросу");
                }
            }
            if ($result !== false && $this->response["status"] !== false)
                return $result;
        }
        return false;
    }

    public function getLinkInfoBy($column, $value, $html = true) {
        $query = "SELECT * FROM {$this->table_names["links"]} WHERE $column = '{$value}'";
        $result = $this->db_conn->query($query);
        if ($result) {
            if ($result->num_rows > 0) {
                $result = $result->fetch_assoc();
                $resultString = "";
                $i = 0;
                $count = count($result);
                foreach ($result as $key => $value) {
                    if ($html)
                        $resultString .= "<br>[$key] = $value";
                    else
                        $resultString .= "[$key] = $value" . (($i < $count - 1) ? ", " : "" );
                    $i++;
                }
                $this->setResponce(true, "Информация о ссылке: " . $resultString);
                return $result;
            }
        } else
            $this->setResponce(false, "Произошла ошибка базы данных при обработке запроса получения: " . $this->db_conn->error);
        return false;
    }

    // Получение полной ссылки
    public function getFullLink($short_link, $html = true) {
        $short_link = $this->validate($short_link);
        if ($short_link !== false) {
            $query = "SELECT link FROM {$this->table_names["links"]} WHERE short_link = '{$short_link}'";
            $result = $this->db_conn->query($query);
            if ($result) {
                if ($result->num_rows > 0) {
                    $result = $result->fetch_assoc();
                    $result = $result["link"];
                    $result = urldecode($result);
                    if ($html === true)
                        $result = "<a href='$result'>$result</a>";
                    $this->setResponce(true, "Ваша полная ссылка: " . $result);
                    return $result;
                } else
                    $this->setResponce(false, "Введенной ссылки не существует");
            } else
                $this->setResponce(false, "Произошла ошибка базы данных при обработке запроса получения: " . $this->db_conn->error);
        }
        return false;
    }

    // Смена ссылки по её короткому коду (Почему-то метод affected_rows не определялся, сделал по простому)
    public function changeLink($link, $shortLink, $html = true) {
        $link = $this->validate($link);
        if ($link !== false) {
            $shortLink = $this->validate($shortLink);
            if ($shortLink !== false) {
                $query = "SELECT id FROM {$this->table_names["links"]} WHERE short_link = '{$shortLink}'";
                $result = $this->db_conn->query($query);
                if ($result) {
                    if ($result->num_rows > 0) {
                        $query = "UPDATE {$this->table_names['links']} SET link = '{$link}' WHERE short_link = '{$shortLink}'";
                        $result = $this->db_conn->query($query);
                        if ($html) {
                            $link = "<a href='$link'>$link</a>";
                            $shortLink = "<a href='{$this->getLink($shortLink)}'>$shortLink</a>";
                        }
                        if ($result) {
                            $this->setResponce(true, "Запись успешна обновлена, новое значение - " . $link . ", по короткой ссылке - " . $shortLink);
                            return $this->getLink($shortLink);
                        } else
                            $this->setResponce(false, "Произошла ошибка базы данных при обработке запроса изменения: " . $this->db_conn->error);
                    } else
                        $this->setResponce(false, "Переданной ссылки не существует");
                } else
                    $this->setResponce(false, "Произошла ошибка базы данных при обработке запроса проверки существования ссылки: " . $this->db_conn->error);
            }
        }
        return false;
    }

    // Удаление ссылки (Почему-то метод affected_rows не определялся, сделал по простому)
    public function removeLink($id) {
        $id = htmlspecialchars(strip_tags($id));
        if (strlen($id) > 0) {
            $id = intval($id);
            if ($id >= 0) {
                $query = "SELECT id FROM {$this->table_names["links"]} WHERE id = '{$id}'";
                $result = $this->db_conn->query($query);
                if ($result) {
                    if ($result->num_rows > 0) {
                        $query = "DELETE FROM {$this->table_names["links"]} WHERE id = {$id}";
                        $result = $this->db_conn->query($query);
                        if ($result)
                            $this->setResponce(true, "Запись успешно удалена");
                        else
                            $this->setResponce(false, "Произошла ошибка базы данных при обработке запроса удаления: " . $this->db_conn->error);
                    } else
                        $this->setResponce(false, "Данного id не существует");
                } else
                    $this->setResponce(false, "Произошла ошибка базы данных при обработке запроса проверки существования id: " . $this->db_conn->error);
            } else
                $this->setResponce(false, "Число должно быть больше или равно 0");
        } else
            $this->setResponce(false, "Введено некорректное значение");
    }

    public function setResponce($status, $message = "") {
        $this->response["status"] = $status;
        $this->response["message"] = $message;
    }

    public function returnResponse() {
        $statusClass = $this->response["status"] ? "text-success" : "text-error";
        return "<p class='" . $statusClass . "'>" . $this->response["message"] . "</p>";
    }

    // Вычисление факториала (на опенсервере не было модуля, описал отдельно)
    public function gmp_fact($number) {
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

    public function encodeLink($link) {
        $link = $this->validate($link);
        if ($link !== false) {
            $this->setResponce(true);
            return urlencode($link);
        }
        return false;
    }

    public function getLink($shortLink) {
        $link = $this->redirectLink . "?l=" . $shortLink;
        return $link;
    }

    public function getLinkHtml($shortLink) {
        $link = $this->redirectLink . "?l=" . $shortLink;
        return "<a href='$link'>{$link}</a>";
    }

    public function getLinkAndRedirect($shortLink) {
        $query = "SELECT link FROM {$this->table_names['links']} WHERE short_link = '{$shortLink}'";
        $result = $this->db_conn->query($query);
        if ($result) {
            $result = $result->fetch_assoc();
            $result = $result["link"];
            $result = urldecode($result);
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