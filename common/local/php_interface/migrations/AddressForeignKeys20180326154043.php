<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace Sprint\Migration;

use Adv\Bitrixtools\Migration\SprintMigrationBase;
use Bitrix\Highloadblock\DataManager;
use Bitrix\Main\Application;
use Bitrix\Main\Entity\Query\Join;
use Bitrix\Main\Entity\ReferenceField;
use Bitrix\Main\UserTable;
use FourPaws\App\Application as FourPawsApplication;
use Sprint\Migration\Exceptions\MigrationException;

class AddressForeignKeys20180326154043 extends SprintMigrationBase
{
    protected $description = 'Add Address Foreign Key';

    /**
     * @throws \Bitrix\Main\Db\SqlQueryException
     * @throws \Exception
     * @return bool|void
     */
    public function up()
    {
        $this->deleteWrongEntity();
        Application::getConnection()
            ->query('ALTER TABLE adv_adress ADD FOREIGN KEY (UF_USER_ID) REFERENCES b_user(ID) ON DELETE CASCADE');
    }

    /**
     * @throws \RuntimeException
     * @return bool|void
     */
    public function down()
    {
        parent::down();
        throw new \RuntimeException('No down (:');
    }

    /**
     * @throws \Bitrix\Main\Db\SqlQueryException
     * @throws \Exception
     */
    protected function deleteWrongEntity(): void
    {
        Application::getConnection()->startTransaction();
        try {
            /**
             * @var DataManager $dataManager
             */
            $dataManager = FourPawsApplication::getInstance()->getContainer()->get('bx.hlblock.address');
            $result = $dataManager::query()
                ->addSelect('ID')
                ->whereNull('USER.ID')
                ->registerRuntimeField(
                    'USER',
                    new ReferenceField(
                        'USER',
                        UserTable::getEntity(),
                        Join::on('this.UF_USER_ID', 'ref.ID'),
                        ['join_type' => 'LEFT']
                    )
                )
                ->exec();

            while ($item = $result->fetch()) {
                $data = (array)$item;
                if (!$dataManager::delete($data['ID'])) {
                    throw new MigrationException('delete ' . $data['ID'] . ' error');
                }
            }


            Application::getConnection()->commitTransaction();
        } catch (\Exception $exception) {
            Application::getConnection()->rollbackTransaction();
            throw $exception;
        }
    }
}
