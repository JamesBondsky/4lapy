<?php
namespace FourPaws\SaleBundle\Repository;

use Bitrix\Main;
use Bitrix\Main\Entity\AddResult;
use Bitrix\Main\Entity\IntegerField;

/**
 * Class OrderNumberTable
 *
 * Fields:
 * <ul>
 * <li> ACCOUNT_NUMBER int mandatory
 * </ul>
 *
 * @package FourPaws\SaleBundle\Repository
 **/

class OrderNumberTable extends Main\Entity\DataManager
{
    /**
     * Returns DB table name for entity.
     *
     * @return string
     */
    public static function getTableName(): string
    {
        return '4lapy_order_number';
    }

    /**
     * Returns entity map definition.
     *
     * @return array
     * @throws Main\SystemException
     */
    public static function getMap(): array
    {
        return array(
            'ACCOUNT_NUMBER' => new IntegerField(
                'ACCOUNT_NUMBER',
                [
                    'primary'      => true,
                    'autocomplete' => true,
                    'title' => 'Номер заказа',
                ]
            ),
        );
    }

    /**
     * @param int $orderId
     * @return int
     * @throws \Exception
     */
    public static function addCustomized(int $orderId): int
	{
	    if ($orderId <= 0)
        {
            throw new Main\ArgumentNullException('ACCOUNT_NUMBER creating error: wrong ORDER_ID');
        }

		/** @var Main\Authentication\Context $authContext */
		$authContext = null;

		$entity = static::getEntity();

        $connection = $entity->getConnection();

        $tableName = $entity->getDBTableName();
        //$identity = $entity->getAutoIncrement();

        $connection->queryExecute(\sprintf(
            'INSERT INTO %s VALUES (null, %s);',
            $tableName,
            $orderId
        ));

        // Использован такой способ получения PRIMARY-ключа вместо lastInsertId, т.к. есть подозрения,
        // что при втором варианте возникает race condition при большом количестве одновременных запросов
        $newAccountNumber = $connection->query(\sprintf(
            'SELECT ACCOUNT_NUMBER 
              FROM %s
              WHERE ORDER_ID=%s
              LIMIT 1;',
            $tableName,
            $orderId
        ))->fetch()['ACCOUNT_NUMBER'];

        $entity->cleanCache();

		return $newAccountNumber;
	}
}