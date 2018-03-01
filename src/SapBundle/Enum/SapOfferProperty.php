<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\SapBundle\Enum;

/**
 * Class OfferProperty
 * @package FourPaws\SapBundle\Enum
 * Название свойств в САПе
 */
final class SapOfferProperty
{
    /**
     * Цвет товара.
     * Единственный выбор из справочника «Цвет».
     */
    const COLOUR = 'COLOUR';

    /**
     * Объем для клиентов
     * Единственный выбор из справочника «Объем».
     */
    const VOLUME = 'VOLUME';

    /**
     * Размер одежды
     * Единственный выбор значений из справочника «Размер одежды и обуви».
     */
    const CLOTHING_SIZE = 'CLOTHING_SIZE';

    /**
     * Вид упаковки
     * Единственный выбор из значений справочника «Тип упаковки».
     */
    const KIND_OF_PACKING = 'KIND_OF_PACKING';

    /**
     * Год сезона
     * Единственный выбор из справочника «Сезонность».
     */
    const SEASON_YEAR = 'SEASON_YEAR';

    /**
     * Комбинация по вкусу
     */
    const FLAVOUR_COMBINATION = 'FLAVOUR_COMBINATION';
}
