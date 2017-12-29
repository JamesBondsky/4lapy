<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace Sprint\Migration;

use Adv\Bitrixtools\Migration\SprintMigrationBase;
use Bitrix\Main\Application;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Db\SqlQueryException;
use Bitrix\Main\Entity\DatetimeField;
use Bitrix\Main\Entity\StringField;

/**
 * Class ConfirmCode20171213115940
 *
 * @package Sprint\Migration
 */
class ConfirmCode20171213115940 extends SprintMigrationBase
{
    protected $description = 'Создание таблицы для хранения кодов из смс';
    
    /**
     * @throws ArgumentException
     * @throws SqlQueryException
     * @return bool|void
     */
    public function up()
    {
        $connection = Application::getConnection();
        if (!$connection->isTableExists('4lp_ConfirmCode')) {
            /** @noinspection PhpUnhandledExceptionInspection */
            $connection->createTable(
                '4lp_ConfirmCode',
                [
                    'ID'   => new StringField(
                        'ID',
                        [
                                'primary'  => true,
                                'required' => true,
                                'unique'   => true,
                            ]
                    ),
                    'CODE' => new StringField(
                        'CODE',
                        [
                                  'required' => true,
                                  'unique'   => true,
                              ]
                    ),
                    'DATE' => new DatetimeField(
                        'DATE',
                        [
                                  'required' => true,
                              ]
                    ),
                ],
                ['ID']
            );
        }
        
        \CAgent::AddAgent('\FourPaws\UserBundle\Controller\ConfirmCodeAgent::delExpiredCodes();', '', 'Y', 60);
    }
    
    /**
     * @throws SqlQueryException
     * @return bool|void
     */
    public function down()
    {
        $connection = Application::getConnection();
        /** @noinspection PhpUnhandledExceptionInspection */
        $connection->dropTable('4lp_ConfirmCode');
        $res = \CAgent::GetList(
            [],
            ['NAME' => '\FourPaws\UserBundle\Controller\ConfirmCodeAgent::delExpiredCodes();']
        );
        if ($agent = $res->Fetch()) {
            \CAgent::Delete($agent['ID']);
        }
    }
}
