<?php

namespace Sprint\Migration;


use Adv\Bitrixtools\Migration\SprintMigrationBase;
use Bitrix\Main\Application;
use FourPaws\App\Application as App;
use Bitrix\Main\Db\SqlQueryException;
use FourPaws\KkmBundle\Service\KkmService;
use FourPaws\StoreBundle\Entity\Store;
use FourPaws\StoreBundle\Repository\StoreRepository;

class KkmTableToken20190514133439 extends SprintMigrationBase
{

    protected $description = "Добавляет таблицу для хранения токенов";

    public function up()
    {
        /** @var KkmService $kkmService */
        $kkmService = App::getInstance()->getContainer()->get('kkm.service');
        /** @var StoreRepository $storeRepo */
        $storeRepo = App::getInstance()->getContainer()->get(StoreRepository::class);
        try {
            Application::getConnection()->query('DROP TABLE IF EXISTS `4lapy_kkm_token`;');
            Application::getConnection()->query('
                CREATE TABLE `4lapy_kkm_token` (
                    `id` INT NOT NULL AUTO_INCREMENT,
                    `token` CHAR(16) NOT NULL,
                    `store_code` CHAR(16) NOT NULL,
                    PRIMARY KEY (`id`)
                )'
            );

            Application::getConnection()->query('ALTER TABLE `4lapy_kkm_token` ADD INDEX `ix_token` (`token`);');

            $stores = $storeRepo->findBy(['ACTIVE' => 'Y', 'UF_IS_SHOP' => true, 'UF_IS_SUPPLIER' => false]);

            /** @var Store $store */
            foreach ($stores as $store) {
                $token = $kkmService->generateToken();
                Application::getConnection()->query('INSERT INTO `4lapy_kkm_token` (token, store_code) VALUES (\'' . $token . '\',\'' . $store->getXmlId() . '\')');
            }

            return true;
        } catch (SqlQueryException $e) {
            return false;
        }
    }

    public function down()
    {
        try {
            Application::getConnection()->query('DROP TABLE IF EXISTS `4lapy_kkm_token`;');
            return true;
        } catch (SqlQueryException $e) {
            return false;
        }
    }
}
