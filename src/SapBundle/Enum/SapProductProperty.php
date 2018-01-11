<?php

namespace FourPaws\SapBundle\Enum;

/**
 * Class ProductProperty
 * @package FourPaws\SapBundle\Enum
 * Название свойств в САПе
 */
final class SapProductProperty
{
    /**
     * Объединение по фасовке
     * Содержит признак объединения торговых предложений в составной товар.
     * Система должна объединить торговые предложения с одинаковым значением поля в составной товар.
     * Число десятичных знаков после запятой – 2
     */
    const PACKING_COMBINATION = 'PACKING_COMBINATION';
}
