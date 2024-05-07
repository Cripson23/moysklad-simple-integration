<?php

namespace app\services\Auth\AuthorizationType;

class BearerAuthorizationType implements AuthorizationTypeInterface
{
	/**
	 * Инициализирует и задает значения переменным класса
	 *
	 * @param string $token
	 */
	public function __construct(private readonly string $token)
	{
	}

	/**
     * Получение строки заголовка авторизации через токен
     *
	 * @return string
	 */
	public function getAuthorizationHeader(): string
	{
		return 'Authorization: Bearer ' . $this->token;
	}
}