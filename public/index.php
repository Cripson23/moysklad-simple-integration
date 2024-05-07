<?php

// Подключение bootstrap.php и получение маршрутизатора
$router = require_once __DIR__ . '/../bootstrap.php';

// Получение пути из запроса, убирая начальный и конечный слэш
$requestUri = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');

// Отправка запроса на обработку
$router->dispatch($requestUri);