<?php

namespace Sprint\Migration;


use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;

class FashionFooterPropSectionLink20190906133132 extends \Adv\Bitrixtools\Migration\SprintMigrationBase
{
    protected $description = 'Добавление свойства "Ссылка в каталог" с привязкой в инфоблок "Одежда: категории товаров"';

    protected $propCode = 'SECTION_LINK';

    public function up()
    {
        $helper = new HelperManager();

        $iblockId = IblockUtils::getIblockId(IblockType::GRANDIN, IblockCode::FASHION_FOOTER_PRODUCTS);

        $helper->Iblock()->addPropertyIfNotExists($iblockId, array(
            'NAME' => 'Ссылка в каталог',
            'ACTIVE' => 'Y',
            'SORT' => '500',
            'CODE' => $this->propCode,
            'DEFAULT_VALUE' => '',
            'PROPERTY_TYPE' => 'S',
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
            'VERSION' => '1',
            'USER_TYPE' => NULL,
            'USER_TYPE_SETTINGS' => NULL,
            'HINT' => '',
        ));
    }

    public function down()
    {
        $helper = new HelperManager();
        $iblockId = IblockUtils::getIblockId(IblockType::PUBLICATION, IblockCode::BANNERS);

        $helper->Iblock()->deletePropertyIfExists($iblockId, $this->propCode);
    }

}
