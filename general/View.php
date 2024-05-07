<?php

namespace general;

/**
 * Класс отвечающий за работу с представлением
 */
class View
{
	/**
	 * Отрисовывает представление с шаблоном
	 *
	 * @param string $viewRoute Маршрут до view разделенный символом .
	 * @param array $variables Переменные для передачи в маршрут
	 * @return void
	 */
	public static function render(string $viewRoute, array $variables = []): void
	{
		extract($variables);

		// Преобразование viewRoute в путь к файлу представления
		$viewRouteParts = explode('.', $viewRoute);
		$subFolder = implode('/', $viewRouteParts);
		$viewFileName = array_pop($viewRouteParts);  // Получаем последний элемент как имя файла
		$subFolderPath = implode('/', $viewRouteParts); // Путь к подпапке

		$view = ROOT_PATH . "/app/views/{$subFolderPath}/{$viewFileName}.php";
		$template = ROOT_PATH . '/app/views/template.php';

		if (file_exists($view) && file_exists($template)) {
			include $template;
		} else {
			Logger::logError("Template or view file not found: {$template}");
			Router::redirectToError(500);
		}
	}

	/**
	 * @param string $viewRoute Маршрут до view разделенный символом .
	 * @param array $variables Переменные для передачи в маршрут
	 * @return void
	 */
	public static function renderPageWithoutTemplate(string $viewRoute, array $variables = []): void
	{
		extract($variables);

		$viewRouteParts = explode('.', $viewRoute);
		$subFolder = implode('/', $viewRouteParts);
		$viewFileName = array_pop($viewRouteParts);  // Получаем последний элемент как имя файла
		$subFolderPath = implode('/', $viewRouteParts); // Путь к подпапке

		$view = ROOT_PATH . "/app/views/{$subFolderPath}/{$viewFileName}.php";

		if (file_exists($view)) {
			include $view;
		} else {
			Logger::logError("View file not found: {$template}");
			Router::redirectToError(500);
		}
	}
}