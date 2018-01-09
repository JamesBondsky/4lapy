<?php

namespace FourPaws\SapBundle\Enum;

final class OfferProperty
{
    /**
     * Объединение по фасовке
     * Содержит признак объединения торговых предложений в составной товар.
     * Система должна объединить торговые предложения с одинаковым значением поля в составной товар.
     * Число десятичных знаков после запятой – 2
     */
    const PACKING_COMBINATION = 'PACKING_COMBINATION';

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
}
