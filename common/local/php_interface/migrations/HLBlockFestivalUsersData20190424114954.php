<?php

namespace Sprint\Migration;


use FourPaws\Enum\HlblockCode;

class HLBlockFestivalUsersData20190424114954 extends \Adv\Bitrixtools\Migration\SprintMigrationBase
{

    protected $description = 'Создание HL-блока "Фестиваль (регистрация)"';

    public function up()
    {
        $helper = new HelperManager();

        $hlblockId = $helper->Hlblock()->addHlblockIfNotExists(array(
            'NAME' => HlblockCode::FESTIVAL_USERS_DATA,
            'TABLE_NAME' => 'b_hlbd_festival_users_data',
            'LANG' =>
                array(
                    'ru' =>
                        array(
                            'NAME' => 'Фестиваль (регистрация)',
                        ),
                ),
        ));
        $entityId = 'HLBLOCK_' . $hlblockId;

        $helper->UserTypeEntity()->addUserTypeEntityIfNotExists($entityId, 'UF_USER', array(
            'FIELD_NAME' => 'UF_USER',
            'USER_TYPE_ID' => 'integer',
            'XML_ID' => '',
            'SORT' => '100',
            'MULTIPLE' => 'N',
            'MANDATORY' => 'N',
            'SHOW_FILTER' => 'I',
            'SHOW_IN_LIST' => 'Y',
            'EDIT_IN_LIST' => 'Y',
            'IS_SEARCHABLE' => 'N',
            'SETTINGS' =>
                array(
                    'SIZE' => 20,
                    'MIN_VALUE' => 0,
                    'MAX_VALUE' => 0,
                    'DEFAULT_VALUE' => '',
                ),
            'EDIT_FORM_LABEL' =>
                array(
                    'ru' => 'Пользователь',
                ),
            'LIST_COLUMN_LABEL' =>
                array(
                    'ru' => 'Пользователь',
                ),
            'LIST_FILTER_LABEL' =>
                array(
                    'ru' => 'Пользователь',
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
        $helper->UserTypeEntity()->addUserTypeEntityIfNotExists($entityId, 'UF_SURNAME', array(
            'FIELD_NAME' => 'UF_SURNAME',
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
                    'ru' => 'Фамилия',
                ),
            'LIST_COLUMN_LABEL' =>
                array(
                    'ru' => 'Фамилия',
                ),
            'LIST_FILTER_LABEL' =>
                array(
                    'ru' => 'Фамилия',
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
        $helper->UserTypeEntity()->addUserTypeEntityIfNotExists($entityId, 'UF_NAME', array(
            'FIELD_NAME' => 'UF_NAME',
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
                    'ru' => 'Имя',
                ),
            'LIST_COLUMN_LABEL' =>
                array(
                    'ru' => 'Имя',
                ),
            'LIST_FILTER_LABEL' =>
                array(
                    'ru' => 'Имя',
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
        $helper->UserTypeEntity()->addUserTypeEntityIfNotExists($entityId, 'UF_PHONE', array(
            'FIELD_NAME' => 'UF_PHONE',
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
                    'ru' => 'Номер телефона',
                ),
            'LIST_COLUMN_LABEL' =>
                array(
                    'ru' => 'Номер телефона',
                ),
            'LIST_FILTER_LABEL' =>
                array(
                    'ru' => 'Номер телефона',
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
        $helper->UserTypeEntity()->addUserTypeEntityIfNotExists($entityId, 'UF_EMAIL', array(
            'FIELD_NAME' => 'UF_EMAIL',
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
                    'ru' => 'Email',
                ),
            'LIST_COLUMN_LABEL' =>
                array(
                    'ru' => 'Email',
                ),
            'LIST_FILTER_LABEL' =>
                array(
                    'ru' => 'Email',
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
        $helper->UserTypeEntity()->addUserTypeEntityIfNotExists($entityId, 'UF_RULES', array(
            'FIELD_NAME' => 'UF_RULES',
            'USER_TYPE_ID' => 'boolean',
            'XML_ID' => '',
            'SORT' => '100',
            'MULTIPLE' => 'N',
            'MANDATORY' => 'N',
            'SHOW_FILTER' => 'I',
            'SHOW_IN_LIST' => 'Y',
            'EDIT_IN_LIST' => 'Y',
            'IS_SEARCHABLE' => 'N',
            'SETTINGS' =>
                array(
                    'DEFAULT_VALUE' => 0,
                    'DISPLAY' => 'CHECKBOX',
                    'LABEL' =>
                        array(
                            0 => '',
                            1 => '',
                        ),
                    'LABEL_CHECKBOX' => '',
                ),
            'EDIT_FORM_LABEL' =>
                array(
                    'ru' => 'Правила приняты',
                ),
            'LIST_COLUMN_LABEL' =>
                array(
                    'ru' => 'Правила приняты',
                ),
            'LIST_FILTER_LABEL' =>
                array(
                    'ru' => 'Правила приняты',
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
        $helper->UserTypeEntity()->addUserTypeEntityIfNotExists($entityId, 'UF_FESTIVAL_USER_ID', array(
            'FIELD_NAME' => 'UF_FESTIVAL_USER_ID',
            'USER_TYPE_ID' => 'integer',
            'XML_ID' => '',
            'SORT' => '100',
            'MULTIPLE' => 'N',
            'MANDATORY' => 'N',
            'SHOW_FILTER' => 'I',
            'SHOW_IN_LIST' => 'Y',
            'EDIT_IN_LIST' => 'Y',
            'IS_SEARCHABLE' => 'N',
            'SETTINGS' =>
                array(
                    'SIZE' => 20,
                    'MIN_VALUE' => 0,
                    'MAX_VALUE' => 0,
                    'DEFAULT_VALUE' => '',
                ),
            'EDIT_FORM_LABEL' =>
                array(
                    'ru' => 'Номер участника',
                ),
            'LIST_COLUMN_LABEL' =>
                array(
                    'ru' => 'Номер участника',
                ),
            'LIST_FILTER_LABEL' =>
                array(
                    'ru' => 'Номер участника',
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
    }

    public function down()
    {
        $helper = new HelperManager();

        $helper->Hlblock()->deleteHlblockIfExists(HlblockCode::FESTIVAL_USERS_DATA);

    }

}
