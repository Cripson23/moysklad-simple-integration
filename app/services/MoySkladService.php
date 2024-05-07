<?php

namespace app\services;

use app\services\Auth\AuthorizationType\AuthorizationTypeInterface;
use app\services\Auth\AuthorizationType\BasicAuthorizationType;
use app\services\Auth\AuthorizationType\BearerAuthorizationType;
use enums\HttpStatusCodes;
use general\Cache\CachedInterface;

/**
 * Класс для взаимодействия с Мой склад
 */
class MoySkladService
{
	const BASE_URL = 'https://api.moysklad.ru/api/remap/1.2/';
	const URL_CONFIG = [
		'auth' => 'security/token',
		'order' => 'entity/customerorder',
        'order_meta' => 'entity/customerorder/metadata',
		'agent' => 'entity/counterparty',
		'organization' => 'entity/organization',
		'currency' => 'entity/currency'
	];
    const CACHE_CONFIG = [
        'order_meta' => [
            'key' => 'order_meta_data',
            'ttl' => 86400
        ],
        'order_additional' => [
            'ttl' => 86400
        ]
    ];
	const DEFAULT_ORDER_ORDERS = '?order=created,desc';

	private AuthorizationTypeInterface $authType;

    public function __construct(private readonly CachedInterface $cache)
    {
    }

    /**
	 * Авторизация в Мой склад
	 *
	 * @param string $username Логин
	 * @param string $password Пароль
	 * @return array Результат выполнения авторизации в Мой склад, в случае успеха токен авторизации, иначе http код ошибки
	 */
	public function auth(string $username, string $password): array
	{
		$this->setBasicAuth($username, $password);

		$url = self::BASE_URL . self::URL_CONFIG['auth'];
		$curlRequest = CurlService::createCurlRequest(url: $url, method: 'POST', authorization: $this->authType);
        $result = CurlService::sendCurlRequest($curlRequest);

		if ($result['success']) {
			$accessToken = $result['response']['access_token'] ?? null;
			if ($accessToken) {
				$this->setBearerToken($accessToken);
				return [
					'success' => true,
					'access_token' => $accessToken,
				];
			}
		}

		return [
			'success' => false,
			'status' => $result['http_code'] ?? HttpStatusCodes::INTERNAL->value
		];
	}

	/**
	 * Получение заказов пользователя
	 *
	 * @param string $accessToken Токен доступа
	 * @return array Результат получения заказов покупателей в Мой склад, в случае успеха массив заказов, иначе http код ошибки
	 */
	public function getCustomerOrders(string $accessToken): array
	{
		$this->setBearerToken($accessToken);

		$url = self::BASE_URL . self::URL_CONFIG['order'] . self::DEFAULT_ORDER_ORDERS;
        $curlRequest = CurlService::createCurlRequest(url: $url, authorization: $this->authType);
		$result = CurlService::sendCurlRequest($curlRequest);

		if ($result['success']) {
			$orders = $result['response']['rows'] ?? null;
			if ($orders) {
				return [
					'success' => true,
					'orders' => $orders
				];
			}
		}

		return [
			'success' => false,
			'status' => $result['http_code'] ?? HttpStatusCodes::INTERNAL->value
		];
	}

    /**
     * Получение метаданных для заказов покупателей
     *
     * @param string $accessToken Токен доступа
     * @return array Результат получения метаданных заказов покупателей в Мой склад, в случае успеха данные, иначе http код ошибки
     */
    public function getCustomerOrdersMetaData(string $accessToken): array
    {
        $cacheKey = self::CACHE_CONFIG['order_meta']['key'];
        $cacheTtl = self::CACHE_CONFIG['order_meta']['ttl'];
        $cachedData = $this->cache->get($cacheKey);
        if ($cachedData) {
            return [
                'success' => true,
                'order_meta_data' => $cachedData
            ];
        }

        $this->setBearerToken($accessToken);

        $url = self::BASE_URL . self::URL_CONFIG['order_meta'];
        $curlRequest = CurlService::createCurlRequest(url: $url, authorization: $this->authType);
        $result = CurlService::sendCurlRequest($curlRequest);

        if ($result['success']) {
            $ordersMetaData = $result['response'] ?? null;
            if ($ordersMetaData) {
                $this->cache->set($cacheKey, $ordersMetaData, $cacheTtl);
                return [
                    'success' => true,
                    'order_meta_data' => $ordersMetaData
                ];
            }
        }

        return [
            'success' => false,
            'status' => $result['http_code'] ?? HttpStatusCodes::INTERNAL->value
        ];
    }

    /**
     * Получение: контрагента, организации, валюты для 1 заказа
     *
     * @param string $accessToken Токен доступа
     * @param array $uuids Массив UUID для разных сущностей
     * @return array Результат с данными или ошибками
     */
    public function getCustomerOrderAdditionalData(string $accessToken, array $uuids): array
    {
        $this->setBearerToken($accessToken);

        $urls = [
            'agent' => self::BASE_URL . self::URL_CONFIG['agent'] . '/' . $uuids['agent'],
            'organization' => self::BASE_URL . self::URL_CONFIG['organization'] . '/' . $uuids['organization'],
            'currency' => self::BASE_URL . self::URL_CONFIG['currency'] . '/' . $uuids['currency']
        ];

        $data = [
            'agent' => null,
            'organization' => null,
            'currency' => null,
        ];

        $curlRequests = [];
        foreach ($urls as $key => $url) {
            $cacheKey = "{$key}_{$uuids[$key]}";
            $cachedData = $this->cache->get($cacheKey);
            if ($cachedData) {
                $data[$key] = $cachedData;
            } else {
                // Создаём запросы, если данных нет по ключу в кеше
                $curlRequests[$key] = CurlService::createCurlRequest(url: $url, authorization: $this->authType);
            }
        }

        if (!empty($curlRequests)) {
            $results = CurlService::executeMultiCurl($curlRequests);

            foreach ($results as $key => $result) {
                if ($result['success']) {
                    $data[$key] = $result['response'];
                    // Кешируем успешно полученные данные
                    $this->cache->set("{$key}_{$uuids[$key]}", $result['response'], self::CACHE_CONFIG['order_additional']['ttl']);
                } else {
                    return [
                        'success' => false,
                        'status' => $result['http_code'] ?? HttpStatusCodes::INTERNAL->value,
                        'error_key' => $key
                    ];
                }
            }
        }

        return [
            'success' => true,
            'data' => $data
        ];
    }

    /**
     * Запрос на обновление статуса заказа
     *
     * @param string $accessToken
     * @param string $orderUuid
     * @param string $stateUuid
     * @return array|true[]
     */
    public function updateCustomerOrderState(string $accessToken, string $orderUuid, string $stateUuid): array
    {
        $this->setBearerToken($accessToken);

        $url = self::BASE_URL . self::URL_CONFIG['order'] . '/' . $orderUuid;
        $data = [
            'state' => [
                'meta' => [
                    'href' => self::BASE_URL . self::URL_CONFIG['order_meta'] . '/states/' . $stateUuid,
                    'type' => 'state',
                    'mediaType' => 'application/json'
                ]
            ]
        ];

        $curlRequest = CurlService::createCurlRequest(url: $url, data: $data, method: 'PUT', authorization: $this->authType);
        $result = CurlService::sendCurlRequest($curlRequest);

        if ($result['success'] && isset($result['response'])) {
            return ['success' => true];
        }

        return [
            'success' => false,
            'status' => $result['http_code'] ?? HttpStatusCodes::INTERNAL->value
        ];
    }

    /**
     * Получение информации о заказе
     *
     * @param string $accessToken
     * @param string $orderUuid
     * @return array
     */
    public function getCustomerOrder(string $accessToken, string $orderUuid): array
    {
        $this->setBearerToken($accessToken);

        $url = self::BASE_URL . self::URL_CONFIG['order'] . '/' . $orderUuid;
        $curlRequest = CurlService::createCurlRequest(url: $url, authorization: $this->authType);
        $result = CurlService::sendCurlRequest($curlRequest);

        if ($result['success']) {
            return [
                'success' => true,
                'order' => $result['response']
            ];
        }

        return [
            'success' => false,
            'status' => $result['http_code'] ?? HttpStatusCodes::INTERNAL->value
        ];
    }

    /**
     * Устанавливает авторизацию на Basic auth.
     *
     * @param string $login Логин
     * @param string $password Пароль
     */
    private function setBasicAuth(string $login, string $password): void
    {
        $this->authType = new BasicAuthorizationType($login, $password);
    }

    /**
     * Устанавливает авторизацию на Bearer auth.
     *
     * @param string $token Токен
     */
    private function setBearerToken(string $token): void
    {
        $this->authType = new BearerAuthorizationType($token);
    }

	/**
	 * Получение информации об контрагенте
     *
	 * @param string $accessToken Токен доступа
	 * @param string $uuid Идентификатор контрагента
	 * @return array Результат получения данных о контрагенте, в случае успеха данные, иначе http код ошибки
	 */
	/*public function getAgentByUuid(string $accessToken, string $uuid): array
	{
		$this->setBearerToken($accessToken);

		$url = self::BASE_URL . self::URL_CONFIG['agent'] . '/' . $uuid;
        $curlRequest = CurlService::createCurlRequest(url: $url, authorization: $this->authType);
		$result = CurlService::sendCurlRequest($curlRequest);

		if ($result['success']) {
			$agent = $result['response'] ?? null;
			if ($agent) {
				return [
					'success' => true,
					'agent' => $agent
				];
			}
		}

		return [
			'success' => false,
			'status' => $result['http_code'] ?? HttpStatusCodes::INTERNAL->value
		];
	}*/

	/**
	 * Получение информации об организации
     *
	 * @param string $accessToken Токен доступа
	 * @param string $uuid Идентификатор организации
	 * @return array Результат получения данных об организации, в случае успеха данные, иначе http код ошибки
	 */
	/*public function getOrganizationByUuid(string $accessToken, string $uuid): array
	{
		$this->setBearerToken($accessToken);

		$url = self::BASE_URL . self::URL_CONFIG['organization'] . '/' . $uuid;
        $curlRequest = CurlService::createCurlRequest(url: $url, authorization: $this->authType);
		$result = CurlService::sendCurlRequest($curlRequest);

		if ($result['success']) {
			$organization = $result['response'] ?? null;
			if ($organization) {
				return [
					'success' => true,
					'organization' => $organization
				];
			}
		}

		return [
			'success' => false,
			'status' => $result['http_code'] ?? HttpStatusCodes::INTERNAL->value
		];
	}*/

	/**
	 * Получение информации о валюте
     *
	 * @param string $accessToken Токен доступа
	 * @param string $uuid Идентификатор валюты
	 * @return array Результат получения данных о валюте, в случае успеха данные, иначе http код ошибки
	 */
	/*public function getCurrencyByUuid(string $accessToken, string $uuid): array
	{
		$this->setBearerToken($accessToken);

		$url = self::BASE_URL . self::URL_CONFIG['currency'] . '/' . $uuid;
        $curlRequest = CurlService::createCurlRequest(url: $url, authorization: $this->authType);
		$result = CurlService::sendCurlRequest($curlRequest);

		if ($result['success']) {
			$currency = $result['response'] ?? null;
			if ($currency) {
				return [
					'success' => true,
					'currency' => $currency
				];
			}
		}

		return [
			'success' => false,
			'status' => $result['http_code'] ?? HttpStatusCodes::INTERNAL->value
		];
	}*/
}