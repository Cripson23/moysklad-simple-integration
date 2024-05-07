<?php

namespace app\validators;

use helpers\StringHelper;

class OrderValidator
{
    /**
     * Валидирует входные параметры обновления статуса
     *
     * @param array $stateUpdateData
     * @return array
     */
    public static function validateStateUpdate(array $stateUpdateData): array
    {
        $errors = [];

        if (empty($stateUpdateData['order_uuid']) || empty($stateUpdateData['state_uuid'])) {
            // order
            if (empty($stateUpdateData['order_uuid'])) {
                $errors['order_uuid'][] = 'Необходимо передать ID заказа';
            }
            if (!StringHelper::isValidUuid($stateUpdateData['order_uuid'])) {
                $errors['order_uuid'][] = 'Передан некорректный ID заказа';
            }

            // state
            if (empty($stateUpdateData['state_uuid'])) {
                $errors['password'][] = 'Необходимо передать ID статуса';
            }
            if (!StringHelper::isValidUuid($stateUpdateData['state_uuid'])) {
                $errors['state_uuid'][] = 'Передан некорректный ID статуса';
            }
        }

        return $errors;
    }

    /**
     * Валидирует Uuid заказа для получения
     *
     * @param string $orderUuid
     * @return array
     */
    public static function validateGet(string $orderUuid): array
    {
        $errors = [];

        if (empty($orderUuid)) {
            $errors['order_uuid'][] = 'Необходимо передать ID заказа';
        }

        if (!StringHelper::isValidUuid($orderUuid)) {
            $errors['order_uuid'][] = 'Передан некорректный ID заказа';
        }

        return $errors;
    }
}