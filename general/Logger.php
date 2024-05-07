<?php

namespace general;

/**
 * Класс логирования
 */
class Logger
{
	const DIR_NAME = 'log';

	/**
	 * Логирует сообщение в заданный файл
	 *
	 * @param $message
	 * @param string $logFilePath
	 * @return void
	 */
    public static function logMessage($message, string $logFilePath = 'app'): void
	{
        // Путь к директории логов
        $logDirectory = ROOT_PATH . '/' . self::DIR_NAME;
        // Полный путь к файлу лога
        $logPath = $logDirectory . '/' . $logFilePath . '.log';

        // Проверяем, существует ли директория логов, если нет - создаем
        if (!file_exists($logDirectory)) {
            mkdir($logDirectory, 0777, true);
        }

        // Получаем текущую дату и время
        $currentTime = date('Y-m-d H:i:s');
        // Форматируем строку лога
        $log = $currentTime . ' ' . $message . "\n";
        // Добавляем запись в файл лога
        file_put_contents($logPath, $log, FILE_APPEND);
    }

	/**
	 * Логирует ошибку в файл ошибок
	 *
	 * @param string $message
	 * @param string $logFilePath
	 * @return void
	 */
	public static function logError(string $message, string $logFilePath = 'error'): void
	{
		self::logMessage($message, $logFilePath);
	}
}