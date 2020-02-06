<?php
namespace Alexkova\Fileinspector;

use Bitrix\Main\Entity;
use Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

/**
 * Class StatisticTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> BASE_ID int optional
 * <li> TIMESTAMP_X datetime mandatory default 'CURRENT_TIMESTAMP'
 * <li> DATE_MODIFY datetime optional
 * <li> MODULE_ID string(50) optional
 * <li> HEIGHT int optional
 * <li> WIDTH int optional
 * <li> FILE_SIZE int mandatory
 * <li> CONTENT_TYPE string(255) optional
 * <li> SUBDIR string(255) optional
 * <li> FILE_NAME string(255) optional
 * <li> ORIGINAL_NAME string(255) optional
 * <li> DESCRIPTION string(255) optional
 * <li> HANDLER_ID string(50) optional
 * <li> DEFFECT_CODE string(6) optional
 * <li> DEFFECT_DETAIL string optional
 * <li> SEARCH_DETAIL string optional
 * </ul>
 *
 * @package Bitrix\Fileinspector
 **/

class StatisticTable extends Entity\DataManager
{
	public static function getFilePath()
	{
		return __FILE__;
	}

	public static function getTableName()
	{
		return 'alexkova_fileinspector_statistic';
	}

	public static function getMap()
	{
		return array(
			'ID' => array(
				'data_type' => 'integer',
				'primary' => true,
				'autocomplete' => true,
				'title' => Loc::getMessage('STATISTIC_ENTITY_ID_FIELD'),
				'def' => 'Y',
			),
			'BASE_ID' => array(
				'data_type' => 'integer',
				'title' => Loc::getMessage('STATISTIC_ENTITY_BASE_ID_FIELD'),
			),
			'TIMESTAMP_X' => array(
				'data_type' => 'datetime',
				'required' => true,
				'title' => Loc::getMessage('STATISTIC_ENTITY_TIMESTAMP_X_FIELD'),
			),
			'DATE_MODIFY' => array(
				'data_type' => 'datetime',
				'title' => Loc::getMessage('STATISTIC_ENTITY_DATE_MODIFY_FIELD'),
			),
			'MODULE_ID' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateModuleId'),
				'title' => Loc::getMessage('STATISTIC_ENTITY_MODULE_ID_FIELD'),
				'def' => 'Y',
			),
			'OPERATION_ID' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateModuleId'),
				'title' => Loc::getMessage('STATISTIC_ENTITY_OPERATION_ID_FIELD'),
				'def' => 'Y',
			),
			'HEIGHT' => array(
				'data_type' => 'integer',
				'title' => Loc::getMessage('STATISTIC_ENTITY_HEIGHT_FIELD'),
			),
			'WIDTH' => array(
				'data_type' => 'integer',
				'title' => Loc::getMessage('STATISTIC_ENTITY_WIDTH_FIELD'),
			),
			'FILE_SIZE' => array(
				'data_type' => 'integer',
				'required' => true,
				'title' => Loc::getMessage('STATISTIC_ENTITY_FILE_SIZE_FIELD'),
			),
			'CONTENT_TYPE' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateContentType'),
				'title' => Loc::getMessage('STATISTIC_ENTITY_CONTENT_TYPE_FIELD'),
			),
			'SUBDIR' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateSubdir'),
				'title' => Loc::getMessage('STATISTIC_ENTITY_SUBDIR_FIELD'),
			),
			'FILE_NAME' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateFileName'),
				'title' => Loc::getMessage('STATISTIC_ENTITY_FILE_NAME_FIELD'),
				'def' => 'Y',
			),
			'ORIGINAL_NAME' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateOriginalName'),
				'title' => Loc::getMessage('STATISTIC_ENTITY_ORIGINAL_NAME_FIELD'),
			),
			'DESCRIPTION' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateDescription'),
				'title' => Loc::getMessage('STATISTIC_ENTITY_DESCRIPTION_FIELD'),
			),
			'HANDLER_ID' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateHandlerId'),
				'title' => Loc::getMessage('STATISTIC_ENTITY_HANDLER_ID_FIELD'),
			),
			'DEFFECT_CODE' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateDeffectCode'),
				'title' => Loc::getMessage('STATISTIC_ENTITY_DEFFECT_CODE_FIELD'),
				'def' => 'Y',
			),
			'DEFFECT_DETAIL' => array(
				'data_type' => 'text',
				'title' => Loc::getMessage('STATISTIC_ENTITY_DEFFECT_DETAIL_FIELD'),
				'def' => 'Y',
			),
			'SEARCH_DETAIL' => array(
				'data_type' => 'text',
				'title' => Loc::getMessage('STATISTIC_ENTITY_SEARCH_DETAIL_FIELD'),
				'def' => 'Y',
			),
		);
	}
	public static function validateModuleId()
	{
		return array(
			new Entity\Validator\Length(null, 50),
		);
	}
	public static function validateContentType()
	{
		return array(
			new Entity\Validator\Length(null, 255),
		);
	}
	public static function validateSubdir()
	{
		return array(
			new Entity\Validator\Length(null, 255),
		);
	}
	public static function validateFileName()
	{
		return array(
			new Entity\Validator\Length(null, 255),
		);
	}
	public static function validateOriginalName()
	{
		return array(
			new Entity\Validator\Length(null, 255),
		);
	}
	public static function validateDescription()
	{
		return array(
			new Entity\Validator\Length(null, 255),
		);
	}
	public static function validateHandlerId()
	{
		return array(
			new Entity\Validator\Length(null, 50),
		);
	}
	public static function validateDeffectCode()
	{
		return array(
			new Entity\Validator\Length(null, 6),
		);
	}
}
