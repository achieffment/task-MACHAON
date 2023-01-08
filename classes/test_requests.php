<?php

class TestRequests {

    public $url;
    public $token = "f6fdffe48c908deb0f4c3bd36c032e72";
    public $headers = [];

    function __construct($url = "") {
        $this->url = ($url) ? $url : "http" . ((isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on") ? "s" : "") . "://" . $_SERVER["HTTP_HOST"] . "/rest/";
        $this->headers[] = "Authorization: {$this->token}";
    }

    function sendRequestPost($postParams) {
        $ch = curl_init($this->url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->headers);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postParams);
        $html = curl_exec($ch);
        curl_close($ch);
        return $html;
    }

    function sendRequestPut($putParams) {
        $ch = curl_init($this->url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->headers);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($putParams));
        $html = curl_exec($ch);
        curl_close($ch);
        return $html;
    }

    function sendRequestDelete($deleteParams) {
        $ch = curl_init($this->url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->headers);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($deleteParams));
        $html = curl_exec($ch);
        curl_close($ch);
        return $html;
    }

    function sendRequestGetLink($link) {
        $ch = curl_init($this->url . "?getlink=$link");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->headers);
        curl_setopt($ch, CURLOPT_HEADER, false);
        $html = curl_exec($ch);
        curl_close($ch);
        return $html;
    }

    function sendRequestGetAll($sort = "") {
        $sortString = ($sort) ? "&$sort" : "";
        $ch = curl_init($this->url . "?getall$sortString");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->headers);
        curl_setopt($ch, CURLOPT_HEADER, false);
        $html = curl_exec($ch);
        curl_close($ch);
        return $html;
    }

}