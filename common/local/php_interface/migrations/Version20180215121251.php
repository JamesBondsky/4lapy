<?php

namespace Sprint\Migration;


class Version20180215121251 extends \Adv\Bitrixtools\Migration\SprintMigrationBase
{

    protected $description = "Create user fields for mobile api settings";

    public function up()
    {
        $helper = new HelperManager();

        $fieldsCode = ['UF_PUSH_ORD_STAT', 'UF_PUSH_NEWS', 'UF_PUSH_ACC_CHANGE', 'UF_GPS_MESS'];

        foreach ($fieldsCode as $fieldCode) {
            $helper->UserTypeEntity()->addUserTypeEntityIfNotExists('USER', $fieldCode, [
                'ENTITY_ID'     => 'USER',
                'FIELD_NAME'    => $fieldCode,
                'USER_TYPE_ID'  => 'boolean',
                'XML_ID'        => null,
                'SORT'          => '500',
                'MULTIPLE'      => 'N',
                'MANDATORY'     => 'N',
                'SHOW_FILTER'   => 'N',
                'SHOW_IN_LIST'  => 'Y',
                'EDIT_IN_LIST'  => 'Y',
                'IS_SEARCHABLE' => 'N',
                'SETTINGS'      =>
                    [
                        'DEFAULT_VALUE' => 0,
                        'DISPLAY'       => 'CHECKBOX',
                    ],
            ]);
            echo "\n";
        }
    }

    public function down()
    {
    }
}
