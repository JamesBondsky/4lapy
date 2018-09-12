<?php

namespace Sprint\Migration;


use Adv\Bitrixtools\Migration\SprintMigrationBase;

/**
 * Class FilterMergeValuesField20180910194125
 * @package Sprint\Migration
 */
class FilterMergeValuesField20180910194125 extends SprintMigrationBase
{
    protected $description = 'Галочка "использовать объединение значений" в hl-блоке фильтров';

    /**
     *
     * @return bool|void
     */
    public function up()
    {
        $helper = new HelperManager();
        $HLBlockId = $this->getHelper()->Hlblock()->getHlblockId('Filter');
        $HLBlockId = 'HLBLOCK_' . $HLBlockId;
        $helper->UserTypeEntity()->addUserTypeEntityIfNotExists($HLBlockId, 'UF_MERGE_VALUES', [
            'ENTITY_ID' => $HLBlockId,
            'FIELD_NAME' => 'UF_MERGE_VALUES',
            'USER_TYPE_ID' => 'boolean',
            'XML_ID' => 'UF_MERGE_VALUES',
            'SORT' => '600',
            'MULTIPLE' => 'N',
            'MANDATORY' => 'N',
            'SHOW_FILTER' => 'N',
            'SHOW_IN_LIST' => 'Y',
            'EDIT_IN_LIST' => 'Y',
            'IS_SEARCHABLE' => 'N',
            'SETTINGS' => [
                'DEFAULT_VALUE' => 0,
                'DISPLAY' => 'CHECKBOX',
                'LABEL' => [
                    0 => '',
                    1 => '',
                ],
                'LABEL_CHECKBOX' => '',
            ],
            'EDIT_FORM_LABEL' => [
                'ru' => 'Использовать объединение вариантов',
            ],
            'LIST_COLUMN_LABEL' => [
                'ru' => 'Объединить значения',
            ],
            'LIST_FILTER_LABEL' => [
                'ru' => 'Использовать объединение вариантов',
            ],
            'ERROR_MESSAGE' => [
                'ru' => '',
            ],
            'HELP_MESSAGE' => [
                'ru' => 'Объединить значения при отображении в фильтре в соответствии с полем привязки к базовому значению',
            ],
        ]);
    }

    /**
     *
     * @return bool|void
     */
    public function down()
    {
    }
}
