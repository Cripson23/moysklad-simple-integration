<?php

namespace enums;

enum HttpDefaultErrorMessages: int
{
	case PAGE_NOT_FOUND = 404;
	case INTERNAL = 500;

    public static function fromStatusCode(int $statusCode): ?self
    {
        return match ($statusCode) {
            404 => self::PAGE_NOT_FOUND,
            500 => self::INTERNAL,
            default => null,
        };
    }

    public function text(): string
    {
        return match ($this) {
            self::PAGE_NOT_FOUND => 'К сожалению, страница, которую вы искали, не может быть найдена.',
            self::INTERNAL => 'Сожалеем, произошла внутренняя ошибка приложения. <br>Обратитесь в службу технической поддержки.',
        };
    }
}
