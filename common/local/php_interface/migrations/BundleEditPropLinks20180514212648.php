<?php

namespace Sprint\Migration;


use Adv\Bitrixtools\Migration\SprintMigrationBase;
use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use Bitrix\Highloadblock\HighloadBlockTable;
use Bitrix\Main\UserFieldTable;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;
use FourPaws\Helpers\HighloadHelper;

class BundleEditPropLinks20180514212648 extends SprintMigrationBase
{

    protected $description = 'Изменение свойств привязки';

    public function up()
    {
        $helper = new HelperManager();

        $hlblockId = $helper->Hlblock()->getHlblockId('Bundle');
        $entityId = 'HLBLOCK_' . $hlblockId;

        $hlblockBundleItemsId = $helper->Hlblock()->getHlblockId('BundleItems');
        $entityBundleItemsId = 'HLBLOCK_' . $hlblockBundleItemsId;
        $hlFieldId = UserFieldTable::query()
            ->where('ENTITY_ID', $entityBundleItemsId)
            ->where('FIELD_NAME','UF_PRODUCT')
            ->setSelect(['ID'])
            ->exec()->fetch()['ID'];

        $helper->UserTypeEntity()->updateUserTypeEntityIfExists($entityId, 'UF_PRODUCTS', [
            'SETTINGS'          =>
                [
                    'DISPLAY'       => 'LIST',
                    'LIST_HEIGHT'   => 5,
                    'HLBLOCK_ID'    => $hlblockBundleItemsId,
                    'HLFIELD_ID'    => $hlFieldId,
                    'DEFAULT_VALUE' => 0,
                ],
        ]);

        $helper->UserTypeEntity()->updateUserTypeEntityIfExists($entityBundleItemsId, 'UF_PRODUCT', [
            'SETTINGS'          =>
                [
                    'DISPLAY'       => 'LIST',
                    'LIST_HEIGHT'   => 5,
                    'IBLOCK_ID'     => IblockUtils::getIblockId(IblockType::CATALOG,IblockCode::OFFERS),
                    'DEFAULT_VALUE' => '',
                    'ACTIVE_FILTER' => 'Y',
                ],
        ]);
    }
}
