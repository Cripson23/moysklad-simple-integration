<?php

namespace app\services;

use CurlHandle;
use general\Logger;
use app\services\Auth\AuthorizationType\AuthorizationTypeInterface;

/**
 * Сервис для отправки запросов через curl
 */
class CurlService
{
    /**
     * Создает экземпляр curl запроса
     *
     * @param string $url Адрес отправления
     * @param array $data Данные для отправки
     * @param string $method Используемый метод для отправки
     * @param AuthorizationTypeInterface|null $authorization Авторизация
     * @return CurlHandle|false Дескриптор cURL
     */
    public static function createCurlRequest(string $url, array $data = [], string $method = 'GET', AuthorizationTypeInterface $authorization = null): CurlHandle|bool
    {
        $headers = [
            'Accept: application/json;charset=utf-8',
            'Accept-Encoding: gzip',
            'Content-Type: application/json',
        ];

        if ($authorization) {
            $headers[] = $authorization->getAuthorizationHeader();
        }

        $curl = curl_init();

        switch (strtoupper($method)) {
            case 'POST':
            case 'PUT':
            case 'PATCH':
            case 'DELETE':
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
                if (!empty($data)) {
                    curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
                }
                break;
            case 'GET':
                if (!empty($data)) {
                    $url = sprintf("%s?%s", $url, http_build_query($data));
                }
                break;
        }

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_ENCODING, '');

        return $curl;
    }

    /**
     * Отправляет http запрос через curl
     *
     * @param CurlHandle $curl Экземпляр cURL
     * @return array Данные ответа
     */
    public static function sendCurlRequest(CurlHandle $curl): array
    {
        $response = json_decode(curl_exec($curl), true);
        $curlInfo = curl_getinfo($curl);
        $curlErrNo = curl_errno($curl);
        $curlError = curl_error($curl);

        curl_close($curl);

        $httpCode = $curlInfo['http_code'];
        $isSuccess = ($httpCode >= 200 && $httpCode < 300);

        if ($curlErrNo || !$isSuccess) {
            $errorMessage = $curlErrNo ? $curlError : "HTTP error: $httpCode";
            $jsonResponse = json_encode($response, JSON_UNESCAPED_UNICODE);
            Logger::logError("Error in sending curl request: {$errorMessage} | Response: {$jsonResponse} | HttpCode: {$httpCode}");

            return [
                'success' => false,
                'message' => $errorMessage,
                'http_code' => $httpCode,
                'response' => $response
            ];
        }

        return ['success' => true, 'response' => $response];
    }

    /**
     * Выполняет множественные запросы cURL
     *
     * @param array $curlArray Массив дескрипторов cURL
     * @return array Результаты выполнения запросов
     */
    public static function executeMultiCurl(array $curlArray): array
    {
        $multiHandle = curl_multi_init();
        foreach ($curlArray as $key => $curl) {
            curl_multi_add_handle($multiHandle, $curl);
        }

        $running = null;
        do {
            do {
                $status = curl_multi_exec($multiHandle, $running);
            } while ($status === CURLM_CALL_MULTI_PERFORM);

            if ($status !== CURLM_OK) {
                break;
            }

            // Ожидание активности на соединении
            if (curl_multi_select($multiHandle) === -1) {
                usleep(100);
            }
        } while ($running);

        $results = [];
        foreach ($curlArray as $key => $curl) {
            // Получение содержимого ответа
            $content = curl_multi_getcontent($curl);
            $response = json_decode($content, true);
            $info = curl_getinfo($curl);

            curl_multi_remove_handle($multiHandle, $curl);
            curl_close($curl);

            $results[$key] = [
                'success' => ($info['http_code'] >= 200 && $info['http_code'] < 300),
                'response' => $response,
                'http_code' => $info['http_code']
            ];
        }

        curl_multi_close($multiHandle);

        return $results;
    }
}