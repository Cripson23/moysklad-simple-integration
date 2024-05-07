<?php

namespace helpers;

use DateTime;
use Exception;

/**
 * Класс-помощник для работы с датой и временем
 */
class DateTimeHelper
{
	/**
	 * Конвертирует дату и время в определенный формат
	 *
	 * @param string $string Строка даты и времени для форматирования
	 * @param string $format Строка формата для форматирования
	 * @return string Отформатированная строка
	 * @throws Exception
	 */
	public static function convertFormatDateTime(string $string, string $format): string
	{
		$date = new DateTime($string);
		return $date->format($format);
	}
}