<?php

namespace Sprint\Migration;


use Adv\Bitrixtools\Migration\SprintMigrationBase;
use Bitrix\Main\Application;
use FourPaws\Enum\IblockCode;

class OfferFieldsHitNew20180223174527 extends SprintMigrationBase
{

    protected $description = 'Добавление флагов "Хит", "Новинка", "Расродажа"';

    /**
     * Удаляем старую отсутствующую миграцию из таблицы миграций
     */
    private function removeCsMigration() {
        $connection = Application::getConnection();
        $sql='delete from sprint_migration_versions where version=\'Catalog_structure20170920134928\'';

        $connection->query($sql);
    }

    public function up()
    {
        $this->removeCsMigration();
        $this->createProperties();
    }

    public function down()
    {
        /**
         * Авось пронесет
         */
    }

    private function createProperties()
    {
        $helper = $this->getHelper();
        $iblockId = $helper->Iblock()->getIblockId(IblockCode::OFFERS);

        $helper->Iblock()->addPropertyIfNotExists($iblockId, [
            'NAME'               => 'Хит',
            'ACTIVE'             => 'Y',
            'SORT'               => '15',
            'CODE'               => 'IS_HIT',
            'DEFAULT_VALUE'      => 0,
            'PROPERTY_TYPE'      => 'N',
            'ROW_COUNT'          => '1',
            'COL_COUNT'          => '30',
            'LIST_TYPE'          => 'L',
            'MULTIPLE'           => 'N',
            'XML_ID'             => '',
            'FILE_TYPE'          => '',
            'MULTIPLE_CNT'       => '5',
            'TMP_ID'             => null,
            'LINK_IBLOCK_ID'     => '0',
            'WITH_DESCRIPTION'   => 'N',
            'SEARCHABLE'         => 'N',
            'FILTRABLE'          => 'Y',
            'IS_REQUIRED'        => 'N',
            'VERSION'            => '2',
            'USER_TYPE'          => 'YesNoPropertyType',
            'USER_TYPE_SETTINGS' => null,
            'HINT'               => '',
        ]);
        $helper->Iblock()->addPropertyIfNotExists($iblockId, [
            'NAME'               => 'Новинка',
            'ACTIVE'             => 'Y',
            'SORT'               => '16',
            'CODE'               => 'IS_NEW',
            'DEFAULT_VALUE'      => 0,
            'PROPERTY_TYPE'      => 'N',
            'ROW_COUNT'          => '1',
            'COL_COUNT'          => '30',
            'LIST_TYPE'          => 'L',
            'MULTIPLE'           => 'N',
            'XML_ID'             => '',
            'FILE_TYPE'          => '',
            'MULTIPLE_CNT'       => '5',
            'TMP_ID'             => null,
            'LINK_IBLOCK_ID'     => '0',
            'WITH_DESCRIPTION'   => 'N',
            'SEARCHABLE'         => 'N',
            'FILTRABLE'          => 'Y',
            'IS_REQUIRED'        => 'N',
            'VERSION'            => '2',
            'USER_TYPE'          => 'YesNoPropertyType',
            'USER_TYPE_SETTINGS' => null,
            'HINT'               => '',
        ]);
        $helper->Iblock()->addPropertyIfNotExists($iblockId, [
            'NAME'               => 'Распродажа',
            'ACTIVE'             => 'Y',
            'SORT'               => '17',
            'CODE'               => 'IS_SALE',
            'DEFAULT_VALUE'      => 0,
            'PROPERTY_TYPE'      => 'N',
            'ROW_COUNT'          => '1',
            'COL_COUNT'          => '30',
            'LIST_TYPE'          => 'L',
            'MULTIPLE'           => 'N',
            'XML_ID'             => '',
            'FILE_TYPE'          => '',
            'MULTIPLE_CNT'       => '5',
            'TMP_ID'             => null,
            'LINK_IBLOCK_ID'     => '0',
            'WITH_DESCRIPTION'   => 'N',
            'SEARCHABLE'         => 'N',
            'FILTRABLE'          => 'Y',
            'IS_REQUIRED'        => 'N',
            'VERSION'            => '2',
            'USER_TYPE'          => 'YesNoPropertyType',
            'USER_TYPE_SETTINGS' => null,
            'HINT'               => '',
        ]);

        $helper->AdminIblock()->buildElementForm($iblockId, [
            'Торговое предложение'  =>
                [
                    'ID'                        => 'ID',
                    'DATE_CREATE'               => 'Создан',
                    'TIMESTAMP_X'               => 'Изменен',
                    'ACTIVE'                    => 'Активность',
                    'PROPERTY_IS_NEW'           => 'Новинка',
                    'PROPERTY_IS_HIT'           => 'Популярный хит',
                    'PROPERTY_IS_SALE'          => 'Распродажа',
                    'ACTIVE_FROM'               => 'Начало активности',
                    'ACTIVE_TO'                 => 'Окончание активности',
                    'NAME'                      => 'Название',
                    'PROPERTY_CML2_LINK'        => 'Товар',
                    'XML_ID'                    => 'Внешний код (Material Number из SAP)',
                    'PROPERTY_PRICE_ACTION'     => 'Цена по акции',
                    'PROPERTY_COND_FOR_ACTION'  => 'Тип цены по акции',
                    'PROPERTY_COND_VALUE'       => 'Размер скидки на товар',
                    'PROPERTY_COLOUR'           => 'Цвет',
                    'PROPERTY_VOLUME'           => 'Объём',
                    'PROPERTY_VOLUME_REFERENCE' => 'Объём (справочник)',
                    'PROPERTY_CLOTHING_SIZE'    => 'Размер одежды',
                    'PROPERTY_IMG'              => 'Изображения',
                    'PROPERTY_BARCODE'          => 'Штрих-код',
                    'PROPERTY_KIND_OF_PACKING'  => 'Вид упаковки',
                    'PROPERTY_SEASON_YEAR'      => 'Год сезона',
                    'PROPERTY_MULTIPLICITY'     => 'Кратность упаковки',
                    'PROPERTY_BY_REQUEST'       => 'Под заказ',
                    'PROPERTY_REWARD_TYPE'      => 'Тип вознаграждения для заводчика',
                ],
            'Изображение'           =>
                [
                    'DETAIL_PICTURE' => 'Изображение конкретного товара',
                ],
            'Торговый каталог'      =>
                [
                    'CATALOG' => 'Торговый каталог',
                ],
            'SEO'                   =>
                [
                    'IPROPERTY_TEMPLATES_ELEMENT_META_TITLE'                 => 'Шаблон META TITLE',
                    'IPROPERTY_TEMPLATES_ELEMENT_META_KEYWORDS'              => 'Шаблон META KEYWORDS',
                    'IPROPERTY_TEMPLATES_ELEMENT_META_DESCRIPTION'           => 'Шаблон META DESCRIPTION',
                    'IPROPERTY_TEMPLATES_ELEMENT_PAGE_TITLE'                 => 'Заголовок элемента',
                    'IPROPERTY_TEMPLATES_ELEMENTS_PREVIEW_PICTURE'           => 'Настройки для картинок анонса элементов',
                    'IPROPERTY_TEMPLATES_ELEMENT_PREVIEW_PICTURE_FILE_ALT'   => 'Шаблон ALT',
                    'IPROPERTY_TEMPLATES_ELEMENT_PREVIEW_PICTURE_FILE_TITLE' => 'Шаблон TITLE',
                    'IPROPERTY_TEMPLATES_ELEMENT_PREVIEW_PICTURE_FILE_NAME'  => 'Шаблон имени файла',
                    'IPROPERTY_TEMPLATES_ELEMENTS_DETAIL_PICTURE'            => 'Настройки для детальных картинок элементов',
                    'IPROPERTY_TEMPLATES_ELEMENT_DETAIL_PICTURE_FILE_ALT'    => 'Шаблон ALT',
                    'IPROPERTY_TEMPLATES_ELEMENT_DETAIL_PICTURE_FILE_TITLE'  => 'Шаблон TITLE',
                    'IPROPERTY_TEMPLATES_ELEMENT_DETAIL_PICTURE_FILE_NAME'   => 'Шаблон имени файла',
                    'SEO_ADDITIONAL'                                         => 'Дополнительно',
                    'TAGS'                                                   => 'Теги',
                ],
            'Технические параметры' =>
                [
                    'PROPERTY_FLAVOUR_COMBINATION' => 'Объединение по вкусу',
                    'PROPERTY_21'                  => 'Объединение по фасовке',
                    'PROPERTY_COLOUR_COMBINATION'  => 'Объединение по цвету',
                    'PROPERTY_OLD_URL'             => 'Старый URL',
                ],
        ]);
    }

}
