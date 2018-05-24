<?php

namespace Sprint\Migration;

use Adv\Bitrixtools\Migration\SprintMigrationBase;
use Bitrix\Main\Application;
use Bitrix\Main\DB\SqlQueryException;
use Exception;

/**
 * Class SaleLocationExternalZipRemove20180524172318
 *
 * @package Sprint\Migration
 */
class SaleLocationExternalZipRemove20180524172318 extends SprintMigrationBase
{
    private const EXTERNAL_SERVICE_ID = 4;

    protected $description = 'Удаление zip из местоположений';

    /**
     * @throws SqlQueryException
     */
    public function up()
    {
        $connection = Application::getConnection();
        $connection->startTransaction();

        try {
            $connection->query(
                \sprintf(
                    'DELETE FROM b_sale_loc_ext WHERE SERVICE_ID=%d',
                    self::EXTERNAL_SERVICE_ID
                )
            );
            $connection->query(
                \sprintf(
                    'DELETE FROM b_sale_loc_ext_srv WHERE ID=%d',
                    self::EXTERNAL_SERVICE_ID
                )
            );

            $connection->commitTransaction();
        } catch (Exception $e) {
            $connection->rollbackTransaction();
        }
    }
}
