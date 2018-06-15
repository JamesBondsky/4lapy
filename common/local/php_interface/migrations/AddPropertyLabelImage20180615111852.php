<?php

namespace Sprint\Migration;


use Adv\Bitrixtools\Migration\SprintMigrationBase;
use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;

class AddPropertyLabelImage20180615111852 extends SprintMigrationBase
{
    protected $description = 'Добавления свойства Шильдик(изображение)';

    public function up()
    {
        $helper = new HelperManager();

        $iblockId = IblockUtils::getIblockId(IblockType::PUBLICATION, IblockCode::SHARES);


        $helper->Iblock()->addPropertyIfNotExists($iblockId, [
            'NAME'               => 'Шильдик(изображение)',
            'ACTIVE'             => 'Y',
            'SORT'               => '25',
            'CODE'               => 'LABEL_IMAGE',
            'DEFAULT_VALUE'      => '',
            'PROPERTY_TYPE'      => 'F',
            'ROW_COUNT'          => '1',
            'COL_COUNT'          => '30',
            'LIST_TYPE'          => 'L',
            'MULTIPLE'           => 'N',
            'XML_ID'             => '',
            'FILE_TYPE'          => 'jpg, gif, png, jpeg, svg',
            'MULTIPLE_CNT'       => '5',
            'TMP_ID'             => null,
            'LINK_IBLOCK_ID'     => '0',
            'WITH_DESCRIPTION'   => 'N',
            'SEARCHABLE'         => 'N',
            'FILTRABLE'          => 'N',
            'IS_REQUIRED'        => 'N',
            'VERSION'            => '2',
            'USER_TYPE'          => null,
            'USER_TYPE_SETTINGS' => null,
            'HINT'               => '',
        ]);
    }
}
