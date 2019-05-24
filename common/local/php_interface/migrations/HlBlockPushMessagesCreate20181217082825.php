<?php

namespace Sprint\Migration;


class HlBlockPushMessagesCreate20181217082825 extends \Adv\Bitrixtools\Migration\SprintMigrationBase {

    protected $description = "Создание HL-блока Push Messages для пуш-уведомлений";

    const HL_BLOCK_NAME = 'PushMessages';

    protected $hlBlockData = [
        'NAME'       => self::HL_BLOCK_NAME,
        'TABLE_NAME' => 'api_push_messages',
        'LANG'       => [
            'ru' => [
                'NAME' => 'Push уведомления',
            ],
        ],
    ];

    protected $fields = [
        [
            'FIELD_NAME'        => 'UF_ACTIVE',
            'USER_TYPE_ID'      => 'boolean',
            'XML_ID'            => 'UF_ACTIVE',
            'SORT'              => 10,
            'MULTIPLE'          => 'N',
            'MANDATORY'         => 'N',
            'SHOW_FILTER'       => 'N',
            'SHOW_IN_LIST'      => 'Y',
            'EDIT_IN_LIST'      => 'Y',
            'IS_SEARCHABLE'     => 'N',
            'EDIT_FORM_LABEL'   => [
                'ru' => 'Активность',
                'en' => 'Active',
            ],
            'LIST_COLUMN_LABEL' => [
                'ru' => 'Активность',
                'en' => 'Active',
            ],
            'LIST_FILTER_LABEL' => [
                'ru' => 'Активность',
                'en' => 'Active',
            ],
            'SETTINGS'          => [
                'DEFAULT_VALUE' => true,
            ],
        ],
        [
            'FIELD_NAME'        => 'UF_MESSAGE',
            'USER_TYPE_ID'      => 'string',
            'XML_ID'            => 'UF_MESSAGE',
            'SORT'              => 20,
            'MULTIPLE'          => 'N',
            'MANDATORY'         => 'Y',
            'SHOW_FILTER'       => 'Y',
            'SHOW_IN_LIST'      => 'Y',
            'EDIT_IN_LIST'      => 'Y',
            'IS_SEARCHABLE'     => 'N',
            'EDIT_FORM_LABEL'   => [
                'ru' => 'Текст сообщения',
            ],
            'LIST_COLUMN_LABEL' => [
                'ru' => 'Текст сообщения',
            ],
            'LIST_FILTER_LABEL' => [
                'ru' => 'Текст сообщения',
            ],
        ],
        [
            'FIELD_NAME'        => 'UF_START_SEND',
            'USER_TYPE_ID'      => 'datetime',
            'XML_ID'            => 'UF_START_SEND',
            'SORT'              => 30,
            'MULTIPLE'          => 'N',
            'MANDATORY'         => 'Y',
            'SHOW_FILTER'       => 'Y',
            'SHOW_IN_LIST'      => 'Y',
            'EDIT_IN_LIST'      => 'Y',
            'IS_SEARCHABLE'     => 'N',
            'SETTINGS'          => [
                'DEFAULT_VALUE' => [
                    'TYPE'  => 'NOW',
                    'VALUE' => '',
                ],
                'USE_SECOND'    => 'Y',
            ],
            'EDIT_FORM_LABEL'   => [
                'ru' => 'Дата и время начала отправки',
            ],
            'LIST_COLUMN_LABEL' => [
                'ru' => 'Дата и время начала отправки',
            ],
            'LIST_FILTER_LABEL' => [
                'ru' => 'Дата и время начала отправки',
            ],
        ],
        [
            'FIELD_NAME'        => 'UF_TYPE',
            'USER_TYPE_ID'      => 'enumeration',
            'XML_ID'            => 'UF_TYPE',
            'SORT'              => 40,
            'MULTIPLE'          => 'N',
            'MANDATORY'         => 'Y',
            'SHOW_FILTER'       => 'Y',
            'SHOW_IN_LIST'      => 'Y',
            'EDIT_IN_LIST'      => 'Y',
            'IS_SEARCHABLE'     => 'N',
            'EDIT_FORM_LABEL'   => [
                'ru' => 'Тип push',
            ],
            'LIST_COLUMN_LABEL' => [
                'ru' => 'Тип push',
            ],
            'LIST_FILTER_LABEL' => [
                'ru' => 'Тип push',
            ],
            'SETTINGS' => [
                'DISPLAY' => 'CHECKBOX'
            ],
            'ENUMS' => [
                'n1' => [
                    'XML_ID' => 'news',
                    'VALUE' => 'Новости'
                ],
                'n2' => [
                    'XML_ID' => 'offers',
                    'VALUE' => 'Акции',
                ],
                'n3' => [
                    'XML_ID' => 'change_order_status',
                    'VALUE' => 'Изменение статуса заказа'
                ],
                'n4' => [
                    'XML_ID' => 'order_review',
                    'VALUE' => 'Возможность оставить отзыв о заказе'
                ],
                'n5' => [
                    'XML_ID' => 'message',
                    'VALUE' => 'Сообщение'
                ]
            ]
        ],
        [
            'FIELD_NAME'        => 'UF_EVENT_ID',
            'USER_TYPE_ID'      => 'integer',
            'XML_ID'            => 'UF_EVENT_ID',
            'SORT'              => 50,
            'MULTIPLE'          => 'N',
            'MANDATORY'         => 'Y',
            'SHOW_FILTER'       => 'N',
            'SHOW_IN_LIST'      => 'Y',
            'EDIT_IN_LIST'      => 'Y',
            'IS_SEARCHABLE'     => 'N',
            'EDIT_FORM_LABEL'   => [
                'ru' => 'ID события (ID новости, акции, заказа)',
            ],
            'LIST_COLUMN_LABEL' => [
                'ru' => 'ID события (ID новости, акции, заказа)',
            ],
            'LIST_FILTER_LABEL' => [
                'ru' => 'ID события (ID новости, акции, заказа)',
            ],
        ],
        [
            'FIELD_NAME'        => 'UF_GROUPS',
            'USER_TYPE_ID'      => 'enumeration',
            'XML_ID'            => 'UF_GROUPS',
            'SORT'              => 60,
            'MULTIPLE'          => 'Y',
            'MANDATORY'         => 'N',
            'SHOW_FILTER'       => 'Y',
            'SHOW_IN_LIST'      => 'Y',
            'EDIT_IN_LIST'      => 'Y',
            'IS_SEARCHABLE'     => 'N',
            'SETTINGS' => [
                'DISPLAY' => 'CHECKBOX'
            ],
            'EDIT_FORM_LABEL'   => [
                'ru' => 'Отправлять всем пользователям, подписанным',
            ],
            'LIST_COLUMN_LABEL' => [
                'ru' => 'Отправлять всем пользователям, подписанным',
            ],
            'LIST_FILTER_LABEL' => [
                'ru' => 'Отправлять всем пользователям, подписанным',
            ],
            'ENUMS' => [
                'n1' => [
                    'XML_ID' => 'UF_PUSH_NEWS',
                    'VALUE' => 'на новости и акции'
                ],
                'n2' => [
                    'XML_ID' => 'UF_PUSH_ORD_STAT',
                    'VALUE' => 'на изменение статуса заказа',
                ],
            ]
        ],
        [
            'FIELD_NAME'        => 'UF_USERS',
            'USER_TYPE_ID'      => 'integer',
            'XML_ID'            => 'UF_USERS',
            'SORT'              => 70,
            'MULTIPLE'          => 'Y',
            'MANDATORY'         => 'N',
            'SHOW_FILTER'       => 'N',
            'SHOW_IN_LIST'      => 'Y',
            'EDIT_IN_LIST'      => 'Y',
            'IS_SEARCHABLE'     => 'N',
            'EDIT_FORM_LABEL'   => [
                'ru' => 'Дополнительно отправлять указанным пользователям',
            ],
            'LIST_COLUMN_LABEL' => [
                'ru' => 'Дополнительно отправлять указанным пользователям',
            ],
            'LIST_FILTER_LABEL' => [
                'ru' => 'Дополнительно отправлять указанным пользователям',
            ],
        ],
        [
            'FIELD_NAME'        => 'UF_FILE',
            'USER_TYPE_ID'      => 'file',
            'XML_ID'            => 'UF_FILE',
            'SORT'              => 80,
            'MULTIPLE'          => 'N',
            'MANDATORY'         => 'N',
            'SHOW_FILTER'       => 'N',
            'SHOW_IN_LIST'      => 'Y',
            'EDIT_IN_LIST'      => 'Y',
            'IS_SEARCHABLE'     => 'N',
            'EDIT_FORM_LABEL'   => [
                'ru' => 'Файл с телефонами пользователей (удаляется после обработки)',
            ],
            'LIST_COLUMN_LABEL' => [
                'ru' => 'Файл с телефонами пользователей (удаляется после обработки)',
            ],
            'LIST_FILTER_LABEL' => [
                'ru' => 'Файл с телефонами пользователей (удаляется после обработки)',
            ],
        ],
        [
            'FIELD_NAME'        => 'UF_PLATFORM',
            'USER_TYPE_ID'      => 'enumeration',
            'XML_ID'            => 'UF_PLATFORM',
            'SORT'              => 90,
            'MULTIPLE'          => 'N',
            'MANDATORY'         => 'N',
            'SHOW_FILTER'       => 'N',
            'SHOW_IN_LIST'      => 'Y',
            'EDIT_IN_LIST'      => 'Y',
            'IS_SEARCHABLE'     => 'N',
            'SETTINGS' => [
                'DISPLAY' => 'CHECKBOX'
            ],
            'EDIT_FORM_LABEL'   => [
                'ru' => 'Отправлять на устройства',
            ],
            'LIST_COLUMN_LABEL' => [
                'ru' => 'Отправлять на устройства',
            ],
            'LIST_FILTER_LABEL' => [
                'ru' => 'Отправлять на устройства',
            ],
            'ENUMS' => [
                'n1' => [
                    'XML_ID' => 'ios',
                    'VALUE' => 'iOS'
                ],
                'n2' => [
                    'XML_ID' => 'android',
                    'VALUE' => 'Android',
                ],
            ]
        ],
    ];

    public function up(){
        $helper = new HelperManager();
        $hlBlockHelper = $helper->Hlblock();


        /** @var \Sprint\Migration\Helpers\UserTypeEntityHelper $userTypeEntityHelper */
        $userTypeEntityHelper = $this->getHelper()->UserTypeEntity();

        if (!$hlBlockId = $hlBlockHelper->getHlblockId(static::HL_BLOCK_NAME)) {
            if ($hlBlockId = $hlBlockHelper->addHlblock($this->hlBlockData)) {
                $this->log()->info('Добавлен HL-блок ' . static::HL_BLOCK_NAME);
            } else {
                $this->log()->error('Ошибка при создании HL-блока ' . static::HL_BLOCK_NAME);

                return false;
            }
        } else {
            $this->log()->info('HL-блок ' . static::HL_BLOCK_NAME . ' уже существует');
        }

        $entityId  = 'HLBLOCK_' . $hlBlockId;

        foreach ($this->fields as $field) {
            if ($fieldId = $userTypeEntityHelper->addUserTypeEntityIfNotExists(
                $entityId,
                $field['FIELD_NAME'],
                $field
            )) {
                $this->log()->info(
                    'Добавлено поле ' . $field['FIELD_NAME'] . ' в HL-блок ' . self::HL_BLOCK_NAME
                );
            } else {
                $this->log()->error(
                    'Ошибка при добавлении поля ' . $field['FIELD_NAME'] . ' в HL-блок ' . self::HL_BLOCK_NAME
                );

                return false;
            }

            if ($field['ENUMS']) {
                $enum = new \CUserFieldEnum();
                if ($enum->SetEnumValues($fieldId, $field['ENUMS'])) {
                    $this->log()->info('Добавлены значения для поля ' . $field['FIELD_NAME']);
                } else {
                    $this->log()->error('Не удалось добавить значения для поля ' . $field['FIELD_NAME']);
                }
            }
        }

    }

    public function down(){
        $helper = new HelperManager();

        if (!$hlBlockId = $helper->Hlblock()->getHlblockId(static::HL_BLOCK_NAME)) {
            $this->log()->error('HL-блок ' . static::HL_BLOCK_NAME . ' не найден');

            return true;
        }

        if ($helper->Hlblock()->deleteHlblock($hlBlockId)) {
            $this->log()->info('HL-блок ' . static::HL_BLOCK_NAME . ' удален');
        } else {
            $this->log()->error('Ошибка при удалении HL-блока ' . static::HL_BLOCK_NAME);

            return false;
        }

        return true;

    }

}
