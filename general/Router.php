<?php

namespace general;

use enums\HttpDefaultErrorMessages;
use Exception;

/**
 * Класс для управления маршрутизацией
 */
class Router
{
    private array $routes = [];

	/**
	 * Регистрирует все маршруты из заданного файла
	 *
	 * @param string $path Путь к файлу маршрутов
	 * @return void
	 * @throws Exception
	 */
	public function registerRoutes(string $path): void
	{
		if (!file_exists($path)) {
			throw new Exception("Файл маршрутов не найден: {$path}");
		}

		$routes = require $path;
		foreach ($routes as $route) {
			$this->add($route[0], $route[1], $route[2], $route[3] ?? 'GET');
		}
	}

	/**
	 * Вызывает метод контроллера в зависимости от маршрута
	 *
	 * @param string $requestUri Uri запроса
	 * @return void
	 */
	public function dispatch(string $requestUri): void
	{
		$requestMethod = $_SERVER['REQUEST_METHOD'];

		foreach ($this->routes as $pattern => $route) {
			if (preg_match($pattern, $requestUri, $params) && $requestMethod == $route['methodType']) {
				array_shift($params); // Удаляем полное совпадение из результатов

				$controllerName = $route['controller'];
				$methodName = $route['method'];

				$controller = new $controllerName();
				call_user_func_array([$controller, $methodName], $params);
				return;
			}
		}

		self::redirectToError(404);
	}

	/**
	 * Перенаправление на маршрут
	 *
	 * @param string $path
	 * @return void
	 */
	public static function redirect(string $path): void
	{
		header('HTTP/1.1 303 See Other');
		header("Location: {$path}");
		exit;
	}

	/**
	 * Перенаправление на страницу ошибки
	 *
	 * @param int $errorCode
	 * @param string|null $errorMessage
	 * @return void
	 */
	public static function redirectToError(int $errorCode, string $errorMessage = null): void
	{
		$errorMessage = $errorMessage ?? HttpDefaultErrorMessages::fromStatusCode($errorCode)->text();

		http_response_code($errorCode);

		View::render('common.error', [
            'title' => 'Ошибка ' . $errorCode,
            'errorCode' => $errorCode,
            'errorMessage' => $errorMessage
        ]);

		exit;
	}

	/**
	 * Добавляет маршрут в объект
	 *
	 * @param string $uri
	 * @param string $controller
	 * @param string $method
	 * @param string $methodType
	 * @return void
	 */
	private function add(string $uri, string $controller, string $method, string $methodType = 'GET'): void
	{
		$this->routes[$uri] = [
			'controller' => $controller,
			'method' => $method,
			'methodType' => strtoupper($methodType)
		];
	}
}