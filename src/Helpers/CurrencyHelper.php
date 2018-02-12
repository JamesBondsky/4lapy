<?php

namespace FourPaws\Helpers;

use CCurrencyLang;
use Bitrix\Currency\CurrencyManager;

class CurrencyHelper
{
    /**
     * @param $price
     * @param bool $zeroText
     * @param null $currency
     *
     * @return mixed|string
     */
    public static function formatPrice($price, $zeroText = true, $currency = null)
    {
        if ($zeroText && $price == 0) {
            return 'Бесплатно';
        }

        if (!$currency) {
            $currency = CurrencyManager::getBaseCurrency();
        }

        return CCurrencyLang::CurrencyFormat($price, $currency);
    }
}
