<?
namespace FourPaws\MobileApiOldBundle\Push;

class SessionTable extends \Bitrix\Main\Entity\DataManager
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'user_session';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap()
	{
		return array(
			new \Bitrix\Main\Entity\IntegerField('ID', array(
				'primary' => true,
				'autocomplete' => true,
				'column_name' => 'id',
				'title' => $MESS['EVENT_ENTITY_ID_FIELD'],
			)),
			new \Bitrix\Main\Entity\StringField('TOKEN', array(
				'column_name' => 'token',
				'title' => $MESS['EVENT_ENTITY_TOKEN_FIELD'],
			)),
			new \Bitrix\Main\Entity\IntegerField('USER_ID', array(
				'column_name' => 'user_id',
				'title' => $MESS['EVENT_USER_ID_FIELD'],
			)),
			new \Bitrix\Main\Entity\StringField('PLATFORM', array(
				'values' => array('IOS', 'ANDROID'),
				'column_name' => 'platform',
				'title' => $MESS['EVENT_ENTITY_PLATFORM_FIELD'],
				'fetch_data_modification' => array(__CLASS__, 'fetchDataModifPlatform'),
			)),
			new \Bitrix\Main\Entity\StringField('PUSH_TOKEN', array(
				'column_name' => 'push_token',
				'title' => $MESS['EVENT_ENTITY_PUSH_TOKEN_FIELD'],
			)),
			new \Bitrix\Main\Entity\IntegerField('FUSER_ID', array(
				'column_name' => 'basket_id',
				'title' => $MESS['EVENT_FUSER_ID_FIELD'],
			)),
		);
	}

	public static function fetchDataModifPlatform()
	{
		return array(
			function ($value)
			{
				return strtoupper($value);
			}
		);
	}

	public static function add(array $data)
	{
		throw new \Bitrix\Main\NotImplementedException('Добавление записей запрещено.');
	}
}


$MESS['EVENT_ENTITY_ID_FIELD'] = 'ID';
$MESS['EVENT_ENTITY_TOKEN_FIELD'] = 'Api-токен устройства';
$MESS['EVENT_USER_ID_FIELD'] = 'Id пользователя bitrix';
$MESS['EVENT_ENTITY_PLATFORM_FIELD'] = 'Платформа устройства';
$MESS['EVENT_ENTITY_PUSH_TOKEN_FIELD'] = 'Push-токен устройства';
$MESS['EVENT_FUSER_ID_FIELD'] = 'Id корзины пользователя bitrix';
