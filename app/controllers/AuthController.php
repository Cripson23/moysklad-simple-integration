<?php

namespace app\controllers;

use general\Router;
use general\View;
use app\factories\ServiceFactory;
use app\services\Auth\AuthService;

class AuthController extends BaseController
{
    private readonly AuthService $authService;

	public function __construct()
	{
        $this->authService = new AuthService(ServiceFactory::createMoySkladService());
		parent::__construct();
	}

	/**
	 * Отображение страницы авторизации
	 *
	 * @return void
	 */
	public function view(): void
	{
		$authData = $this->getAuthData();

		if ($authData['status']) {
			Router::redirect('/');
			exit;
		}

		View::render('auth.view', ['title' => 'Авторизация']);
	}

	/**
	 * Обработка REST запроса на авторизацию
	 *
	 * @return void
	 */
	public function login(): void
	{
		$loginData = $this->postParams;
		$result = $this->authService->login($loginData);
		$this->sendJsonResponse($result);
	}

	/**
	 * Обработка REST запроса на разлогирование
	 *
	 * @return void
	 */
	public function logout(): void
	{
		$this->authService->logout();
		$this->sendJsonResponse(['success' => true]);
	}
}