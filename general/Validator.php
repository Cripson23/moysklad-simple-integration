<?php

namespace general;

/**
 * Класс содержащий основные методы валидации
 */
class Validator
{
	/**
	 * Проверка длины строки
	 *
	 * @param string $string Строка для валидации
	 * @param int $min Минимальная длина
	 * @param int $max Максимальная длина
	 * @return bool Результат валидации
	 */
    public static function validateLength(string $string, int $min, int $max): bool
    {
        $length = strlen($string);
        return $length >= $min && $length <= $max;
    }

	/**
	 * Валидация электронной почты
	 *
	 * @param string $email Электронная почта
	 * @return mixed Результат валидации
	 */
    public static function validateEmail(string $email): mixed
	{
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }

	/**
	 * Валидация номера телефона
	 *
	 * @param string $phoneNumber Номер телефона
	 * @return false|int Результат валидации
	 */
    public static function validatePhoneNumber(string $phoneNumber): bool|int
	{
        $pattern = '/^\+7[0-9]{10}$/';
        return preg_match($pattern, $phoneNumber);
    }

	/**
	 * Валидация пароля
	 *
	 * @param string $password Пароль
	 * @param int $min Минимальная длина
	 * @param int $max Максимальная длина
	 * @return false|int Результат валидации
	 */
    public static function validatePassword(string $password, int $min = 8, int $max = 32): bool|int
	{
        // Проверка на минимальную и максимальную длину, наличие хотя бы одной цифры, одной заглавной буквы
		$pattern = "/^(?=.*\d)(?=.*[A-Z]).{{$min},{$max}}$/";
        return preg_match($pattern, $password);
    }
}