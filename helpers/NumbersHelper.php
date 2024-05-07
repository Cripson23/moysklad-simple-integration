<?php

namespace helpers;

class NumbersHelper
{
    /**
     * Переводит десятичное число в 16-тиричное представление
     *
     * @param int $decimal Целое 10-тиричное число
     * @return string Результат в 16-ричном представлении
     */
    public static function decimalToHex(int $decimal): string
    {
        $hex = dechex($decimal);
        // Дополняем результат до 6 символов, добавляя нули в начало, если это необходимо
        return strtoupper(str_pad($hex, 6, '0', STR_PAD_LEFT));
    }
}