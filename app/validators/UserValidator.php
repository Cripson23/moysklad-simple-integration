<?php

namespace app\validators;

class UserValidator
{
	/**
	 * Валидирует входные параметры
	 *
	 * @param array $loginData
	 * @return array
	 */
	public static function validateLogin(array $loginData): array
	{
		$errors = [];

		if (empty($loginData['username']) || empty($loginData['password'])) {
			// username
			if (empty($loginData['username'])) {
				$errors['username'][] = 'Необходимо заполнить Логин';
			}

			// password
			if (empty($loginData['password'])) {
				$errors['password'][] = 'Необходимо заполнить Пароль';
			}
		}

		return $errors;
	}
}