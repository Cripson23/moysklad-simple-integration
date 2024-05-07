<?php

namespace general\Cache;

interface CachedInterface
{
    public function get(string $key): array|null;
    public function set(string $key, array $value, int $ttl): void;
}