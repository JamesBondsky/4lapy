<?php

namespace Sprint\Migration;

use Adv\Bitrixtools\Migration\SprintMigrationBase;
use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use FourPaws\Enum\HlblockCode;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;

class HLBlockPersonalCoupon20190409160626 extends SprintMigrationBase
{

    protected $description = 'Создание HL-блока "Купоны персональных предложений"';

    public function up()
    {
        $helper = new HelperManager();

        $hlblockId = $helper->Hlblock()->addHlblockIfNotExists(array(
            'NAME' => HlblockCode::PERSONAL_COUPON,
            'TABLE_NAME' => 'b_hlbd_personal_coupon',
            'LANG' =>
                array(
                    'ru' =>
                        array(
                            'NAME' => 'Купоны персональных предложений',
                        ),
                ),
        ));
        $entityId = 'HLBLOCK_' . $hlblockId;

        $helper->UserTypeEntity()->addUserTypeEntityIfNotExists($entityId, 'UF_PROMO_CODE', array(
            'FIELD_NAME' => 'UF_PROMO_CODE',
            'USER_TYPE_ID' => 'string',
            'XML_ID' => '',
            'SORT' => '100',
            'MULTIPLE' => 'N',
            'MANDATORY' => 'N',
            'SHOW_FILTER' => 'E',
            'SHOW_IN_LIST' => 'Y',
            'EDIT_IN_LIST' => 'Y',
            'IS_SEARCHABLE' => 'N',
            'SETTINGS' =>
                array(
                    'SIZE' => 20,
                    'ROWS' => 1,
                    'REGEXP' => '',
                    'MIN_LENGTH' => 0,
                    'MAX_LENGTH' => 0,
                    'DEFAULT_VALUE' => '',
                ),
            'EDIT_FORM_LABEL' =>
                array(
                    'ru' => 'Промокод',
                ),
            'LIST_COLUMN_LABEL' =>
                array(
                    'ru' => 'Промокод',
                ),
            'LIST_FILTER_LABEL' =>
                array(
                    'ru' => 'Промокод',
                ),
            'ERROR_MESSAGE' =>
                array(
                    'ru' => '',
                ),
            'HELP_MESSAGE' =>
                array(
                    'ru' => '',
                ),
        ));
        $helper->UserTypeEntity()->addUserTypeEntityIfNotExists($entityId, 'UF_OFFER', array(
            'FIELD_NAME' => 'UF_OFFER',
            'USER_TYPE_ID' => 'iblock_element',
            'XML_ID' => '',
            'SORT' => '100',
            'MULTIPLE' => 'N',
            'MANDATORY' => 'Y',
            'SHOW_FILTER' => 'I',
            'SHOW_IN_LIST' => 'Y',
            'EDIT_IN_LIST' => 'Y',
            'IS_SEARCHABLE' => 'N',
            'SETTINGS' =>
                array(
                    'DISPLAY' => 'LIST',
                    'LIST_HEIGHT' => 5,
                    'IBLOCK_ID' => IblockUtils::getIblockId(IblockType::PUBLICATION, IblockCode::PERSONAL_OFFERS),
                    'DEFAULT_VALUE' => '',
                    'ACTIVE_FILTER' => 'N',
                ),
            'EDIT_FORM_LABEL' =>
                array(
                    'ru' => 'Персональное предложение',
                ),
            'LIST_COLUMN_LABEL' =>
                array(
                    'ru' => 'Персональное предложение',
                ),
            'LIST_FILTER_LABEL' =>
                array(
                    'ru' => 'Персональное предложение',
                ),
            'ERROR_MESSAGE' =>
                array(
                    'ru' => '',
                ),
            'HELP_MESSAGE' =>
                array(
                    'ru' => '',
                ),
        ));
        $helper->UserTypeEntity()->addUserTypeEntityIfNotExists($entityId, 'UF_DATE_CREATED', array(
            'FIELD_NAME' => 'UF_DATE_CREATED',
            'USER_TYPE_ID' => 'datetime',
            'XML_ID' => '',
            'SORT' => '100',
            'MULTIPLE' => 'N',
            'MANDATORY' => 'Y',
            'SHOW_FILTER' => 'N',
            'SHOW_IN_LIST' => 'Y',
            'EDIT_IN_LIST' => 'Y',
            'IS_SEARCHABLE' => 'N',
            'SETTINGS' =>
                array(
                    'DEFAULT_VALUE' =>
                        array(
                            'TYPE' => 'NOW',
                            'VALUE' => '',
                        ),
                    'USE_SECOND' => 'Y',
                ),
            'EDIT_FORM_LABEL' =>
                array(
                    'ru' => 'Время создания',
                ),
            'LIST_COLUMN_LABEL' =>
                array(
                    'ru' => 'Время создания',
                ),
            'LIST_FILTER_LABEL' =>
                array(
                    'ru' => 'Время создания',
                ),
            'ERROR_MESSAGE' =>
                array(
                    'ru' => '',
                ),
            'HELP_MESSAGE' =>
                array(
                    'ru' => '',
                ),
        ));
        $helper->UserTypeEntity()->addUserTypeEntityIfNotExists($entityId, 'UF_DATE_CHANGED', array(
            'FIELD_NAME' => 'UF_DATE_CHANGED',
            'USER_TYPE_ID' => 'datetime',
            'XML_ID' => '',
            'SORT' => '100',
            'MULTIPLE' => 'N',
            'MANDATORY' => 'Y',
            'SHOW_FILTER' => 'N',
            'SHOW_IN_LIST' => 'Y',
            'EDIT_IN_LIST' => 'Y',
            'IS_SEARCHABLE' => 'N',
            'SETTINGS' =>
                array(
                    'DEFAULT_VALUE' =>
                        array(
                            'TYPE' => 'NOW',
                            'VALUE' => '',
                        ),
                    'USE_SECOND' => 'Y',
                ),
            'EDIT_FORM_LABEL' =>
                array(
                    'ru' => 'Время изменения',
                ),
            'LIST_COLUMN_LABEL' =>
                array(
                    'ru' => 'Время изменения',
                ),
            'LIST_FILTER_LABEL' =>
                array(
                    'ru' => 'Время изменения',
                ),
            'ERROR_MESSAGE' =>
                array(
                    'ru' => '',
                ),
            'HELP_MESSAGE' =>
                array(
                    'ru' => '',
                ),
        ));
    }

    public function down()
    {
        $helper = new HelperManager();

        $helper->Hlblock()->deleteHlblockIfExists(HlblockCode::PERSONAL_COUPON);

    }

}
