<?php

// Определение корневой директории
const ROOT_PATH = __DIR__;

// Подключение Composer Autoload
require_once ROOT_PATH . '/vendor/autoload.php';

use general\Config;
use general\Logger;
use general\Router;
use Dotenv\Dotenv;

$router = null;

try {
	// Загрузка переменных окружения из .env
	$dotenv = Dotenv::createImmutable(ROOT_PATH);
	$dotenv->load();

	// Загрузка конфигурации
	Config::load(ROOT_PATH . '/config.php');

	// Инициализация маршрутизатора
	$router = new Router();
	$router->registerRoutes(ROOT_PATH . '/routes.php');
} catch (Exception $e) {
	Logger::logError($e->getMessage());
	Router::redirectToError(500);
} finally {
	// Возвращаем Router для дальнейшего использования
	return $router;
}