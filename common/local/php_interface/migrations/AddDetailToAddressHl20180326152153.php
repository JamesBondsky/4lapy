<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace Sprint\Migration;

use Adv\Bitrixtools\Migration\SprintMigrationBase;

class AddDetailToAddressHl20180326152153 extends SprintMigrationBase
{
    protected $description = 'Add details field in address';

    public function up()
    {
        $hlblockId = $this->getHelper()->Hlblock()->getHlblockId('Address');
        $entityId = 'HLBLOCK_' . $hlblockId;

        $this
            ->getHelper()
            ->UserTypeEntity()
            ->addUserTypeEntityIfNotExists(
                $entityId,
                'UF_DETAILS',
                [
                    'FIELD_NAME'        => 'UF_DETAILS',
                    'USER_TYPE_ID'      => 'string',
                    'XML_ID'            => '',
                    'SORT'              => '1100',
                    'MULTIPLE'          => 'N',
                    'MANDATORY'         => 'N',
                    'SHOW_FILTER'       => 'N',
                    'SHOW_IN_LIST'      => 'Y',
                    'EDIT_IN_LIST'      => 'Y',
                    'IS_SEARCHABLE'     => 'N',
                    'SETTINGS'          => [
                        'SIZE'          => 20,
                        'MIN_VALUE'     => 0,
                        'MAX_VALUE'     => 1024,
                        'DEFAULT_VALUE' => '',
                    ],
                    'EDIT_FORM_LABEL'   => [
                        'ru' => 'Детали',
                    ],
                    'LIST_COLUMN_LABEL' => [
                        'ru' => 'Детали',
                    ],
                    'LIST_FILTER_LABEL' => [
                        'ru' => 'Детали',
                    ],
                    'ERROR_MESSAGE'     => [
                        'ru' => '',
                    ],
                    'HELP_MESSAGE'      => [
                        'ru' => 'Детали',
                    ],
                ]
            );
    }

    public function down()
    {
    }
}
