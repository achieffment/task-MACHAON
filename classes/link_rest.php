<?php

class LinkRest extends Link {

    public function __construct($table_names, $db_conn, $redirectLink) {
        parent::__construct($table_names, $db_conn, $redirectLink);
    }

    public function addResultParam($result, $param, $value) {
        if ($value !== false)
            $result[$param] = $value;
        return $result;
    }

    public function setResultJson($params, $values) {
        $result = [];
        if (is_array($params) && is_array($values)) {
            if (count($params) == count($values)) {
                for ($i = 0; $i < count($params); $i++) {
                    $result = $this->addResultParam($result, $params[$i], $values[$i]);
                }
            } else
                $this->setResponce(false, "Количество передаваемых параметров и значений не совпадает");
        } elseif (!is_array($params) && !is_array($values))
            $result = $this->addResultParam($result, $params, $values);
        else
            $this->setResponce(false, "Переданы недопустимые параметры, передать возможно строку и значение как параметр, или строку и значение как массив");
        $result = array_merge($this->response, $result);
        return $result;
    }

    public function returnResultJson($result) {
        header('Content-Type: application/json');
        echo json_encode($result);
    }

}