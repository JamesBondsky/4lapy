<?php

namespace Sprint\Migration;

use Adv\Bitrixtools\Migration\SprintMigrationBase;
use Adv\Bitrixtools\Tools\HLBlock\HLBlockUtils;
use FourPaws\Enum\HlblockCode;

class HLBlockPersonalCouponUsers20190409160630 extends SprintMigrationBase
{

    protected $description = 'Создание HL-блока "Купоны персональных предложений (привязка к пользователям)"';

    public function up()
    {
        $helper = new HelperManager();

        $hlblockId = $helper->Hlblock()->addHlblockIfNotExists(array(
            'NAME' => HlblockCode::PERSONAL_COUPON_USERS,
            'TABLE_NAME' => 'b_hlbd_personal_coupon_users',
            'LANG' =>
                array(
                    'ru' =>
                        array(
                            'NAME' => 'Купоны персональных предложений (привязка к пользователям)',
                        ),
                ),
        ));
        $entityId = 'HLBLOCK_' . $hlblockId;

        $helper->UserTypeEntity()->addUserTypeEntityIfNotExists($entityId, 'UF_COUPON', array(
            'FIELD_NAME' => 'UF_COUPON',
            'USER_TYPE_ID' => 'hlblock',
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
                    'LIST_HEIGHT' => 10,
                    'HLBLOCK_ID' => HLBlockUtils::getHLBlockIdByName(HlblockCode::PERSONAL_COUPON),
                    'HLFIELD_ID' => 0,
                    'DEFAULT_VALUE' => 0,
                ),
            'EDIT_FORM_LABEL' =>
                array(
                    'ru' => 'Купон',
                ),
            'LIST_COLUMN_LABEL' =>
                array(
                    'ru' => 'Купон',
                ),
            'LIST_FILTER_LABEL' =>
                array(
                    'ru' => 'Купон',
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
        $helper->UserTypeEntity()->addUserTypeEntityIfNotExists($entityId, 'UF_USER_ID', array(
            'FIELD_NAME' => 'UF_USER_ID',
            'USER_TYPE_ID' => 'double',
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
                    'PRECISION' => 4,
                    'SIZE' => 20,
                    'MIN_VALUE' => 0.0,
                    'MAX_VALUE' => 0.0,
                    'DEFAULT_VALUE' => '',
                ),
            'EDIT_FORM_LABEL' =>
                array(
                    'ru' => 'ID пользователя',
                ),
            'LIST_COLUMN_LABEL' =>
                array(
                    'ru' => 'ID пользователя',
                ),
            'LIST_FILTER_LABEL' =>
                array(
                    'ru' => 'ID пользователя',
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

        $helper->Hlblock()->deleteHlblockIfExists(HlblockCode::PERSONAL_COUPON_USERS);

    }

}
