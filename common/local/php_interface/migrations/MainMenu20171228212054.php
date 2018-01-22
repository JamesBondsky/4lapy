<?php

namespace Sprint\Migration;

class MainMenu20171228212054 extends \Adv\Bitrixtools\Migration\SprintMigrationBase {
    protected $description = 'Заполнение пунктов главного меню';

    /** @var int $iMenuIBlockId */
    private $iMenuIBlockId = -1;
    /** @var int $iProductsIBlockId */
    private $iProductsIBlockId = -1;
    /** @var array $arProductsSectionTree */
    private $arProductsSectionTree = array();
    /** @var array $arMenuMap */
    private $arMenuMap = array(
        array(
            'MENU_NAME' => 'Товары по питомцу',
            'MENU_CODE' => 'pet',
            'MENU_SORT' => '100',
            'PRODUCT_SECTION_SEARCH' => '',
            'NESTED' => array(
                //
                // ---
                //
                array(
                    'MENU_NAME' => 'Кошки',
                    'MENU_CODE' => 'cat',
                    'MENU_SORT' => '100',
                    'MENU_HREF' => 'javascript:void(0);',
                    'PRODUCT_SECTION_SEARCH' => 'Кошки',
                    'NESTED' => array(
                        // ---
                        array(
                            'MENU_NAME' => 'Корм',
                            'MENU_CODE' => 'menu_item_s_1',
                            'MENU_SORT' => '100',
                            'PRODUCT_SECTION_SEARCH' => 'Кошки@|@Корм',
                            'NESTED' => array(
                                array(
                                    'MENU_NAME' => 'Сухой',
                                    'MENU_CODE' => 'menu_item_s_2',
                                    'MENU_SORT' => '100',
                                    'PRODUCT_SECTION_SEARCH' => 'Кошки@|@Корм@|@Сухой',
                                ),
                                array(
                                    'MENU_NAME' => 'Консервы',
                                    'MENU_CODE' => 'menu_item_s_3',
                                    'MENU_SORT' => '200',
                                    'PRODUCT_SECTION_SEARCH' => 'Кошки@|@Корм@|@Консервы',
                                ),
                                array(
                                    'MENU_NAME' => 'Диетический',
                                    'MENU_CODE' => 'menu_item_s_4',
                                    'MENU_SORT' => '300',
                                    'PRODUCT_SECTION_SEARCH' => 'Кошки@|@Корм@|@Диетический',
                                ),
                                array(
                                    'MENU_NAME' => 'Кормовая добавка и молоко',
                                    'MENU_CODE' => 'menu_item_s_5',
                                    'MENU_SORT' => '400',
                                    'PRODUCT_SECTION_SEARCH' => 'Кошки@|@Корм@|@Кормовая добавка и молоко',
                                ),
                            )
                        ),
        
                        // ---
                        array(
                            'MENU_NAME' => 'Лакомства',
                            'PRODUCT_SECTION_SEARCH' => 'Кошки@|@Лакомства, витамины, добавки',
                            'MENU_CODE' => 'menu_item_s_6',
                            'MENU_SORT' => '200',
                            'NESTED' => array(
                                array(
                                    'MENU_NAME' => 'Мясные, вяленые, печеные',
                                    'MENU_CODE' => 'menu_item_s_7',
                                    'MENU_SORT' => '100',
// вяленНые
                                    'PRODUCT_SECTION_SEARCH' => 'Кошки@|@Лакомства, витамины, добавки@|@Мясные, вяленные, печеные',
                                ),
                                array(
                                    'MENU_NAME' => 'Для выведения шерсти',
                                    'MENU_CODE' => 'menu_item_s_8',
                                    'MENU_SORT' => '200',
                                    'PRODUCT_SECTION_SEARCH' => 'Кошки@|@Лакомства, витамины, добавки@|@Для выведения шерсти',
                                ),
                                array(
                                    'MENU_NAME' => 'Для чистки зубов',
                                    'MENU_CODE' => 'menu_item_s_9',
                                    'MENU_SORT' => '300',
                                    'PRODUCT_SECTION_SEARCH' => 'Кошки@|@Лакомства, витамины, добавки@|@Для чистки зубов',
                                ),
                                array(
                                    'MENU_NAME' => 'Сушеные натуральные',
                                    'MENU_CODE' => 'menu_item_s_10',
                                    'MENU_SORT' => '400',
                                    'PRODUCT_SECTION_SEARCH' => 'Кошки@|@Лакомства, витамины, добавки@|@Сушеные натуральные',
                                ),
                                array(
                                    'MENU_NAME' => 'Колбаски',
                                    'MENU_CODE' => 'menu_item_s_11',
                                    'MENU_SORT' => '500',
                                    'PRODUCT_SECTION_SEARCH' => 'Кошки@|@Лакомства, витамины, добавки@|@Колбаски',
                                ),
                                array(
                                    'MENU_NAME' => 'Витамины и минералы',
                                    'MENU_CODE' => 'menu_item_s_12',
                                    'MENU_SORT' => '600',
                                    'PRODUCT_SECTION_SEARCH' => 'Кошки@|@Лакомства, витамины, добавки@|@Витамины и минералы',
                                ),
                                array(
                                    'MENU_NAME' => 'Кошачья мята',
                                    'MENU_CODE' => 'menu_item_s_13',
                                    'MENU_SORT' => '700',
                                    'PRODUCT_SECTION_SEARCH' => 'Кошки@|@Лакомства, витамины, добавки@|@Кошачья мята',
                                ),
                            )
                        ),
        
                        // ---
                        array(
                            'MENU_NAME' => 'Наполнители',
                            'MENU_CODE' => 'menu_item_s_14',
                            'MENU_SORT' => '300',
                            'PRODUCT_SECTION_SEARCH' => 'Кошки@|@Наполнители',
                            'NESTED' => array(
                                array(
                                    'MENU_NAME' => 'Древесный',
                                    'MENU_CODE' => 'menu_item_s_15',
                                    'MENU_SORT' => '100',
                                    'PRODUCT_SECTION_SEARCH' => 'Кошки@|@Наполнители@|@Древесный',
                                ),
                                array(
                                    'MENU_NAME' => 'Комкующийся',
                                    'MENU_CODE' => 'menu_item_s_16',
                                    'MENU_SORT' => '200',
                                    'PRODUCT_SECTION_SEARCH' => 'Кошки@|@Наполнители@|@Комкующийся',
                                ),
                                array(
                                    'MENU_NAME' => 'Силикагель',
                                    'MENU_CODE' => 'menu_item_s_17',
                                    'MENU_SORT' => '300',
                                    'PRODUCT_SECTION_SEARCH' => 'Кошки@|@Наполнители@|@Силикагель',
                                ),
                                array(
                                    'MENU_NAME' => 'Впитывающий',
                                    'MENU_CODE' => 'menu_item_s_18',
                                    'MENU_SORT' => '400',
                                    'PRODUCT_SECTION_SEARCH' => 'Кошки@|@Наполнители@|@Впитывающий',
                                ),
                                array(
                                    'MENU_NAME' => 'Ароматизированный',
                                    'MENU_CODE' => 'menu_item_s_19',
                                    'MENU_SORT' => '500',
                                    'PRODUCT_SECTION_SEARCH' => 'Кошки@|@Наполнители@|@Ароматизированный',
                                ),
                                array(
                                    'MENU_NAME' => 'Минеральный',
                                    'MENU_CODE' => 'menu_item_s_20',
                                    'MENU_SORT' => '600',
                                    'PRODUCT_SECTION_SEARCH' => 'Кошки@|@Наполнители@|@Минеральный',
                                ),
                                array(
                                    'MENU_NAME' => 'Бентонит',
                                    'MENU_CODE' => 'menu_item_s_21',
                                    'MENU_SORT' => '700',
                                    'PRODUCT_SECTION_SEARCH' => 'Кошки@|@Наполнители@|@Бентонит',
                                ),
                            )
                        ),
        
                        // ---
                        array(
                            'MENU_NAME' => 'Содержание и уход',
                            'MENU_CODE' => 'menu_item_s_22',
                            'MENU_SORT' => '400',
                            'PRODUCT_SECTION_SEARCH' => '',
                            'NESTED' => array(
                                array(
                                    'MENU_NAME' => 'Когтеточки',
                                    'MENU_CODE' => 'menu_item_s_23',
                                    'MENU_SORT' => '100',
                                    'PRODUCT_SECTION_SEARCH' => 'Кошки@|@Когтеточки',
                                ),
                                array(
                                    'MENU_NAME' => 'Туалеты, лотки, совочки',
                                    'MENU_CODE' => 'menu_item_s_24',
                                    'MENU_SORT' => '200',
                                    'PRODUCT_SECTION_SEARCH' => 'Кошки@|@Туалеты, лотки, совочки',
                                ),
                                array(
                                    'MENU_NAME' => 'Сумки, переноски',
                                    'MENU_CODE' => 'menu_item_s_25',
                                    'MENU_SORT' => '300',
                                    'PRODUCT_SECTION_SEARCH' => 'Кошки@|@Сумки, переноски',
                                ),
                                array(
                                    'MENU_NAME' => 'Лежаки и домики',
                                    'MENU_CODE' => 'menu_item_s_26',
                                    'MENU_SORT' => '400',
                                    'PRODUCT_SECTION_SEARCH' => 'Кошки@|@Лежаки и домики',
                                ),
                                array(
                                    'MENU_NAME' => 'Игрушки',
                                    'MENU_CODE' => 'menu_item_s_27',
                                    'MENU_SORT' => '500',
                                    'PRODUCT_SECTION_SEARCH' => 'Кошки@|@Игрушки',
                                ),
                                array(
                                    'MENU_NAME' => 'Товары для груминга',
                                    'MENU_CODE' => 'menu_item_s_28',
                                    'MENU_SORT' => '600',
                                    'PRODUCT_SECTION_SEARCH' => 'Кошки@|@Товары для груминга',
                                ),
                                array(
                                    'MENU_NAME' => 'Миски, кормушки, поилки',
                                    'MENU_CODE' => 'menu_item_s_29',
                                    'MENU_SORT' => '700',
                                    'PRODUCT_SECTION_SEARCH' => 'Кошки@|@Миски, кормушки, поилки',
                                ),
                                array(
                                    'MENU_NAME' => 'Смотреть все',
                                    'MENU_CODE' => 'menu_item_e_30',
                                    'MENU_SORT' => '5000',
                                    'PRODUCT_SECTION_SEARCH' => 'Кошки',
                                    'MENU_TYPE' => 'E',
                                ),
                            )
                        ),
        
                        // ---
                        array(
                            'MENU_NAME' => 'Ветаптека',
                            'MENU_CODE' => 'menu_item_s_31',
                            'MENU_SORT' => '500',
                            'PRODUCT_SECTION_SEARCH' => '',
                            'NESTED' => array(
                                array(
                                    'MENU_NAME' => 'Защита от блох и клещей',
                                    'MENU_CODE' => 'menu_item_s_32',
                                    'MENU_SORT' => '100',
                                    'PRODUCT_SECTION_SEARCH' => 'Кошки@|@Защита от блох и клещей',
                                ),
                                array(
                                    'MENU_NAME' => 'Противопаразитарные',
                                    'MENU_CODE' => 'menu_item_s_33',
                                    'MENU_SORT' => '200',
                                    'PRODUCT_SECTION_SEARCH' => 'Ветеринарная аптека@|@Кошки@|@Противопаразитарные',
                                ),
                                array(
                                    'MENU_NAME' => 'Витамины и минералы',
                                    'MENU_CODE' => 'menu_item_s_34',
                                    'MENU_SORT' => '300',
                                    'PRODUCT_SECTION_SEARCH' => 'Ветеринарная аптека@|@Кошки@|@Витамины и минералы',
                                ),
                                array(
                                    'MENU_NAME' => 'Вывод шерсти',
                                    'MENU_CODE' => 'menu_item_s_35',
                                    'MENU_SORT' => '400',
                                    'PRODUCT_SECTION_SEARCH' => 'Ветеринарная аптека@|@Кошки@|@Вывод шерсти',
                                ),
                                array(
                                    'MENU_NAME' => 'Капли и лосьоны для ушей',
                                    'MENU_CODE' => 'menu_item_s_36',
                                    'MENU_SORT' => '500',
                                    'PRODUCT_SECTION_SEARCH' => 'Ветеринарная аптека@|@Кошки@|@Капли и лосьоны для ушей',
                                ),
                                array(
                                    'MENU_NAME' => 'Гомеопатия и фитопрепараты',
                                    'MENU_CODE' => 'menu_item_s_37',
                                    'MENU_SORT' => '600',
                                    'PRODUCT_SECTION_SEARCH' => 'Ветеринарная аптека@|@Кошки@|@Гомеопатия и фитопрепараты',
                                ),
                                array(
                                    'MENU_NAME' => 'Биопрепараты',
                                    'MENU_CODE' => 'menu_item_s_38',
                                    'MENU_SORT' => '700',
                                    'PRODUCT_SECTION_SEARCH' => 'Ветеринарная аптека@|@Кошки@|@Биопрепараты',
                                ),
                                array(
                                    'MENU_NAME' => 'Смотреть все',
                                    'MENU_CODE' => 'menu_item_e_39',
                                    'MENU_SORT' => '5000',
                                    'PRODUCT_SECTION_SEARCH' => 'Ветеринарная аптека@|@Кошки',
                                    'MENU_TYPE' => 'E',
                                ),
                            )
                        ),
                    )
                ),
        
                //
                // ---
                //
                array(
                    'MENU_NAME' => 'Собаки',
                    'MENU_CODE' => 'dog',
                    'MENU_SORT' => '200',
                    'MENU_HREF' => 'javascript:void(0);',
                    'PRODUCT_SECTION_SEARCH' => 'Собаки',
                    'NESTED' => array(
                        // ---
                        array(
                            'MENU_NAME' => 'Корм',
                            'MENU_CODE' => 'menu_item_s_40',
                            'MENU_SORT' => '100',
                            'PRODUCT_SECTION_SEARCH' => 'Собаки@|@Корм',
                            'NESTED' => array(
                                array(
                                    'MENU_NAME' => 'Сухой',
                                    'MENU_CODE' => 'menu_item_s_41',
                                    'MENU_SORT' => '100',
                                    'PRODUCT_SECTION_SEARCH' => 'Собаки@|@Корм@|@Сухой',
                                ),
                                array(
                                    'MENU_NAME' => 'Консервы',
                                    'MENU_CODE' => 'menu_item_s_42',
                                    'MENU_SORT' => '200',
                                    'PRODUCT_SECTION_SEARCH' => 'Собаки@|@Корм@|@Консервы',
                                ),
                                array(
                                    'MENU_NAME' => 'Диетический',
                                    'MENU_CODE' => 'menu_item_s_43',
                                    'MENU_SORT' => '300',
                                    'PRODUCT_SECTION_SEARCH' => 'Собаки@|@Корм@|@Диетический',
                                ),
                                array(
                                    'MENU_NAME' => 'Кормовая добавка и молоко',
                                    'MENU_CODE' => 'menu_item_s_44',
                                    'MENU_SORT' => '400',
                                    'PRODUCT_SECTION_SEARCH' => 'Собаки@|@Корм@|@Кормовая добавка и молоко',
                                ),
                            )
                        ),
        
                        // ---
                        array(
                            'MENU_NAME' => 'Лакомства',
                            'MENU_CODE' => 'menu_item_s_45',
                            'MENU_SORT' => '200',
                            'PRODUCT_SECTION_SEARCH' => 'Собаки@|@Лакомства и витамины',
                            'NESTED' => array(
                                array(
                                    'MENU_NAME' => 'Сушеные натуральные',
                                    'MENU_CODE' => 'menu_item_s_46',
                                    'MENU_SORT' => '100',
                                    'PRODUCT_SECTION_SEARCH' => 'Собаки@|@Лакомства и витамины@|@Сушеные натуральные',
                                ),
                                array(
                                    'MENU_NAME' => 'Мясные, вяленые, печеные',
                                    'MENU_CODE' => 'menu_item_s_47',
                                    'MENU_SORT' => '200',
// !!! вяленНые
                                    'PRODUCT_SECTION_SEARCH' => 'Собаки@|@Лакомства и витамины@|@Мясные, вяленные, печеные',
                                ),
                                array(
                                    'MENU_NAME' => 'Для чистки зубов',
                                    'MENU_CODE' => 'menu_item_s_48',
                                    'MENU_SORT' => '300',
                                    'PRODUCT_SECTION_SEARCH' => 'Собаки@|@Лакомства и витамины@|@Для чистки зубов',
                                ),
                                array(
                                    'MENU_NAME' => 'Колбаски',
                                    'MENU_CODE' => 'menu_item_s_49',
                                    'MENU_SORT' => '400',
                                    'PRODUCT_SECTION_SEARCH' => 'Собаки@|@Лакомства и витамины@|@Колбаски',
                                ),
                                array(
                                    'MENU_NAME' => 'Витамины и минералы',
                                    'MENU_CODE' => 'menu_item_s_50',
                                    'MENU_SORT' => '500',
                                    'PRODUCT_SECTION_SEARCH' => 'Собаки@|@Лакомства и витамины@|@Витамины и минералы',
                                ),
                            )
                        ),
        
                        // ---
                        array(
                            'MENU_NAME' => 'Содержание и уход',
                            'MENU_CODE' => 'menu_item_s_51',
                            'MENU_SORT' => '300',
                            'PRODUCT_SECTION_SEARCH' => '',
                            'NESTED' => array(
                                array(
                                    'MENU_NAME' => 'Одежда и обувь',
                                    'MENU_CODE' => 'menu_item_s_52',
                                    'MENU_SORT' => '100',
                                    'PRODUCT_SECTION_SEARCH' => 'Собаки@|@Одежда и обувь',
                                ),
                                array(
                                    'MENU_NAME' => 'Лежаки и домики',
                                    'MENU_CODE' => 'menu_item_s_53',
                                    'MENU_SORT' => '200',
                                    'PRODUCT_SECTION_SEARCH' => 'Собаки@|@Лежаки и домики',
                                ),
                                array(
                                    'MENU_NAME' => 'Намордники, ошейники, поводки',
                                    'MENU_CODE' => 'menu_item_s_54',
                                    'MENU_SORT' => '300',
                                    'PRODUCT_SECTION_SEARCH' => 'Собаки@|@Намордники, ошейники, поводки',
                                ),
                                array(
                                    'MENU_NAME' => 'Сумки, переноски',
                                    'MENU_CODE' => 'menu_item_s_55',
                                    'MENU_SORT' => '400',
                                    'PRODUCT_SECTION_SEARCH' => 'Собаки@|@Сумки, переноски',
                                ),
                                array(
                                    'MENU_NAME' => 'Игрушки',
                                    'MENU_CODE' => 'menu_item_s_56',
                                    'MENU_SORT' => '500',
                                    'PRODUCT_SECTION_SEARCH' => 'Собаки@|@Игрушки',
                                ),
                                array(
                                    'MENU_NAME' => 'Миски, кормушки, поилки',
                                    'MENU_CODE' => 'menu_item_s_57',
                                    'MENU_SORT' => '600',
                                    'PRODUCT_SECTION_SEARCH' => 'Собаки@|@Миски, кормушки, поилки',
                                ),
                                array(
                                    'MENU_NAME' => 'Товары для груминга',
                                    'MENU_CODE' => 'menu_item_s_58',
                                    'MENU_SORT' => '700',
                                    'PRODUCT_SECTION_SEARCH' => 'Собаки@|@Товары для груминга',
                                ),
                                array(
                                    'MENU_NAME' => 'Смотреть все',
                                    'MENU_CODE' => 'menu_item_e_59',
                                    'MENU_SORT' => '5000',
                                    'PRODUCT_SECTION_SEARCH' => 'Собаки',
                                    'MENU_TYPE' => 'E',
                                ),
                            )
                        ),
        
                        // ---
                        array(
                            'MENU_NAME' => 'Ветаптека',
                            'MENU_CODE' => 'menu_item_s_60',
                            'MENU_SORT' => '400',
                            'PRODUCT_SECTION_SEARCH' => '',
                            'NESTED' => array(
                                array(
                                    'MENU_NAME' => 'Защита от блох и клещей',
                                    'MENU_CODE' => 'menu_item_s_61',
                                    'MENU_SORT' => '100',
                                    'PRODUCT_SECTION_SEARCH' => 'Собаки@|@Защита от блох и клещей',
                                ),
                                array(
                                    'MENU_NAME' => 'Противопаразитарные',
                                    'MENU_CODE' => 'menu_item_s_62',
                                    'MENU_SORT' => '200',
                                    'PRODUCT_SECTION_SEARCH' => 'Ветеринарная аптека@|@Собаки@|@Противопаразитарные',
                                ),
                                array(
                                    'MENU_NAME' => 'Витамины и минералы',
                                    'MENU_CODE' => 'menu_item_s_63',
                                    'MENU_SORT' => '300',
                                    'PRODUCT_SECTION_SEARCH' => 'Ветеринарная аптека@|@Собаки@|@Витамины и минералы',
                                ),
                                array(
                                    'MENU_NAME' => 'Дерматология',
                                    'MENU_CODE' => 'menu_item_s_64',
                                    'MENU_SORT' => '400',
                                    'PRODUCT_SECTION_SEARCH' => 'Ветеринарная аптека@|@Собаки@|@Дерматология',
                                ),
                                array(
                                    'MENU_NAME' => 'Кормовые добавки',
                                    'MENU_CODE' => 'menu_item_s_65',
                                    'MENU_SORT' => '500',
                                    'PRODUCT_SECTION_SEARCH' => 'Ветеринарная аптека@|@Собаки@|@Кормовые добавки',
                                ),
                                array(
                                    'MENU_NAME' => 'Биопрепараты',
                                    'MENU_CODE' => 'menu_item_s_66',
                                    'MENU_SORT' => '600',
                                    'PRODUCT_SECTION_SEARCH' => 'Ветеринарная аптека@|@Собаки@|@Биопрепараты',
                                ),
                                array(
                                    'MENU_NAME' => 'Гомеопатия и фитопрепараты',
                                    'MENU_CODE' => 'menu_item_s_67',
                                    'MENU_SORT' => '700',
                                    'PRODUCT_SECTION_SEARCH' => 'Ветеринарная аптека@|@Собаки@|@Гомеопатия и фитопрепараты',
                                ),
                                array(
                                    'MENU_NAME' => 'Смотреть все',
                                    'MENU_CODE' => 'menu_item_e_68',
                                    'MENU_SORT' => '5000',
                                    'PRODUCT_SECTION_SEARCH' => 'Ветеринарная аптека@|@Собаки',
                                    'MENU_TYPE' => 'E',
                                ),
                            )
                        ),
                    )
                ),
        
                //
                // ---
                //
                array(
                    'MENU_NAME' => 'Грызуны и хорьки',
                    'MENU_CODE' => 'rodent',
                    'MENU_SORT' => '300',
                    'MENU_HREF' => 'javascript:void(0);',
                    'PRODUCT_SECTION_SEARCH' => 'Грызуны и хорьки',
                    'NESTED' => array(
                        // ---
                        array(
                            'MENU_NAME' => 'Корм и лакомства',
                            'MENU_CODE' => 'menu_item_s_69',
                            'MENU_SORT' => '100',
                            'PRODUCT_SECTION_SEARCH' => 'Грызуны и хорьки',
                            'NESTED' => array(
                                array(
                                    'MENU_NAME' => 'Повседневный',
                                    'MENU_CODE' => 'menu_item_s_70',
                                    'MENU_SORT' => '100',
                                    'PRODUCT_SECTION_SEARCH' => 'Грызуны и хорьки@|@Корм@|@Повседневный',
                                ),
                                array(
                                    'MENU_NAME' => 'Кормовая добавка',
                                    'MENU_CODE' => 'menu_item_s_71',
                                    'MENU_SORT' => '200',
                                    'PRODUCT_SECTION_SEARCH' => 'Грызуны и хорьки@|@Корм@|@Кормовая добавка',
                                ),
                                array(
                                    'MENU_NAME' => 'Сено',
                                    'MENU_CODE' => 'menu_item_s_72',
                                    'MENU_SORT' => '300',
                                    'PRODUCT_SECTION_SEARCH' => 'Грызуны и хорьки@|@Корм@|@Сено',
                                ),
                                array(
                                    'MENU_NAME' => 'Лакомства',
                                    'MENU_CODE' => 'menu_item_s_73',
                                    'MENU_SORT' => '400',
                                    'PRODUCT_SECTION_SEARCH' => 'Грызуны и хорьки@|@Лакомства и витамины@|@Лакомства',
                                ),
                                array(
                                    'MENU_NAME' => 'Витамины',
                                    'MENU_CODE' => 'menu_item_s_74',
                                    'MENU_SORT' => '500',
                                    'PRODUCT_SECTION_SEARCH' => 'Грызуны и хорьки@|@Лакомства и витамины@|@Витамины',
                                ),
                            )
                        ),
        
                        // ---
                        array(
                            'MENU_NAME' => 'Содержание и уход',
                            'MENU_CODE' => 'menu_item_s_75',
                            'MENU_SORT' => '200',
                            'PRODUCT_SECTION_SEARCH' => '',
                            'NESTED' => array(
                                array(
                                    'MENU_NAME' => 'Наполнители',
                                    'MENU_CODE' => 'menu_item_s_76',
                                    'MENU_SORT' => '100',
                                    'PRODUCT_SECTION_SEARCH' => 'Грызуны и хорьки@|@Наполнители',
                                ),
                                array(
                                    'MENU_NAME' => 'Туалеты и лотки',
                                    'MENU_CODE' => 'menu_item_s_77',
                                    'MENU_SORT' => '200',
                                    'PRODUCT_SECTION_SEARCH' => 'Грызуны и хорьки@|@Туалеты и лотки',
                                ),
                                array(
                                    'MENU_NAME' => 'Домики, лежаки, гнезда',
                                    'MENU_CODE' => 'menu_item_s_78',
                                    'MENU_SORT' => '300',
                                    'PRODUCT_SECTION_SEARCH' => 'Грызуны и хорьки@|@Домики, лежаки, гнезда',
                                ),
                                array(
                                    'MENU_NAME' => 'Игрушки',
                                    'MENU_CODE' => 'menu_item_s_79',
                                    'MENU_SORT' => '400',
                                    'PRODUCT_SECTION_SEARCH' => 'Грызуны и хорьки@|@Игрушки',
                                ),
                                array(
                                    'MENU_NAME' => 'Клетки',
                                    'MENU_CODE' => 'menu_item_s_80',
                                    'MENU_SORT' => '500',
                                    'PRODUCT_SECTION_SEARCH' => 'Грызуны и хорьки@|@Клетки',
                                ),
                                array(
                                    'MENU_NAME' => 'Миски, кормушки, поилки',
                                    'MENU_CODE' => 'menu_item_s_81',
                                    'MENU_SORT' => '600',
                                    'PRODUCT_SECTION_SEARCH' => 'Грызуны и хорьки@|@Миски, кормушки, поилки',
                                ),
                                array(
                                    'MENU_NAME' => 'Сумки, переноски',
                                    'MENU_CODE' => 'menu_item_s_82',
                                    'MENU_SORT' => '700',
                                    'PRODUCT_SECTION_SEARCH' => 'Грызуны и хорьки@|@Сумки, переноски',
                                ),
                                array(
                                    'MENU_NAME' => 'Гигиена и косметика',
                                    'MENU_CODE' => 'menu_item_s_83',
                                    'MENU_SORT' => '800',
                                    'PRODUCT_SECTION_SEARCH' => 'Грызуны и хорьки@|@Гигиена и косметика',
                                ),
                                array(
                                    'MENU_NAME' => 'Защита от блох и клещей',
                                    'MENU_CODE' => 'menu_item_s_84',
                                    'MENU_SORT' => '900',
                                    'PRODUCT_SECTION_SEARCH' => 'Грызуны и хорьки@|@Защита от блох и клещей',
                                ),
                                array(
                                    'MENU_NAME' => 'Смотреть все',
                                    'MENU_CODE' => 'menu_item_e_85',
                                    'MENU_SORT' => '5000',
                                    'PRODUCT_SECTION_SEARCH' => 'Грызуны и хорьки',
                                    'MENU_TYPE' => 'E',
                                ),
                            )
                        ),
        
                        // ---
                        array(
                            'MENU_NAME' => 'Ветаптека',
                            'MENU_CODE' => 'menu_item_s_86',
                            'MENU_SORT' => '300',
                            'PRODUCT_SECTION_SEARCH' => '',
                            'NESTED' => array(
                                array(
                                    'MENU_NAME' => 'Витамины и минералы',
                                    'MENU_CODE' => 'menu_item_s_87',
                                    'MENU_SORT' => '100',
                                    'PRODUCT_SECTION_SEARCH' => 'Ветеринарная аптека@|@Грызуны и хорьки@|@Витамины и минералы',
                                ),
                                array(
                                    'MENU_NAME' => 'Вакцины',
                                    'MENU_CODE' => 'menu_item_s_88',
                                    'MENU_SORT' => '200',
                                    'PRODUCT_SECTION_SEARCH' => 'Ветеринарная аптека@|@Грызуны и хорьки@|@Вакцины',
                                ),
                                array(
                                    'MENU_NAME' => 'Противопаразитарные',
                                    'MENU_CODE' => 'menu_item_s_89',
                                    'MENU_SORT' => '300',
                                    'PRODUCT_SECTION_SEARCH' => 'Ветеринарная аптека@|@Грызуны и хорьки@|@Противопаразитарные',
                                ),
                            )
                        ),
                    )
                ),
        
                //
                // ---
                //
                array(
                    'MENU_NAME' => 'Птицы',
                    'MENU_CODE' => 'bird',
                    'MENU_SORT' => '400',
                    'MENU_HREF' => 'javascript:void(0);',
                    'PRODUCT_SECTION_SEARCH' => 'Птицы',
                    'NESTED' => array(
                        // ---
                        array(
                            'MENU_NAME' => 'Корм и лакомства',
                            'MENU_CODE' => 'menu_item_s_90',
                            'MENU_SORT' => '100',
                            'PRODUCT_SECTION_SEARCH' => 'Птицы@|@Корм',
                            'NESTED' => array(
                                array(
                                    'MENU_NAME' => 'Основной',
                                    'MENU_CODE' => 'menu_item_s_91',
                                    'MENU_SORT' => '100',
                                    'PRODUCT_SECTION_SEARCH' => 'Птицы@|@Корм@|@Основной',
                                ),
                                array(
                                    'MENU_NAME' => 'Дополнительный',
                                    'MENU_CODE' => 'menu_item_s_92',
                                    'MENU_SORT' => '200',
                                    'PRODUCT_SECTION_SEARCH' => 'Птицы@|@Корм@|@Дополнительный',
                                ),
                                array(
                                    'MENU_NAME' => 'Подкормка витаминная',
                                    'MENU_CODE' => 'menu_item_s_93',
                                    'MENU_SORT' => '300',
                                    'PRODUCT_SECTION_SEARCH' => 'Птицы@|@Корм@|@Подкормка витаминная',
                                ),
                                array(
                                    'MENU_NAME' => 'Подкормка минеральная',
                                    'MENU_CODE' => 'menu_item_s_94',
                                    'MENU_SORT' => '400',
                                    'PRODUCT_SECTION_SEARCH' => 'Птицы@|@Корм@|@Подкормка минеральная',
                                ),
                                array(
                                    'MENU_NAME' => 'Песок',
                                    'MENU_CODE' => 'menu_item_s_95',
                                    'MENU_SORT' => '500',
                                    'PRODUCT_SECTION_SEARCH' => 'Птицы@|@Корм@|@Песок',
                                ),
                            )
                        ),
        
                        // ---
                        array(
                            'MENU_NAME' => 'Содержание и уход',
                            'MENU_CODE' => 'menu_item_s_96',
                            'MENU_SORT' => '200',
                            'PRODUCT_SECTION_SEARCH' => '',
                            'NESTED' => array(
                                array(
                                    'MENU_NAME' => 'Игрушки',
                                    'MENU_CODE' => 'menu_item_s_97',
                                    'MENU_SORT' => '100',
                                    'PRODUCT_SECTION_SEARCH' => 'Птицы@|@Игрушки',
                                ),
                                array(
                                    'MENU_NAME' => 'Клетки',
                                    'MENU_CODE' => 'menu_item_s_98',
                                    'MENU_SORT' => '200',
                                    'PRODUCT_SECTION_SEARCH' => 'Птицы@|@Клетки',
                                ),
                                array(
                                    'MENU_NAME' => 'Наполнители',
                                    'MENU_CODE' => 'menu_item_s_99',
                                    'MENU_SORT' => '300',
                                    'PRODUCT_SECTION_SEARCH' => 'Птицы@|@Наполнители',
                                ),
                                array(
                                    'MENU_NAME' => 'Миски, кормушки, поилки',
                                    'MENU_CODE' => 'menu_item_s_100',
                                    'MENU_SORT' => '400',
                                    'PRODUCT_SECTION_SEARCH' => 'Птицы@|@Миски, кормушки, поилки',
                                ),
                                array(
                                    'MENU_NAME' => 'Сумки, переноски',
                                    'MENU_CODE' => 'menu_item_s_101',
                                    'MENU_SORT' => '500',
                                    'PRODUCT_SECTION_SEARCH' => 'Птицы@|@Сумки, переноски',
                                ),
                                array(
                                    'MENU_NAME' => 'Защита от блох и клещей',
                                    'MENU_CODE' => 'menu_item_s_102',
                                    'MENU_SORT' => '600',
                                    'PRODUCT_SECTION_SEARCH' => 'Птицы@|@Защита от блох и клещей',
                                ),
                            )
                        ),
        
                        // ---
                        array(
                            'MENU_NAME' => 'Ветаптека',
                            'MENU_CODE' => 'menu_item_s_103',
                            'MENU_SORT' => '300',
                            'PRODUCT_SECTION_SEARCH' => '',
                            'NESTED' => array(
                                array(
                                    'MENU_NAME' => 'Витамины и минералы',
                                    'MENU_CODE' => 'menu_item_s_104',
                                    'MENU_SORT' => '100',
                                    'PRODUCT_SECTION_SEARCH' => 'Ветеринарная аптека@|@Попугаи@|@Витамины и минералы',
                                ),
                                array(
                                    'MENU_NAME' => 'Вакцины',
                                    'MENU_CODE' => 'menu_item_s_105',
                                    'MENU_SORT' => '200',
// !!! нет секции
                                    'PRODUCT_SECTION_SEARCH' => 'Ветеринарная аптека@|@Попугаи@|@Вакцины',
                                ),
                                array(
                                    'MENU_NAME' => 'Противопаразитарные',
                                    'MENU_CODE' => 'menu_item_s_106',
                                    'MENU_SORT' => '300',
// !!! нет секции
                                    'PRODUCT_SECTION_SEARCH' => 'Ветеринарная аптека@|@Попугаи@|@Противопаразитарные',
                                ),
                            )
                        ),
                    )
                ),
        
                //
                // ---
                //
                array(
                    'MENU_NAME' => 'Рыбы',
                    'MENU_CODE' => 'fish',
                    'MENU_SORT' => '500',
                    'MENU_HREF' => 'javascript:void(0);',
                    'PRODUCT_SECTION_SEARCH' => 'Рыбы',
                    'NESTED' => array(
                        // ---
                        array(
                            'MENU_NAME' => 'Корм и лакомства',
                            'MENU_CODE' => 'menu_item_s_107',
                            'MENU_SORT' => '100',
                            'PRODUCT_SECTION_SEARCH' => 'Рыбы@|@Корм и подкормка',
                            'NESTED' => array(
                                array(
                                    'MENU_NAME' => 'Подкормка, витамины, добавки',
                                    'MENU_CODE' => 'menu_item_s_108',
                                    'MENU_SORT' => '100',
                                    'PRODUCT_SECTION_SEARCH' => 'Рыбы@|@Корм и подкормка@|@Подкормка, витамины, добавки',
                                ),
                                array(
                                    'MENU_NAME' => 'Замороженные',
                                    'MENU_CODE' => 'menu_item_s_109',
                                    'MENU_SORT' => '200',
                                    'PRODUCT_SECTION_SEARCH' => 'Рыбы@|@Корм и подкормка@|@Замороженные',
                                ),
                                array(
                                    'MENU_NAME' => 'Повседневные',
                                    'MENU_CODE' => 'menu_item_s_110',
                                    'MENU_SORT' => '300',
                                    'PRODUCT_SECTION_SEARCH' => 'Рыбы@|@Корм и подкормка@|@Повседневные',
                                ),
                                array(
                                    'MENU_NAME' => 'Для пудовых рыб',
                                    'MENU_CODE' => 'menu_item_s_111',
                                    'MENU_SORT' => '400',
                                    'PRODUCT_SECTION_SEARCH' => 'Рыбы@|@Корм и подкормка@|@Для пудовых рыб',
                                ),
                            )
                        ),
        
                        // ---
                        array(
                            'MENU_NAME' => 'Содержание и уход',
                            'MENU_CODE' => 'menu_item_s_112',
                            'MENU_SORT' => '200',
                            'PRODUCT_SECTION_SEARCH' => '',
                            'NESTED' => array(
                                array(
                                    'MENU_NAME' => 'Аквариумы',
                                    'MENU_CODE' => 'menu_item_s_113',
                                    'MENU_SORT' => '100',
                                    'PRODUCT_SECTION_SEARCH' => 'Рыбы@|@Аквариумы',
                                ),
                                array(
                                    'MENU_NAME' => 'Декор',
                                    'MENU_CODE' => 'menu_item_s_114',
                                    'MENU_SORT' => '200',
                                    'PRODUCT_SECTION_SEARCH' => 'Рыбы@|@Декор',
                                ),
                                array(
                                    'MENU_NAME' => 'Оборудование',
                                    'MENU_CODE' => 'menu_item_s_115',
                                    'MENU_SORT' => '300',
                                    'PRODUCT_SECTION_SEARCH' => 'Рыбы@|@Оборудование',
                                ),
                                array(
                                    'MENU_NAME' => 'Химия и лекарства',
                                    'MENU_CODE' => 'menu_item_s_116',
                                    'MENU_SORT' => '400',
                                    'PRODUCT_SECTION_SEARCH' => 'Рыбы@|@Химия и лекарства',
                                ),
                            )
                        ),
                    )
                ),
        
                //
                // ---
                //
                array(
                    'MENU_NAME' => 'Рептилии',
                    'MENU_CODE' => 'reptile',
                    'MENU_SORT' => '600',
                    'MENU_HREF' => 'javascript:void(0);',
                    'PRODUCT_SECTION_SEARCH' => 'Рептилии',
                    'NESTED' => array(
                        // ---
                        array(
                            'MENU_NAME' => 'Корм и лакомства',
                            'MENU_CODE' => 'menu_item_s_117',
                            'MENU_SORT' => '100',
                            'PRODUCT_SECTION_SEARCH' => 'Рептилии@|@Корм и подкормка',
                            'NESTED' => array(
                                array(
                                    'MENU_NAME' => 'Подкормка, витамины, добавки',
                                    'MENU_CODE' => 'menu_item_s_118',
                                    'MENU_SORT' => '100',
                                    'PRODUCT_SECTION_SEARCH' => 'Рептилии@|@Корм и подкормка@|@Подкормка, витамины, добавки',
                                ),
                                array(
                                    'MENU_NAME' => 'Корма замороженные',
                                    'MENU_CODE' => 'menu_item_s_119',
                                    'MENU_SORT' => '200',
                                    'PRODUCT_SECTION_SEARCH' => 'Рептилии@|@Корм и подкормка@|@Корма замороженные',
                                ),
                                array(
                                    'MENU_NAME' => 'Корма повседневные',
                                    'MENU_CODE' => 'menu_item_s_120',
                                    'MENU_SORT' => '300',
                                    'PRODUCT_SECTION_SEARCH' => 'Рептилии@|@Корм и подкормка@|@Корма повседневные',
                                ),
                            )
                        ),
        
                        // ---
                        array(
                            'MENU_NAME' => 'Содержание и уход',
                            'MENU_CODE' => 'menu_item_s_121',
                            'MENU_SORT' => '200',
                            'PRODUCT_SECTION_SEARCH' => '',
                            'NESTED' => array(
                                array(
                                    'MENU_NAME' => 'Террариумы и подставки',
                                    'MENU_CODE' => 'menu_item_s_122',
                                    'MENU_SORT' => '100',
                                    'PRODUCT_SECTION_SEARCH' => 'Рептилии@|@Террариумы и подставки',
                                ),
                                array(
                                    'MENU_NAME' => 'Декор',
                                    'MENU_CODE' => 'menu_item_s_123',
                                    'MENU_SORT' => '200',
                                    'PRODUCT_SECTION_SEARCH' => 'Рептилии@|@Декор',
                                ),
                                array(
                                    'MENU_NAME' => 'Оборудование',
                                    'MENU_CODE' => 'menu_item_s_124',
                                    'MENU_SORT' => '300',
                                    'PRODUCT_SECTION_SEARCH' => 'Рептилии@|@Оборудование',
                                ),
                                array(
                                    'MENU_NAME' => 'Средства для подготовки воды',
                                    'MENU_CODE' => 'menu_item_s_125',
                                    'MENU_SORT' => '400',
                                    'PRODUCT_SECTION_SEARCH' => 'Рептилии@|@Средства для подготовки воды',
                                ),
                            )
                        ),
                    )
                ),
        
                //
                // ---
                //
                array(
                    'MENU_NAME' => 'Котята',
                    'MENU_CODE' => 'kitten',
                    'MENU_SORT' => '700',
                    'PRODUCT_SECTION_SEARCH' => 'Кошки@|@Завели котенка',
                ),
        
                //
                // ---
                //
                array(
                    'MENU_NAME' => 'Щенки',
                    'MENU_CODE' => 'puppy',
                    'MENU_SORT' => '800',
                    'PRODUCT_SECTION_SEARCH' => 'Собаки@|@Завели щенка',
                ),
            ),
        ),
    );

    public function up() {
        $obHelper = new \Sprint\Migration\HelperManager();
        if (!\Bitrix\Main\Loader::includeModule('iblock')) {
            return false;
        }

        $this->processMenuMapRecursive($this->arMenuMap, 0);

        $this->getMenuIBlockId();
    }

    public function down() {
        //
    }

    /**
     * @return int
     */
    public function getMenuIBlockId() {
        if ($this->iMenuIBlockId < 0) {
            $this->iMenuIBlockId = $this->getIBlockIdByCode('main_menu', 'menu');
        }

        return $this->iMenuIBlockId;
    }

    /**
     * @return int
     */
    public function getProductsIBlockId() {
        if ($this->iProductsIBlockId < 0) {
            $this->iProductsIBlockId = $this->getIBlockIdByCode('products', 'catalog');
        }

        return $this->iProductsIBlockId;
    }

    /**
     * @param string $sIBlockCode
     * @param string $sIBlockType
     * @return int
     */
    protected function getIBlockIdByCode($sIBlockCode, $sIBlockType = '') {
        $iReturn = 0;

        $arFilter = array(
            'CHECK_PERMISSIONS' => 'N',
            'CODE' => $sIBlockCode,
        );
        if (strlen($sIBlockType)) {
            $arFilter['TYPE'] = $sIBlockType;
        }
        $arIBlock = \CIBlock::GetList(array('ID' => 'ASC'), $arFilter)->fetch();
        $iReturn = $arIBlock ? $arIBlock['ID'] : 0;

        return $iReturn;
    }

    protected function processMenuMapRecursive($arMenuMap, $iMenuParentSectionId) {
        foreach ($arMenuMap as $arMenuItem) {
            if (!strlen($arMenuItem['MENU_NAME'])) {
                continue;
            }

            $arProductsSect = array();
            if($arMenuItem['PRODUCT_SECTION_SEARCH']) {
                $arProductsSect = $this->findProductsSectionByName($arMenuItem['PRODUCT_SECTION_SEARCH']);
                if (!$arProductsSect) {
                    continue;
                }
            }

            $arMenuTmpFields = array(
                'NAME' => $arMenuItem['MENU_NAME'],
                'IBLOCK_SECTION_ID' => $iMenuParentSectionId,
                'ACTIVE' => 'Y',
            );
            if (isset($arMenuItem['MENU_CODE']) && strlen($arMenuItem['MENU_CODE'])) {
                $arMenuTmpFields['CODE'] = $arMenuItem['MENU_CODE'];
            }
            if (isset($arMenuItem['MENU_SORT']) && intval($arMenuItem['MENU_SORT'])) {
                $arMenuTmpFields['SORT'] = intval($arMenuItem['MENU_SORT']);
            }
            if (isset($arMenuItem['MENU_TYPE']) && $arMenuItem['MENU_TYPE'] === 'E') {
                $arMenuElem = $this->findMenuElementByName($arMenuItem['MENU_NAME'], $iMenuParentSectionId);
                $arMenuTmpFields['PROPERTY_VALUES']['SECTION_HREF'] = $arProductsSect ? $arProductsSect['ID'] : false;
                if (isset($arMenuItem['MENU_HREF'])) {
                    $arMenuTmpFields['PROPERTY_VALUES']['HREF'] = trim($arMenuItem['MENU_HREF']);
                }

                if (!$arMenuElem) {
                    $this->addMenuElement($arMenuTmpFields);
                } else {
                    $this->updateMenuElement($arMenuElem['ID'], $arMenuTmpFields);
                }
            } else {
                $arMenuSect = $this->findMenuSectionByName($arMenuItem['MENU_NAME'], $iMenuParentSectionId);
                $arMenuTmpFields['UF_SECTION_HREF'] = $arProductsSect ? $arProductsSect['ID'] : false;
                if (isset($arMenuItem['MENU_HREF'])) {
                    $arMenuTmpFields['UF_HREF'] = trim($arMenuItem['MENU_HREF']);
                }
                if (!$arMenuSect) {
                    $iMenuSectionId = $this->addMenuSection($arMenuTmpFields);
                } else {
                    $iMenuSectionId = $arMenuSect['ID'];
                    $this->updateMenuSection($arMenuSect['ID'], $arMenuTmpFields);
                }
                if ($iMenuSectionId && $arMenuItem['NESTED']) {
                    $this->processMenuMapRecursive($arMenuItem['NESTED'], $iMenuSectionId);
                }
            }
        }
    }

    protected function addMenuElement($arFields) {
        $iReturnId = 0;
        $arFields['IBLOCK_ID'] = $this->getMenuIBlockId();
        $obElement = new \CIBlockElement();
        $iReturnId = $obElement->add($arFields);
        return $iReturnId;
    }

    protected function updateMenuElement($iElementId, $arFields) {
        $bResult = false;
        $obElement = new \CIBlockElement();
        $arProps = array();
        if (isset($arFields['PROPERTY_VALUES'])) {
            $arProps = $arFields['PROPERTY_VALUES'];
            unset($arFields['PROPERTY_VALUES']);
        }
        $bResult = $obElement->update($iElementId, $arFields);
        if ($bResult) {
            $obElement->SetPropertyValuesEx($iElementId, $this->getMenuIBlockId(), $arProps);
        }
        return $bResult;
    }

    protected function addMenuSection($arFields) {
        $iReturnId = 0;
        $arFields['IBLOCK_ID'] = $this->getMenuIBlockId();
        $obSection = new \CIBlockSection();
        $iReturnId = $obSection->add($arFields);
        return $iReturnId;
    }

    protected function updateMenuSection($iSectionId, $arFields) {
        $bResult = false;
        $obSection = new \CIBlockSection();
        $bResult = $obSection->update($iSectionId, $arFields);
        return $bResult;
    }

    protected function findProductsSectionByName($sSearchStr) {
        $arReturn = array();
        if (strlen(trim($sSearchStr))) {
            $arTree = $this->getProductsSectionTree();
            $arSearchChain = explode('@|@', $sSearchStr);
            $iParentSectionId = 0;
            foreach ($arSearchChain as $sSearchName) {
                $sSearchNameUpper = ToUpper(trim($sSearchName));
                $bSuccess = false;
                foreach ($arTree as $arTreeItem) {
                    if ($arTreeItem['IBLOCK_SECTION_ID'] != $iParentSectionId) {
                        continue;
                    }
                    if ($arTreeItem['UPPER_NAME'] == $sSearchNameUpper) {
                        $bSuccess = true;
                        $iParentSectionId = $arTreeItem['ID'];
                        $arReturn = $arTreeItem;
                        break;
                    }
                }
                if (!$bSuccess) {
                    $arReturn = array();
                    break;
                }
            }
        }
        return $arReturn;
    }

    protected function findMenuSectionByName($sSearchStr, $iParentSectionId) {
        $arReturn = array();
        $dbItems = \CIBlockSection::GetList(
            array(
                'ID' => 'ASC'
            ),
            array(
                'IBLOCK_ID' => $this->getMenuIBlockId(),
                'NAME' => $sSearchStr,
                'SECTION_ID' => intval($iParentSectionId)
            ),
            false,
            array(
                'ID',
            )
        );
        while ($arItem = $dbItems->fetch()) {
            $arReturn = $arItem;
        }
        return $arReturn;
    }

    protected function findMenuElementByName($sSearchStr, $iParentSectionId) {
        $arReturn = array();
        $dbItems = \CIBlockElement::GetList(
            array(
                'ID' => 'ASC'
            ),
            array(
                'IBLOCK_ID' => $this->getMenuIBlockId(),
                'NAME' => $sSearchStr,
                'SECTION_ID' => intval($iParentSectionId)
            ),
            false,
            array(
                'nTopCount' => 1
            ),
            array(
                'ID',
            )
        );
        while ($arItem = $dbItems->fetch()) {
            $arReturn = $arItem;
        }
        return $arReturn;
    }

    protected function getProductsSectionTree() {
        if (!$this->arProductsSectionTree) {
            $dbItems = \CIBlockSection::GetList(
                array(
                    'LEFT_MARGIN' => 'ASC' // !!!
                ),
                array(
                    'IBLOCK_ID' => $this->getProductsIBlockId(),
                ),
                false,
                array(
                    'ID', 'NAME', 'IBLOCK_SECTION_ID',
                    'LEFT_MARGIN', 'RIGHT_MARGIN', 'DEPTH_LEVEL',
                )
            );
            while ($arItem = $dbItems->fetch()) {
                $arItem['UPPER_NAME'] = ToUpper($arItem['NAME']);
                $arItem['IBLOCK_SECTION_ID'] = intval($arItem['IBLOCK_SECTION_ID']);
                $this->arProductsSectionTree[$arItem['ID']] = $arItem;
            }
        }
        return $this->arProductsSectionTree;
    }
}
