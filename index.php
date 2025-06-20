<?php

error_reporting(E_ALL);

// Определяем, какой модуль подключить
$requestUri = $_SERVER['REQUEST_URI'];
$path = parse_url($requestUri, PHP_URL_PATH);
$pathParts = explode('/', trim($path, '/'));

if (!empty($pathParts[0]) && $pathParts[0] === 'api') {
    // Если путь начинается с 'api', подключаем api.php
    require_once "php/api.php";
} else if (!empty($pathParts[0]) && $pathParts[0] === 'req') {
    require_once "php/req.php";
} else {
    // В противном случае, подключаем render.php
    require_once "php/render.php";
}

?>