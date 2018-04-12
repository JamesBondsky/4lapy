<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\SapBundle\Enum;

/**
 * Class ProductProperty
 *
 * @package FourPaws\SapBundle\Enum
 *
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
    public const PACKING_COMBINATION = 'PACKING_COMBINATION';

    /**
     * Содержит назначение товара для типа питомца.
     */
    public const FOR_WHO = 'FOR_WHO';

    /**
     * Содержит название торговой марки.
     */
    public const TRADE_NAME = 'TRADE_NAME';

    /**
     * Содержит имя и фамилию категорийного менеджера
     */
    public const MANAGER_OF_CATEGORY = 'MANAGER_OF_CATEGORY';

    /**
     * Содержит материал изготовления.
     * Единственный выбор из справочника «Материал».
     */
    public const MANUFACTURE_MATERIAL = 'MANUFACTURE_MATERIAL';

    /**
     * Содержит информацию о компании-производителе.
     * Единственный выбор из справочника значений.
     */
    public const MAKER = 'MAKER';

    /**
     * Содержит размер питомца.
     * Единственный выбор из значений справочника «Размер питомца».
     */
    public const SIZE_OF_THE_ANIMAL_BIRD = 'SIZE_OF_THE_ANIMAL_BIRD';

    /**
     * Содержит название сезона.
     * Единственный выбор из справочника значений
     */
    public const SEASON_CLOTHES = 'SEASON_CLOTHES';

    /**
     * Содержит назначение товара.
     * Единственный выбор из справочника значений.
     */
    public const PURPOSE = 'PURPOSE';

    /**
     * Содержит принадлежность к категории SAP.
     */
    public const CATEGORY = 'CATEGORY';

    /**
     * Содержит вес упаковки.
     * Единственный выбор из справочника значений.
     * @internal
     */
    public const WEIGHT_CAPACITY_PACKING = 'WEIGHT_CAPACITY_PACKING';

    /**
     * Содержит возраст питомца.
     * Единственный выбор значений из справочника «Возраст питомца»
     */
    public const ANIMALS_AGE = 'ANIMALS_AGE';

    /**
     * Содержит форму выпуска.
     * Единственный выбор из справочника значений.
     */
    public const PRODUCT_FORM = 'PRODUCT_FORM';

    /**
     * Вид живого товара
     */
    public const KIND_OF_ANIMAL = 'KIND_OF_ANIMAL';

    /**
     * Содержит фармакологическую группу ветеринарного препарата.
     * Единственный выбор значений из справочника «Фармакологическая группа».
     */
    public const PHARMA_GROUP = 'PHARMA_GROUP';

    /**
     * Содержит специальные показания корма.
     * Единственный выбор значений из справочника «Специальные показания».
     */
    public const FEED_SPECIFICATION = 'FEED_SPECIFICATION';

    /**
     * Содержит вкус корма.
     * Множественный выбор значений из справочника «Вкус».
     */
    public const FLAVOUR = 'FLAVOUR';

    /**
     * Содержит особенности ингредиентов.
     */
    public const FEATURES_OF_INGREDIENTS = 'FEATURES_OF_INGREDIENTS';

    /**
     * Содержит породу питомца.
     * Единственный выбор из значений справочника «Порода».
     */
    public const BREED_OF_ANIMAL = 'BREED_OF_ANIMAL';

    /**
     * Содержит пол питомца.
     */
    public const GENDER_OF_ANIMAL = 'GENDER_OF_ANIMAL';

    /**
     * Содержит консистенцию корма.
     * Единственный выбор из списка значений:
     * 00000001 – «Сухой»;
     * 00000002 – «Влажный».
     */
    public const CONSISTENCE = 'CONSISTENCE';

    /**
     * Содержит индикатор принадлежности товара к собственной торговой марке.
     * Единственный выбор из значений:
     * 00000001 – «Да»;
     * 00000002 – «Нет».
     */
    public const STM = 'STM';

    /**
     * Содержит индикатор необходимости лицензии на товар.
     * Единственный выбор из списка значений:
     * 00000001 – «Да»;
     * 00000002 – «Нет».
     */
    public const LICENSE = 'LICENSE';

    /**
     * Содержит индикатор необходимости хранения товара в холодильнике.
     * Единственный выбор из списка значений:
     * 000001 – «Да»;
     * 000002 – «Нет».
     */
    public const LOW_TEMPERATURE = 'LOW_TEMPERATURE';

    /**
     * Содержит индикатор принадлежности товара к еде.
     * Единственный выбор из списка значений:
     * 00000001 – «Да»;
     * 00000002 – «Нет».
     */
    public const FOOD = 'FOOD';

    /**
     * Содержит индикатор необходимости хранения товара в холодильнике.
     * Единственный выбор из списка значений:
     * 110000001 – «Да»;
     * 110000002 – «Нет».
     */
    public const TRANSPORT_ONLY_REFRIGERATOR = 'TRANSPORT_ONLY_REFRIGERATOR';

    /**
     * Содержит индикатор ограниченности области доставки.
     * Единственный выбор из списка значений:
     * 990000001 – «Да»;
     * 990000002 – «Нет».
     */
    public const DC_SPECIAL_AREA_STORAGE = 'DC_SPECIAL_AREA_STORAGE';
}
