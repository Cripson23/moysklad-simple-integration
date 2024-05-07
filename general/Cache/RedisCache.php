<?php

namespace general\Cache;

use Exception;
use Predis\Client;
use general\Logger;

/**
 * Класс работы с Redis
 */
class RedisCache implements CachedInterface
{
    private Client $redis;

    public function __construct(array $redisConfig)
    {
        $this->redis = new Client($redisConfig);

        // Проверка подключения
        try {
            if (strval($this->redis->ping()) !== 'PONG') {
                throw new Exception('Не удалось подключиться к Redis.');
            }
        } catch (\Exception $e) {
            Logger::logError('Ошибка подключения к Redis: ' . $e->getMessage());
        }
    }

    /**
     * Получение значения по ключу
     *
     * @param string $key Ключ
     * @return array|null Значение
     */
    public function get(string $key): array|null
    {
        $value = $this->redis->get($key);
        return $value ? json_decode($value, true) : null;
    }

    /**
     * Задать значение по ключу
     *
     * @param string $key Ключ
     * @param array $value Значение
     * @param int $ttl Время кэширования
     * @return void
     */
    public function set(string $key, array $value, int $ttl = 3600): void
    {
        $this->redis->setex($key, $ttl, json_encode($value));
    }
}