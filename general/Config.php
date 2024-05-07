<?php
namespace general;

use Exception;

/**
 * Класс управления конфигурацией
 */
class Config
{
	private static array $config = [];

	/**
	 * Загружает файл конфигурации
	 *
	 * @param string $path Путь до файла конфигурации
	 * @return void
	 * @throws Exception
	 */
	public static function load(string $path): void
	{
		if (!file_exists($path)) {
			throw new Exception("Файл конфигурации не найден: {$path}");
		}

		self::$config = require $path;
	}

	/**
	 * Получает часть конфигурации по ключу с разделителем .
	 *
	 * @param string|null $key Ключ конфигурации
	 * @return array|mixed|null
	 */
	public static function get(?string $key = null): mixed
	{
		if ($key === null) {
			return self::$config;
		}

		$segments = explode('.', $key);
		$result = self::$config;

		foreach ($segments as $segment) {
			if (!isset($result[$segment])) {
				return null;
			}
			$result = $result[$segment];
		}

		return $result;
	}
}