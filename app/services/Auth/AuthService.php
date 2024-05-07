<?php

namespace app\services\Auth;

use enums\HttpStatusCodes;
use app\services\MoySkladService;
use app\validators\UserValidator;

class AuthService
{
	public function __construct(private readonly MoySkladService $moySkladService)
	{
	}

	/**
	 * Авторизация
	 *
     * @param array $loginData Данные авторизации
     * @return array Результат авторизации
     */
    public function login(array $loginData): array
    {
        $validateErrors = UserValidator::validateLogin($loginData);
        if (count($validateErrors) > 0) {
            return [
                'success' => false,
                'errors' => $validateErrors,
                'status' => HttpStatusCodes::UNPROCESSABLE->value
            ];
        }

		$accessTokenResult = $this->moySkladService->auth($loginData['username'], $loginData['password']);

		// Обрабатываем ответ от Мой склад
		if ($accessTokenResult['success'] && $accessTokenResult['access_token']) {
			$this->setAuthData($loginData['username'], $accessTokenResult['access_token']);
			return ['success' => true];
		// Если запрос прошёл и не получилось авторизоваться, то возвращаем ошибку валидации
		} else if ($accessTokenResult['status'] === HttpStatusCodes::UNAUTHORIZED->value) {
			return [
				'success' => false,
				'errors' => ['username' => 'Неверный логин или пароль'],
				'status' => HttpStatusCodes::UNPROCESSABLE->value
			];
		}

		// В других случаях возвращаем ответ без изменений
		return $accessTokenResult;
    }

	/**
	 * Задаём авторизацию в сессии
	 *
	 * @param string $username
	 * @param string $accessToken
	 * @return void
	 */
	private function setAuthData(string $username, string $accessToken): void
	{
		$_SESSION['username'] = $username;
		$_SESSION['access_token'] = $accessToken;
	}

	/**
	 * Выход из аккаунта
	 *
	 * @return void
	 */
	public function logout(): void
	{
		$_SESSION['username'] = null;
		$_SESSION['access_token'] = null;
	}
}