<?php
/*
 * @copyright Copyright (c) ADV/web-engineering co.
 */

namespace Sprint\Migration;


use Adv\Bitrixtools\Exception\IblockNotFoundException;
use Adv\Bitrixtools\Migration\SprintMigrationBase;
use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use Bitrix\Main\Application;
use Bitrix\Main\Db\SqlQueryException;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;

class IblockOfferAddPropertyIsSale20180605140005 extends SprintMigrationBase
{
    protected $description = 'Добавление свойства "Распродажа" в ИБ офферов';

    /**
     * @return bool|void
     * @throws IblockNotFoundException
     * @throws SqlQueryException
     */
    public function up()
    {
        Application::getConnection()->query(
            'DELETE FROM sprint_migration_versions WHERE version = "IblockOfferDeletePropertyIsSale20180605113631"'
        );

        $iblockId = IblockUtils::getIblockId(IblockType::CATALOG, IblockCode::OFFERS);
        $this->getHelper()->Iblock()->addPropertyIfNotExists($iblockId, [
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

        $this->getHelper()->AdminIblock()->buildElementForm($iblockId, [
            'Торговое предложение'  =>
                [
                    'ID'                        => 'ID',
                    'DATE_CREATE'               => 'Создан',
                    'TIMESTAMP_X'               => 'Изменен',
                    'ACTIVE'                    => 'Активность',
                    'PROPERTY_IS_NEW'           => 'Новинка',
                    'PROPERTY_IS_HIT'           => 'Хит',
                    'PROPERTY_IS_SALE'          => 'Распродажа',
                    'PROPERTY_IS_POPULAR'       => 'Популярный',
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

    public function down()
    {

    }
}