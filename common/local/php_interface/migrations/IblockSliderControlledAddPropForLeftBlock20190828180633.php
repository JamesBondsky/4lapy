<?php

namespace Sprint\Migration;


use Adv\Bitrixtools\Exception\IblockNotFoundException;
use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;

class IblockSliderControlledAddPropForLeftBlock20190828180633 extends \Adv\Bitrixtools\Migration\SprintMigrationBase
{

    protected $description = "Добавление свойств для фона и svg в левом блоке для слайдера с параметрами";

    public function up()
    {
        $helper = new HelperManager();
        $this->helper = $helper;

        try {
            $iblockId = IblockUtils::getIblockId(IblockType::PUBLICATION, IblockCode::SLIDER_CONTROLLED);
            $helper->Iblock()->addPropertyIfNotExists(
                $iblockId,
                [
                    'NAME' => 'Svg в левом блоке',
                    'ACTIVE' => 'Y',
                    'SORT' => '500',
                    'CODE' => 'LEFT_SVG',
                    'DEFAULT_VALUE' => '',
                    'PROPERTY_TYPE' => 'F',
                    'ROW_COUNT' => '1',
                    'COL_COUNT' => '30',
                    'LIST_TYPE' => 'L',
                    'MULTIPLE' => 'N',
                    'XML_ID' => '',
                    'FILE_TYPE' => 'svg',
                    'MULTIPLE_CNT' => '5',
                    'TMP_ID' => null,
                    'LINK_IBLOCK_ID' => '0',
                    'WITH_DESCRIPTION' => 'N',
                    'SEARCHABLE' => 'N',
                    'FILTRABLE' => 'N',
                    'IS_REQUIRED' => 'N',
                    'VERSION' => '1',
                    'USER_TYPE' => null,
                    'USER_TYPE_SETTINGS' => null,
                    'HINT' => '',
                ]
            );

            $helper->Iblock()->addPropertyIfNotExists(
                $iblockId,
                [
                    'NAME' => 'Хэш цвета левого блока',
                    'ACTIVE' => 'Y',
                    'SORT' => '500',
                    'CODE' => 'HASH_LEFT_COLOR',
                    'DEFAULT_VALUE' => '',
                    'PROPERTY_TYPE' => 'S',
                    'ROW_COUNT' => '1',
                    'COL_COUNT' => '30',
                    'LIST_TYPE' => 'L',
                    'MULTIPLE' => 'N',
                    'XML_ID' => '',
                    'FILE_TYPE' => '',
                    'MULTIPLE_CNT' => '5',
                    'TMP_ID' => null,
                    'LINK_IBLOCK_ID' => '0',
                    'WITH_DESCRIPTION' => 'N',
                    'SEARCHABLE' => 'N',
                    'FILTRABLE' => 'N',
                    'IS_REQUIRED' => 'N',
                    'VERSION' => '1',
                    'USER_TYPE' => null,
                    'USER_TYPE_SETTINGS' => null,
                    'HINT' => '',
                ]
            );
        } catch (IblockNotFoundException $e) {
        }
    }

    public function down()
    {
        $helper = new HelperManager();

        //your code ...

    }

}
