<?php

namespace app\factories;

use general\Cache\RedisCache;
use general\Config;
use app\services\MoySkladService;

class ServiceFactory
{
    /**
     * Создаёт экземпляр сервиса для работы с Мой склад
     *
     * @return MoySkladService
     */
    public static function createMoySkladService(): MoySkladService
    {
        $config = Config::get('cache');
        $cache = new RedisCache($config);

        return new MoySkladService($cache);
    }
}