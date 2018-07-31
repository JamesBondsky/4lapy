<?php
/*
 * @copyright Copyright (c) ADV/web-engineering co.
 */

namespace Sprint\Migration;

use Adv\Bitrixtools\Migration\SprintMigrationBase;

class DeleteManzanaOrders20180730125341 extends SprintMigrationBase
{
    protected $description = 'Удаление всех заказов из манзаны';

    /**
     * @return bool|void
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     * @throws \Exception
     */
    public function up()
    {
        /**
         * @do nothing
         */
    }

    public function down()
    {

    }
}
