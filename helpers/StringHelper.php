<?php

namespace helpers;

/**
 * Класс-помощник для работы со строками
 */
class StringHelper
{
	/**
	 * Извлечение uuid из строки
	 *
	 * @param string $str
	 * @return string|null
	 */
	public static function extractUuidFromStr(string $str): ?string
	{
		$pattern = "/[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}/";

		if (preg_match($pattern, $str, $matches)) {
			return $matches[0];
		}

		return null;
	}

	/**
	 * Проверяет, является ли строка валидным UUID.
	 *
	 * @param string $str Проверяемая строка.
	 * @return bool Возвращает true, если строка является валидным UUID, иначе false.
	 */
	public static function isValidUuid(string $str): bool
	{
		$pattern = "/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i";

		return (bool) preg_match($pattern, $str);
	}

	/**
	 * @param float|int $amount
	 * @return string
	 */
	public static function formatCurrency(float|int $amount): string
	{
		return number_format($amount / 100, 2, ',', ' ');
	}
}