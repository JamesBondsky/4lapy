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
     * @param array $fields
     * @return AddResult
     * @throws \Exception
     */
    public static function add(array $fields): AddResult
	{
		/** @var Main\Authentication\Context $authContext */
		$authContext = null;

		$entity = static::getEntity();
		$result = new AddResult();

		try
		{
			$connection = $entity->getConnection();

			$tableName = $entity->getDBTableName();
			$identity = $entity->getAutoIncrement();

            $dataReplacedColumn = [];
            $accountNumber = (int)$fields['ACCOUNT_NUMBER'];
			if ($accountNumber)
            {
                $dataReplacedColumn = [
                    'ACCOUNT_NUMBER' => $accountNumber
                ];
            }
			$id = $connection->add($tableName, $dataReplacedColumn, $identity);

			$primary = null;

			if (!empty($id))
			{
				$primary = array($entity->getAutoIncrement() => $id);
			}
			$result->setPrimary($primary);

			$entity->cleanCache();
		}
		catch (\Exception $e)
		{
			// check result to avoid warning
			$result->isSuccess();

			throw $e;
		}

		return $result;
	}
}