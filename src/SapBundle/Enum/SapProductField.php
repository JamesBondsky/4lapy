<?php

namespace FourPaws\SapBundle\Enum;

final class SapProductField
{
    /**
     * Код страны-производителя
     */
    const COUNTRY = 'Country';

    /**
     * Название страны-производителя товара.
     * Единственный выбор из значений справочника «Страна-производитель».
     */
    const COUNTRY_NAME = 'Country_Name';
}
