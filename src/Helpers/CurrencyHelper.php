<?php

namespace FourPaws\Helpers;

use CCurrencyLang;
use Bitrix\Currency\CurrencyManager;

class CurrencyHelper
{
    public static function formatPrice($price, $currency = null)
    {
        if ($price == 0) {
            return 'Бесплатно';
        }

        if (!$currency) {
            $currency = CurrencyManager::getBaseCurrency();
        }

        return CCurrencyLang::CurrencyFormat($price, $currency);
    }
}
