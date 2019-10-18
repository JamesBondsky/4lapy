<?php

namespace Sprint\Migration;


use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use Bitrix\Iblock\PropertyEnumerationTable;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;

class AddTypePropertyToBannersIblock20191004135919 extends \Adv\Bitrixtools\Migration\SprintMigrationBase
{
    protected $description = 'Добавление свойства "тип" в инфоблок "баннеры"';

    protected const PROPERTY_CODE = 'TYPE';

    protected const PROPERTY_ENUM = [
        [
            'VALUE' => 'Акция',
            'DEF' => 'N',
            'SORT' => '500',
            'XML_ID' => 'action',
            'TMP_ID' => NULL,
        ],
        [
            'VALUE' => 'Каталог',
            'DEF' => 'N',
            'SORT' => '500',
            'XML_ID' => 'catalog',
            'TMP_ID' => NULL,
        ],
        [
            'VALUE' => 'Новость',
            'DEF' => 'N',
            'SORT' => '500',
            'XML_ID' => 'news',
            'TMP_ID' => NULL,
        ],
        [
            'VALUE' => 'Ссылка',
            'DEF' => 'Y',
            'SORT' => '500',
            'XML_ID' => 'browser',
            'TMP_ID' => NULL,
        ],
        [
            'VALUE' => 'Статья',
            'DEF' => 'N',
            'SORT' => '500',
            'XML_ID' => 'articles',
            'TMP_ID' => NULL,
        ],
        [
            'VALUE' => 'Товар',
            'DEF' => 'N',
            'SORT' => '500',
            'XML_ID' => 'goods',
            'TMP_ID' => NULL,
        ],
    ];

    /**
     * @return bool
     */
    public function up(): bool
    {
        $helper = new HelperManager();

        try {
            $iblockId = IblockUtils::getIblockId(IblockType::PUBLICATION, IblockCode::BANNERS);
        } catch (\Exception $e) {
            return false;
        }

        $propertyId = $helper->Iblock()->addPropertyIfNotExists($iblockId, [
            'NAME' => 'Тип баннера',
            'ACTIVE' => 'Y',
            'SORT' => '500',
            'CODE' => self::PROPERTY_CODE,
            'DEFAULT_VALUE' => '',
            'PROPERTY_TYPE' => 'L',
            'ROW_COUNT' => '1',
            'COL_COUNT' => '30',
            'LIST_TYPE' => 'L',
            'MULTIPLE' => 'N',
            'XML_ID' => '',
            'FILE_TYPE' => '',
            'MULTIPLE_CNT' => '5',
            'TMP_ID' => NULL,
            'LINK_IBLOCK_ID' => '0',
            'WITH_DESCRIPTION' => 'N',
            'SEARCHABLE' => 'N',
            'FILTRABLE' => 'N',
            'IS_REQUIRED' => 'N',
            'VERSION' => '2',
            'USER_TYPE' => NULL,
            'USER_TYPE_SETTINGS' => NULL,
            'HINT' => ''
        ]);

        if (!$propertyId) {
            return false;
        }

        try {
            foreach (self::PROPERTY_ENUM as $propertyEnum) {
                PropertyEnumerationTable::add(array_merge(['PROPERTY_ID' => $propertyId], $propertyEnum));
            }
        } catch (\Exception $e) {
            $this->deleteProperty();
            return false;
        }

        $helper->AdminIblock()->buildElementForm($iblockId, [
            'Настройки' =>
                [
                    'ID' => 'ID',
                    'DATE_CREATE' => 'Дата создания',
                    'TIMESTAMP_X' => 'Дата изменения',
                    'ACTIVE' => 'Активность',
                    'ACTIVE_FROM' => 'Начало активности (время)',
                    'ACTIVE_TO' => 'Окончание активности (время)',
                    'NAME' => 'Название',
                    'SORT' => 'Сортировка',
                    'PROPERTY_LINK' => 'Ссылка',
                    'XML_ID' => 'Внешний код',
                    'PROPERTY_SECTION' => 'Привязка к разделу',
                    'PROPERTY_ELEMENT' => 'Привязка к элементу',
                ],
            'Баннер' =>
                [
                    'PREVIEW_PICTURE' => 'Изображение для мобильного',
                    'PROPERTY_IMG_TABLET' => 'Изображение для планшета для мобильного',
                    'DETAIL_PICTURE' => 'Передний план (Десктоп) для планшета для мобильного',
                    'PROPERTY_BACKGROUND' => 'Фон (Десктоп) для планшета для мобильного',
                    'PROPERTY_LOCATION' => 'Местоположение',
                    'PROPERTY_TYPE' => 'Тип баннера',
                ],
        ]);

        return true;
    }

    /**
     * @return bool
     */
    public function down(): bool
    {
        return $this->deleteProperty();
    }

    /**
     * @return bool
     */
    protected function deleteProperty(): bool
    {
        $helper = new HelperManager();

        try {
            $iblockId = IblockUtils::getIblockId(IblockType::PUBLICATION, IblockCode::BANNERS);
        } catch (\Exception $e) {
            return false;
        }

        $helper->Iblock()->deletePropertyIfExists($iblockId, self::PROPERTY_CODE);

        return true;
    }
}
