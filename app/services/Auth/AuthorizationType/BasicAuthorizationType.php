<?php

namespace app\services\Auth\AuthorizationType;

class BasicAuthorizationType implements AuthorizationTypeInterface
{
	/**
	 * Инициализирует и задает значения переменным класса
	 *
	 * @param string $username
	 * @param string $password
	 */
	public function __construct(private readonly string $username, private readonly string $password)
	{
	}

	/**
     * Получение строки заголовка базовой авторизации
     *
	 * @return string
	 */
	public function getAuthorizationHeader(): string
	{
		return 'Authorization: Basic ' . base64_encode($this->username . ':' . $this->password);
	}
}