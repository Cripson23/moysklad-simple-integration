<?php

namespace app\controllers;

use Exception;
use enums\HttpStatusCodes;
use general\Router;
use general\View;
use app\services\OrderService;

class OrdersController extends BaseController
{
	public function __construct(private readonly OrderService $orderService = new OrderService())
	{
		parent::__construct();
	}

	/**
	 * Обработка запроса на получение списка заказов
	 *
	 * @return void
	 * @throws Exception
	 */
    public function index(): void
	{
		$authData = $this->getAuthData();

		if (!$authData['status']) {
			Router::redirect('/auth/login');
		}

		$getOrdersListResult = $this->orderService->getOrdersList($authData['access_token']);

		if (!$getOrdersListResult['success'] || !$getOrdersListResult['orders']) {
			if ($getOrdersListResult['status'] === HttpStatusCodes::UNAUTHORIZED->value) {
				// Пользователь уже разлогинен
				$this->setSessionNotification('auth-error', 'Ошибка авторизации', 'Авторизационная сессия не прошла проверку, пожалуйста, авторизуйтесь снова');
				Router::redirect('/auth/login');
			} else {
				Router::redirectToError(500, 'Не удалось загрузить Заказы покупателя, попробуйте ещё раз');
			}
		}

		View::render('orders.index', [
			'title' => 'Все заказы',
			'orders' => $getOrdersListResult['orders'],
			'username' => $authData['username']
		]);
    }

	/**
     * Обработка REST запроса на обновление статуса заказа
     *
	 * @return void
	 */
	public function updateState(): void
	{
		$authData = $this->getAuthData();

		if (!$authData['status']) {
			$this->sendJsonResponse(['success' => false, 'status' => HttpStatusCodes::UNAUTHORIZED]);
		}

		$result = $this->orderService->updateOrderState($authData['access_token'], $this->postParams);

		$this->sendJsonResponse($result);
	}

    /**
     * Обработка REST запроса на получение последней даты обновления запроса
     *
     * @return void
     * @throws Exception
     */
    public function getLastModifiedDate(): void
    {
        $authData = $this->getAuthData();

        if (!$authData['status']) {
            $this->sendJsonResponse(['success' => false, 'status' => HttpStatusCodes::UNAUTHORIZED]);
        }

        $result = $this->orderService->getOrderLastModifiedDate($authData['access_token'], $this->getParams['order_uuid'] ?? null);

        $this->sendJsonResponse($result);
    }
}