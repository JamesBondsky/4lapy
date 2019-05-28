<?php
namespace FourPaws\UserBundle\Repository;

use Bitrix\Main;
use Bitrix\Main\Entity\AddResult;
use Bitrix\Main\Entity\IntegerField;
use FourPaws\UserBundle\Exception\InvalidArgumentException;

/**
 * Class FestivalUsersTable
 *
 * Fields:
 * <ul>
 * <li> id int mandatory
 * <li> hash string(32) mandatory
 * <li> date_insert datetime mandatory default 'CURRENT_TIMESTAMP'
 * </ul>
 *
 * @package FourPaws\UserBundle\Repository
 **/

class FestivalUsersTable extends Main\Entity\DataManager
{
    /**
     * Returns DB table name for entity.
     *
     * @return string
     */
    public static function getTableName(): string
    {
        return '4lapy_festival_users';
    }

    /**
     * Returns entity map definition.
     *
     * @return array
     * @throws Main\SystemException
     * @throws \Exception
     */
    public static function getMap(): array
    {
        return array(
            'id' => new IntegerField(
                'id',
                [
                    'primary'      => true,
                    'autocomplete' => true,
                    'title' => 'Id записи регистрации',
                ]
            ),
            'hash' => new Main\Entity\StringField(
                'hash',
                [
                    'required' => true,
                    'validation' => array(__CLASS__, 'validateHash'),
                    'title' => 'Hash записи регистрации',
                ]
            ),
            'date_insert' => new Main\Entity\DatetimeField(
                'date_insert',
                [
                    'required' => true,
                    'title' => 'Дата создания записи регистрации',
                ]
            ),
        );
    }

    /**
     * @param string $hash
     *
     * @return int
     * @throws Main\Db\SqlQueryException
     */
    public static function addCustomized(string $hash): int
	{
	    if ($hash === '')
        {
            throw new InvalidArgumentException('Festival user id creating error: wrong hash: ' . $hash);
        }

		/** @var Main\Authentication\Context $authContext */
		$authContext = null;

		$entity = static::getEntity();

        $connection = $entity->getConnection();

        $tableName = $entity->getDBTableName();
        //$identity = $entity->getAutoIncrement();

        $connection->queryExecute(\sprintf(
            'INSERT INTO %s VALUES (null, "%s", NOW());',
            $tableName,
            $hash
        ));

        $newId = $connection->query(\sprintf(
            'SELECT id 
              FROM %s
              WHERE hash="%s"
              ORDER BY id DESC 
              LIMIT 1;',
            $tableName,
            $hash
        ))->fetch()['id'];

        $entity->cleanCache();

		return $newId;
	}

    /**
     * Returns validators for hash field.
     *
     * @return array
     * @throws Main\ArgumentTypeException
     */
    public static function validateHash(): array
    {
        return array(
            new Main\Entity\Validator\Length(null, 32),
        );
    }
}