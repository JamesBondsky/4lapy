<?php

namespace Sprint\Migration;


class AddFieldImagesCommentsHl20190913161819 extends \Adv\Bitrixtools\Migration\SprintMigrationBase
{
    const HL_NAME = 'Comments';

    const FIELD_NAME = 'UF_IMAGES';

    protected $description = 'Добавление поля "Изображения" в highload комметарии';

    public function up()
    {
        $helper = new HelperManager();

        $hlblockId = (int)$helper->Hlblock()->getHlblockId(static::HL_NAME);
        if ($hlblockId > 0) {
            $entityId = 'HLBLOCK_' . $hlblockId;
            $helper->UserTypeEntity()->addUserTypeEntityIfNotExists(
                $entityId,
                static::FIELD_NAME,
                [
                    'FIELD_NAME' => static::FIELD_NAME,
                    'USER_TYPE_ID' => 'file',
                    'XML_ID' => static::FIELD_NAME,
                    'SORT' => 200,
                    'MULTIPLE' => 'Y',
                    'MANDATORY' => 'N',
                    'SHOW_FILTER' => 'N',
                    'SHOW_IN_LIST' => 'Y',
                    'EDIT_IN_LIST' => 'Y',
                    'IS_SEARCHABLE' => 'N',
                    'SETTINGS' => [
                        'SIZE' => 20,
                        'LIST_WIDTH' => 200,
                        'LIST_HEIGHT' => 200,
                        'MAX_SHOW_SIZE' => 0,
                        'MAX_ALLOWED_SIZE' => 0,
                        'EXTENSIONS' => [
                          'png', 'jpg', 'jpeg'
                        ],
                    ],
                    'EDIT_FORM_LABEL' => [
                        'ru' => 'Изображения',
                    ],
                    'LIST_COLUMN_LABEL' => [
                        'ru' => 'Изображения',
                    ],
                    'LIST_FILTER_LABEL' => [
                        'ru' => 'Изображения',
                    ],
                    'ERROR_MESSAGE' => [
                        'ru' => '',
                    ],
                    'HELP_MESSAGE' => [
                        'ru' => 'Изображения',
                    ],
                ]
            );
        }
    }

    public function down()
    {
        $helper = new HelperManager();

        $hlblockId = (int)$helper->Hlblock()->getHlblockId(static::HL_NAME);
        if ($hlblockId > 0) {
            $entityId = 'HLBLOCK_' . $hlblockId;

            $helper->UserTypeEntity()->deleteUserTypeEntityIfExists($entityId, static::FIELD_NAME);
        }
    }
}
