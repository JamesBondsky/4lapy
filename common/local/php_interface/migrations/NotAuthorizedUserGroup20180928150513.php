<?php

namespace Sprint\Migration;


use Adv\Bitrixtools\Exception\MigrationFailureException;
use Adv\Bitrixtools\Migration\SprintMigrationBase;
use Bitrix\Main\GroupTable;
use CGroup;

/**
 * Class NotAuthorizedUserGroup20180928150513
 * @package Sprint\Migration
 */
class NotAuthorizedUserGroup20180928150513 extends SprintMigrationBase
{

    protected $description = 'Добавление динамической группы пользователей "Неавторизованные пользователи"';

    /**
     *
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     * @throws MigrationFailureException
     *
     * @return bool|void
     */
    public function up()
    {
        $result = GroupTable::getList([
            'filter' => ['STRING_ID' => 'NOT_AUTH'],
        ]);
        if (!$result->fetchAll()) {
            $cGroup = new CGroup();
            $cGroup->Add([
                'ACTIVE' => 'Y',
                'C_SORT' => 9000,
                'NAME' => 'Неавторизованные',
                'DESCRIPTION' => 'Назначается динамически',
                'STRING_ID' => 'NOT_AUTH'
            ]);
            if ($cGroup->LAST_ERROR) {
                throw new MigrationFailureException($cGroup->LAST_ERROR);
            }
        }
    }

    /**
     * @return bool|void
     */
    public function down()
    {

    }

}
