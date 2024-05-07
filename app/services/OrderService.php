<?php

namespace app\services;

use Exception;
use enums\HttpStatusCodes;
use general\Logger;
use helpers\DateTimeHelper;
use helpers\NumbersHelper;
use helpers\StringHelper;
use app\services\Auth\AuthService;
use app\validators\OrderValidator;
use app\factories\ServiceFactory;

class OrderService
{
    private readonly MoySkladService $moySkladService;
    private readonly AuthService $authService;

	public function __construct() {
        $this->moySkladService = ServiceFactory::createMoySkladService();
        $this->authService = new AuthService($this->moySkladService);
	}

	/**
	 * Получение списка заказов покупателей
	 *
	 * @param string $accessToken Токен авторизации
	 * @return array
	 * @throws Exception
	 */
	public function getOrdersList(string $accessToken): array
	{
		$ordersListResult = $this->moySkladService->getCustomerOrders($accessToken);

		if ($ordersListResult['success']) {
			$ordersListResult = $this->prepareOrderList($accessToken, $ordersListResult['orders']);
		}

		// Разлогин если токен неактуален или при ошибке авторизации в Мой склад
		if (!$ordersListResult['success'] && $ordersListResult['status'] == HttpStatusCodes::UNAUTHORIZED->value) {
			$this->authService->logout();
		}

		return $ordersListResult;
	}

	/**
	 * @param string $accessToken
	 * @param array $stateUpdateData
	 * @return array|true[]
	 */
	public function updateOrderState(string $accessToken, array $stateUpdateData): array
	{
		$validateErrors = OrderValidator::validateStateUpdate($stateUpdateData);
		if (count($validateErrors) > 0) {
			return [
				'success' => false,
				'errors' => $validateErrors,
				'status' => HttpStatusCodes::UNPROCESSABLE->value
			];
		}

		$updateOrderStateResult = $this->moySkladService->updateCustomerOrderState(
            $accessToken,
			$stateUpdateData['order_uuid'],
			$stateUpdateData['state_uuid']
		);

		// Разлогин если токен неактуален или при ошибке авторизации в Мой склад
		if (!$updateOrderStateResult['success'] && $updateOrderStateResult['status'] == HttpStatusCodes::UNAUTHORIZED->value) {
			$this->authService->logout();
		}

		return $updateOrderStateResult;
	}

    /**
     * Получение данных о заказе
     *
     * @param string $accessToken
     * @param string $orderUuid
     * @return array
     * @throws Exception
     */
    public function getOrderLastModifiedDate(string $accessToken, string $orderUuid): array
    {
        $validateErrors = OrderValidator::validateGet($orderUuid);
        if (count($validateErrors) > 0) {
            return [
                'success' => false,
                'errors' => $validateErrors,
                'status' => HttpStatusCodes::UNPROCESSABLE->value
            ];
        }

        $getOrderResult = $this->moySkladService->getCustomerOrder($accessToken, $orderUuid);

        // Разлогин если токен неактуален или при ошибке авторизации в Мой склад
        if (!$getOrderResult['success'] && $getOrderResult['status'] == HttpStatusCodes::UNAUTHORIZED->value) {
            $this->authService->logout();
        }

        if ($getOrderResult['success']) {
            return [
                'success' => true,
                'updated_at' => DateTimeHelper::convertFormatDateTime($getOrderResult['order']['updated'], 'd.m.Y H:i')
            ];
        }

        return $getOrderResult;
    }

    /** ---------- Приватные методы ----------- */
	/**
	 * Постобработка списка заказов
	 *
	 * @param string $accessToken
	 * @param array $ordersList
	 * @return array|null
	 */
	private function prepareOrderList(string $accessToken, array $ordersList): ?array
	{
        $getMetaDataResult = $this->moySkladService->getCustomerOrdersMetaData($accessToken);
        if (!$getMetaDataResult['success'] || !isset($getMetaDataResult['order_meta_data']['states'])) {
            return [
                'success' => false,
                'status' => $getMetaDataResult['status']
            ];
        }

        $states = $this->getPreparedStatesList($getMetaDataResult['order_meta_data']['states']);

		$resultOrders = [
			'success' => true,
			'orders' => [
				'items' => [],
				'items_count' => count($ordersList),
				'sum_total' => 0,
                'states' => $states
			],
		];

		try {
			$sumTotal = 0;
			foreach ($ordersList as $order) {
                $uuids = [
                    'agent' => StringHelper::extractUuidFromStr($order['agent']['meta']['href']),
                    'organization' => StringHelper::extractUuidFromStr($order['organization']['meta']['href']),
                    'currency' => StringHelper::extractUuidFromStr($order['rate']['currency']['meta']['href'])
                ];

                $additionalDataResult = $this->moySkladService->getCustomerOrderAdditionalData($accessToken, $uuids);
                if (!$additionalDataResult['success']) {
                    return [
                        'success' => false,
                        'status' => $additionalDataResult['status']
                    ];
                }

                $additionalData = $additionalDataResult['data'];

				$created_at = DateTimeHelper::convertFormatDateTime($order['created'], 'd.m.Y H:i');
				$updated_at = DateTimeHelper::convertFormatDateTime($order['updated'], 'd.m.Y H:i');
				$stateUuid = StringHelper::extractUuidFromStr($order['state']['meta']['href']);

				$preparedOrder = [
					'id' => $order['id'],
					'number' => [
						'link' => $order['meta']['uuidHref'],
						'value' => $order['name'],
					],
					'created_at' => $created_at,
					'agent' => [
						'link' => $order['agent']['meta']['uuidHref'],
						'value' => $additionalData['agent']['name']
					],
					'organization_name' => $additionalData['organization']['name'],
					'sum' => StringHelper::formatCurrency($order['sum']),
					'currency_name' => $additionalData['currency']['name'],
					'updated_at' => $updated_at,
					'state' => array_merge(['id' => $stateUuid], $states[$stateUuid])
				];

				$resultOrders['orders']['items'][] = $preparedOrder;
				$sumTotal += floor($order['sum'] * $additionalData['currency']['rate']);
			}
		} catch (Exception $e) {
			Logger::logError("Ошибка при обработке заказов: {$e}");
            return [
                'success' => false,
                'status' => HttpStatusCodes::INTERNAL->value
            ];
		}

		$resultOrders['orders']['sum_total'] = StringHelper::formatCurrency($sumTotal);

		return $resultOrders;
	}

    /**
     * Получение обработанных статусов из метаданных
     *
     * @param array $statesList
     * @return array
     */
    private function getPreparedStatesList(array $statesList): array
    {
        $preparedStates = [];

        foreach ($statesList as $state) {
            $preparedStates[$state['id']] = [
                'name' => $state['name'],
                'color' => NumbersHelper::decimalToHex($state['color'])
            ];
        }

        return $preparedStates;
    }
}