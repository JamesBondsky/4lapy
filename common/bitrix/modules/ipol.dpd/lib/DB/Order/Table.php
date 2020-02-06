<?php
namespace Ipolh\DPD\DB\Order;

use \Bitrix\Main;
use \Bitrix\Main\Entity;
use \Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class Table extends Entity\DataManager 
{
	public static function getTableName()
	{
		return 'b_ipol_dpd_order';
	}
	
	public static function getMap()
    {
        return array(
			new Entity\IntegerField(
				'ID',
				array(
					'primary' => true,
					'autocomplete' => true
				)
			),						
			new Entity\IntegerField(
				'ORDER_ID',
				array(
					'required' => true,
					'title' => Loc::getMessage('IPOLH_DPD_TABLE_ORDER_ORDER_ID')					
				)
			),
			new Entity\IntegerField(
				'SHIPMENT_ID',
				array(
					'title' => Loc::getMessage('IPOLH_DPD_TABLE_ORDER_SHIPMENT_ID')					
				)
			),
			new Entity\DatetimeField(
				'ORDER_DATE',
				array(					
					'validation' => array(__CLASS__, 'validateOrderDate'),
					'title' => Loc::getMessage('IPOLH_DPD_TABLE_ORDER_ORDER_DATE')
				)
			),
			new Entity\DatetimeField(
				'ORDER_DATE_CREATE',
				array(
					'validation' => array(__CLASS__, 'validateOrderDateCreate'),
					'title' => Loc::getMessage('IPOLH_DPD_TABLE_ORDER_ORDER_DATE_CREATE')
				)
			),
			new Entity\DatetimeField(
				'ORDER_DATE_CANCEL',
				array(
					'validation' => array(__CLASS__, 'validateOrderDateCancel'),
					'title' => Loc::getMessage('IPOLH_DPD_TABLE_ORDER_ORDER_DATE_CANCEL')
				)
			),
			new Entity\DatetimeField(
				'ORDER_DATE_STATUS',
				array(
					'validation' => array(__CLASS__, 'validateOrderDateStatus'),
					'title' => Loc::getMessage('IPOLH_DPD_TABLE_ORDER_ORDER_DATE_STATUS')
				)
			),
			new Entity\StringField(
				'ORDER_NUM',
				array(
					'validation' => array(__CLASS__, 'validateOrderNum'),
					'title' => Loc::getMessage('IPOLH_DPD_TABLE_ORDER_ORDER_NUM')
				)
			),
			new Entity\TextField(
				'ORDER_STATUS',
				array(
					'title' => Loc::getMessage('IPOLH_DPD_TABLE_ORDER_ORDER_STATUS'),
					'default_value' => 'NEW',
				)
			),
			new Entity\TextField(
				'ORDER_STATUS_CANCEL',
				array(
					'title' => Loc::getMessage('IPOLH_DPD_TABLE_ORDER_ORDER_STATUS_CANCEL')
				)
			),			
			new Entity\TextField(
				'ORDER_ERROR',
				array(
					'title' => Loc::getMessage('IPOLH_DPD_TABLE_ORDER_ORDER_ERROR')
				)
			),
			new Entity\StringField(
				'SERVICE_CODE',
				array(
					'required' => !(isset($_REQUEST['IPOLH_DPD_ORDER']) && $_REQUEST['IPOLH_DPD_ORDER'] == 'Y'),
					'validation' => array(__CLASS__, 'validateServiceCode'),
					'title' => Loc::getMessage('IPOLH_DPD_TABLE_ORDER_SERVICE_CODE')
				)
			),
			new Entity\StringField(
				'SERVICE_VARIANT',
				array(
					'required' => !(isset($_REQUEST['IPOLH_DPD_ORDER']) && $_REQUEST['IPOLH_DPD_ORDER'] == 'Y'),
					'validation' => array(__CLASS__, 'validateServiceVariant'),
					'title' => Loc::getMessage('IPOLH_DPD_TABLE_ORDER_SERVICE_VARIANT')
				)
			),
			new Entity\DateField(
				'PICKUP_DATE',
				array(
					'validation' => array(__CLASS__, 'validatePickupDate'),
					'title' => Loc::getMessage('IPOLH_DPD_TABLE_ORDER_PICKUP_DATE')
				)
			),
			new Entity\StringField(
				'PICKUP_TIME_PERIOD',
				array(
					'required' => !(isset($_REQUEST['IPOLH_DPD_ORDER']) && $_REQUEST['IPOLH_DPD_ORDER'] == 'Y'),
					'validation' => array(__CLASS__, 'validatePickupTimePeriod'),
					'title' => Loc::getMessage('IPOLH_DPD_TABLE_ORDER_PICKUP_TIME_PERIOD')
				)
			),
			new Entity\StringField(
				'DELIVERY_TIME_PERIOD',
				array(
					'validation' => array(__CLASS__, 'validateDeliveryTimePeriod'),
					'title' => Loc::getMessage('IPOLH_DPD_TABLE_ORDER_DELIVERY_TIME_PERIOD')
				)
			),
			new Entity\FloatField(
				'CARGO_WEIGHT',
				array(
					'required' => true,
					'default_value' => 0,
					'title' => Loc::getMessage('IPOLH_DPD_TABLE_ORDER_CARGO_WEIGHT')
				)
			),
			new Entity\FloatField(
				'DIMENSION_WIDTH',
				array(
					'default_value' => 0,
					'title' => Loc::getMessage('IPOLH_DPD_TABLE_ORDER_DIMENSION_WIDTH')
				)
			),
			new Entity\FloatField(
				'DIMENSION_HEIGHT',
				array(
					'default_value' => 0,
					'title' => Loc::getMessage('IPOLH_DPD_TABLE_ORDER_DIMENSION_WIDTH')
				)
			),
			new Entity\FloatField(
				'DIMENSION_LENGTH',
				array(
					'default_value' => 0,
					'title' => Loc::getMessage('IPOLH_DPD_TABLE_ORDER_DIMENSION_WIDTH')
				)
			),
			new Entity\FloatField(
				'CARGO_VOLUME',
				array(
					'default_value' => 0,
					'title' => Loc::getMessage('IPOLH_DPD_TABLE_ORDER_CARGO_VOLUME')
				)
			),
			new Entity\FloatField(
				'CARGO_NUM_PACK',
				array(
					'required' => !(isset($_REQUEST['IPOLH_DPD_ORDER']) && $_REQUEST['IPOLH_DPD_ORDER'] == 'Y'),
					'title' => Loc::getMessage('IPOLH_DPD_TABLE_ORDER_CARGO_NUM_PACK')					
				)
			),
			new Entity\StringField(
				'CARGO_CATEGORY',
				array(
					'required' => !(isset($_REQUEST['IPOLH_DPD_ORDER']) && $_REQUEST['IPOLH_DPD_ORDER'] == 'Y'),
					'validation' => array(__CLASS__, 'validateCargoCategory'),
					'title' => Loc::getMessage('IPOLH_DPD_TABLE_ORDER_CARGO_CATEGORY')
				)
			),
			new Entity\StringField(
				'SENDER_FIO',
				array(
					'required' => !(isset($_REQUEST['IPOLH_DPD_ORDER']) && $_REQUEST['IPOLH_DPD_ORDER'] == 'Y'),
					'validation' => array(__CLASS__, 'validateReceiverFio'),
					'title' => Loc::getMessage('IPOLH_DPD_TABLE_ORDER_RECEIVER_FIO')
				)
			),			
			new Entity\StringField(
				'SENDER_NAME',
				array(
					'required' => !(isset($_REQUEST['IPOLH_DPD_ORDER']) && $_REQUEST['IPOLH_DPD_ORDER'] == 'Y'),
					'validation' => array(__CLASS__, 'validateReceiverName'),
					'title' => Loc::getMessage('IPOLH_DPD_TABLE_ORDER_RECEIVER_NAME')
				)
			),
			new Entity\StringField(
				'SENDER_PHONE',
				array(
					'validation' => array(__CLASS__, 'validateReceiverPhone'),
					'title' => Loc::getMessage('IPOLH_DPD_TABLE_ORDER_RECEIVER_PHONE')
				)
			),
			new Entity\StringField(
				'SENDER_LOCATION',
				array(
					'required' => !(isset($_REQUEST['IPOLH_DPD_ORDER']) && $_REQUEST['IPOLH_DPD_ORDER'] == 'Y'),
					'validation' => array(__CLASS__, 'validateSenderLocation'),
					'title' => Loc::getMessage('IPOLH_DPD_TABLE_ORDER_SENDER_LOCATION'),
				)
			),
			new Entity\StringField(
				'SENDER_STREET',
				array(
					'validation' => array(__CLASS__, 'validateReceiverStreet'),
					'title' => Loc::getMessage('IPOLH_DPD_TABLE_ORDER_RECEIVER_STREET')
				)
			),
			new Entity\StringField(
				'SENDER_STREETABBR',
				array(
					'validation' => array(__CLASS__, 'validateReceiverStreetAbbr'),
					'title' => Loc::getMessage('IPOLH_DPD_TABLE_ORDER_RECEIVER_STREETABBR')
				)
			),
			new Entity\StringField(
				'SENDER_HOUSE',
				array(
					'validation' => array(__CLASS__, 'validateReceiverHouse'),
					'title' => Loc::getMessage('IPOLH_DPD_TABLE_ORDER_RECEIVER_HOUSE')
				)
			),
			new Entity\StringField(
				'SENDER_KORPUS',
				array(
					'validation' => array(__CLASS__, 'validateReceiverKorpus'),
					'title' => Loc::getMessage('IPOLH_DPD_TABLE_ORDER_RECEIVER_KORPUS')
				)
			),
			new Entity\StringField(
				'SENDER_STR',
				array(
					'validation' => array(__CLASS__, 'validateReceiverStr'),
					'title' => Loc::getMessage('IPOLH_DPD_TABLE_ORDER_RECEIVER_STR')
				)
			),
			new Entity\StringField(
				'SENDER_VLAD',
				array(
					'validation' => array(__CLASS__, 'validateReceiverVlad'),
					'title' => Loc::getMessage('IPOLH_DPD_TABLE_ORDER_RECEIVER_VLAD')
				)
			),
			new Entity\StringField(
				'SENDER_OFFICE',
				array(
					'validation' => array(__CLASS__, 'validateReceiverOffice'),
					'title' => Loc::getMessage('IPOLH_DPD_TABLE_ORDER_RECEIVER_OFFICE')
				)
			),
			new Entity\StringField(
				'SENDER_FLAT',
				array(
					'validation' => array(__CLASS__, 'validateReceiverFlat'),
					'title' => Loc::getMessage('IPOLH_DPD_TABLE_ORDER_RECEIVER_FLAT')
				)
			),
			new Entity\StringField(
				'SENDER_TERMINAL_CODE',
				array(
					'validation' => array(__CLASS__, 'validateTerminalCode'),
					'title' => Loc::getMessage('IPOLH_DPD_TABLE_ORDER_TERMINAL_CODE')
				)
			),
			new Entity\StringField(
				'RECEIVER_FIO',
				array(
					'required' => !(isset($_REQUEST['IPOLH_DPD_ORDER']) && $_REQUEST['IPOLH_DPD_ORDER'] == 'Y'),
					'validation' => array(__CLASS__, 'validateReceiverFio'),
					'title' => Loc::getMessage('IPOLH_DPD_TABLE_ORDER_RECEIVER_FIO')
				)
			),			
			new Entity\StringField(
				'RECEIVER_NAME',
				array(
					'required' => !(isset($_REQUEST['IPOLH_DPD_ORDER']) && $_REQUEST['IPOLH_DPD_ORDER'] == 'Y'),
					'validation' => array(__CLASS__, 'validateReceiverName'),
					'title' => Loc::getMessage('IPOLH_DPD_TABLE_ORDER_RECEIVER_NAME')
				)
			),
			new Entity\StringField(
				'RECEIVER_PHONE',
				array(
					'validation' => array(__CLASS__, 'validateReceiverPhone'),
					'title' => Loc::getMessage('IPOLH_DPD_TABLE_ORDER_RECEIVER_PHONE')
				)
			),
			new Entity\StringField(
				'RECEIVER_LOCATION',
				array(
					'required' => !(isset($_REQUEST['IPOLH_DPD_ORDER']) && $_REQUEST['IPOLH_DPD_ORDER'] == 'Y'),
					'validation' => array(__CLASS__, 'validateReceiverLocation'),
					'title' => Loc::getMessage('IPOLH_DPD_TABLE_ORDER_RECEIVER_LOCATION'),
				)
			),
			new Entity\StringField(
				'RECEIVER_STREET',
				array(
					'validation' => array(__CLASS__, 'validateReceiverStreet'),
					'title' => Loc::getMessage('IPOLH_DPD_TABLE_ORDER_RECEIVER_STREET')
				)
			),
			new Entity\StringField(
				'RECEIVER_STREETABBR',
				array(
					'validation' => array(__CLASS__, 'validateReceiverStreetAbbr'),
					'title' => Loc::getMessage('IPOLH_DPD_TABLE_ORDER_RECEIVER_STREETABBR')
				)
			),
			new Entity\StringField(
				'RECEIVER_HOUSE',
				array(
					'validation' => array(__CLASS__, 'validateReceiverHouse'),
					'title' => Loc::getMessage('IPOLH_DPD_TABLE_ORDER_RECEIVER_HOUSE')
				)
			),
			new Entity\StringField(
				'RECEIVER_KORPUS',
				array(
					'validation' => array(__CLASS__, 'validateReceiverKorpus'),
					'title' => Loc::getMessage('IPOLH_DPD_TABLE_ORDER_RECEIVER_KORPUS')
				)
			),
			new Entity\StringField(
				'RECEIVER_STR',
				array(
					'validation' => array(__CLASS__, 'validateReceiverStr'),
					'title' => Loc::getMessage('IPOLH_DPD_TABLE_ORDER_RECEIVER_STR')
				)
			),
			new Entity\StringField(
				'RECEIVER_VLAD',
				array(
					'validation' => array(__CLASS__, 'validateReceiverVlad'),
					'title' => Loc::getMessage('IPOLH_DPD_TABLE_ORDER_RECEIVER_VLAD')
				)
			),
			new Entity\StringField(
				'RECEIVER_OFFICE',
				array(
					'validation' => array(__CLASS__, 'validateReceiverOffice'),
					'title' => Loc::getMessage('IPOLH_DPD_TABLE_ORDER_RECEIVER_OFFICE')
				)
			),
			new Entity\StringField(
				'RECEIVER_FLAT',
				array(
					'validation' => array(__CLASS__, 'validateReceiverFlat'),
					'title' => Loc::getMessage('IPOLH_DPD_TABLE_ORDER_RECEIVER_FLAT')
				)
			),
			new Entity\StringField(
				'RECEIVER_TERMINAL_CODE',
				array(
					'validation' => array(__CLASS__, 'validateTerminalCode'),
					'title' => Loc::getMessage('IPOLH_DPD_TABLE_ORDER_TERMINAL_CODE')
				)
			),
			new Entity\StringField(
				'RECEIVER_COMMENT',
				array(
					'title' => Loc::getMessage('IPOLH_DPD_TABLE_ORDER_COMMENT')
				)
			),
			new Entity\FloatField(
				'PRICE',
				array(
					'title' => Loc::getMessage('IPOLH_DPD_TABLE_ORDER_PRICE')					
				)
			),
			new Entity\FloatField(
				'PRICE_DELIVERY',
				array(
					'title' => Loc::getMessage('IPOLH_DPD_TABLE_ORDER_PRICE_DELIVERY')					
				)
			),					
			new Entity\FloatField(
				'CARGO_VALUE',
				array(
					'title' => Loc::getMessage('IPOLH_DPD_TABLE_ORDER_CARGO_VALUE')					
				)
			),
			new Entity\BooleanField(
				'NPP',
				array(
					'values' => array('N', 'Y'),
					'default_value' => 'N',
					'title' => Loc::getMessage('IPOLH_DPD_TABLE_ORDER_NPP')					
				)
			),
			new Entity\FloatField(
				'SUM_NPP',
				array(
					'title' => Loc::getMessage('IPOLH_DPD_TABLE_ORDER_SUM_NPP')					
				)
			),
			new Entity\BooleanField(
				'CARGO_REGISTERED',
				array(
					'values' => array('N', 'Y'),
					'default_value' => 'N',
					'title' => Loc::getMessage('IPOLH_DPD_TABLE_ORDER_NPP')					
				)
			),			
			new Entity\StringField(
				'SMS',
				array(
					'validation' => array(__CLASS__, 'validateReceiverSms'),
					'title' => Loc::getMessage('IPOLH_DPD_TABLE_ORDER_SMS')
				)
			),
			new Entity\StringField(
				'EML',
				array(
					'validation' => array(__CLASS__, 'validateReceiverEml'),
					'title' => Loc::getMessage('IPOLH_DPD_TABLE_ORDER_EML')
				)
			),
			new Entity\StringField(
				'ESD',
				array(
					'validation' => array(__CLASS__, 'validateReceiverEsd'),
					'title' => Loc::getMessage('IPOLH_DPD_TABLE_ORDER_ESD')
				)
			),
			new Entity\StringField(
				'ESZ',
				array(
					'validation' => array(__CLASS__, 'validateReceiverEsz'),
					'title' => Loc::getMessage('IPOLH_DPD_TABLE_ORDER_ESZ')
				)
			),			
			new Entity\StringField(
				'OGD',
				array(
					'validation' => array(__CLASS__, 'validateReceiverOgd'),
					'title' => Loc::getMessage('IPOLH_DPD_TABLE_ORDER_OGD')
				)
			),
			new Entity\BooleanField(
				'DVD',
				array(
					'values' => array('N', 'Y'),
					'default_value' => 'N',
					'title' => Loc::getMessage('IPOLH_DPD_TABLE_ORDER_DVD')
				)
			),
			new Entity\BooleanField(
				'VDO',
				array(
					'values' => array('N', 'Y'),
					'default_value' => 'N',
					'title' => Loc::getMessage('IPOLH_DPD_TABLE_ORDER_VDO')
				)
			),
			new Entity\StringField(
				'POD',
				array(
					'validation' => array(__CLASS__, 'validateReceiverPOD'),
					'title' => Loc::getMessage('IPOLH_DPD_TABLE_ORDER_POD')
				)
			),
			new Entity\BooleanField(
				'PRD',
				array(
					'values' => array('N', 'Y'),
					'default_value' => 'N',
					'title' => Loc::getMessage('IPOLH_DPD_TABLE_ORDER_PRD')
				)
			),
			new Entity\BooleanField(
				'TRM',
				array(
					'values' => array('N', 'Y'),
					'default_value' => 'N',
					'title' => Loc::getMessage('IPOLH_DPD_TABLE_ORDER_TRM')
				)
			),
			new Entity\StringField(
				'LABEL_FILE',
				array(
					'validation' => array(__CLASS__, 'validateLabelFile'),
					'title' => Loc::getMessage('IPOLH_DPD_TABLE_LABEL_FILE')
				)
			),
			new Entity\StringField(
				'INVOICE_FILE',
				array(
					'validation' => array(__CLASS__, 'validateInvoiceFile'),
					'title' => Loc::getMessage('IPOLH_DPD_TABLE_INVOICE_FILE')
				)
			),
			new Entity\StringField(
				'PAYMENT_TYPE',
				array(
					'title' => Loc::getMessage('IPOLH_DPD_TABLE_PAYMENT_TYPE')
				)
			),
        );
    }
	
	public static function validateOrderNum()
	{
		if (isset($_REQUEST['IPOLH_DPD_ORDER']) && $_REQUEST['IPOLH_DPD_ORDER'] == 'Y') {
			return array();
		}

		return array(
			new Main\Entity\Validator\Length(null, 15),
		);
	}
	
	public static function validateOrderDate()
	{
		if (isset($_REQUEST['IPOLH_DPD_ORDER']) && $_REQUEST['IPOLH_DPD_ORDER'] == 'Y') {
			return array();
		}

		return array(
			new Main\Entity\Validator\Length(null, 20),
		);
	}

	public static function validateOrderDateCreate()
	{
		if (isset($_REQUEST['IPOLH_DPD_ORDER']) && $_REQUEST['IPOLH_DPD_ORDER'] == 'Y') {
			return array();
		}

		return array(
			new Main\Entity\Validator\Length(null, 20),
		);
	}
	
	public static function validateOrderDateCancel()
	{
		if (isset($_REQUEST['IPOLH_DPD_ORDER']) && $_REQUEST['IPOLH_DPD_ORDER'] == 'Y') {
			return array();
		}

		return array(
			new Main\Entity\Validator\Length(null, 20),
		);
	}

	public static function validateOrderDateStatus()
	{
		if (isset($_REQUEST['IPOLH_DPD_ORDER']) && $_REQUEST['IPOLH_DPD_ORDER'] == 'Y') {
			return array();
		}

		return array(
			new Main\Entity\Validator\Length(null, 20),
		);
	}
	
	public static function validateServiceCode()
	{
		if (isset($_REQUEST['IPOLH_DPD_ORDER']) && $_REQUEST['IPOLH_DPD_ORDER'] == 'Y') {
			return array();
		}

		return array(
			new Main\Entity\Validator\Length(null, 3),
		);
	}
	
	public static function validateServiceVariant()
	{
		if (isset($_REQUEST['IPOLH_DPD_ORDER']) && $_REQUEST['IPOLH_DPD_ORDER'] == 'Y') {
			return array();
		}

		return array(
			new Main\Entity\Validator\Length(null, 2),
		);
	}
	
	public static function validateCargoCategory()
	{
		if (isset($_REQUEST['IPOLH_DPD_ORDER']) && $_REQUEST['IPOLH_DPD_ORDER'] == 'Y') {
			return array();
		}

		return array(
			new Main\Entity\Validator\Length(null, 255),
		);
	}

	public static function validatePickupDate()
	{
		if (isset($_REQUEST['IPOLH_DPD_ORDER']) && $_REQUEST['IPOLH_DPD_ORDER'] == 'Y') {
			return array();
		}

		return array(
			new Main\Entity\Validator\Length(null, 20),

			function($value, $primary, $row, $field) {
				if (!is_null($primary) && empty($value) && $row['ORDER_STATUS'] != 'Canceled') {
					return Loc::getMessage("IPOLH_DPD_TABLE_PICKUP_DATE_ERROR_EMPTY");
				}

				if ($value) {
					// if (isset($_REQUEST['IPOLH_DPD_ORDER']) && \MakeTimeStamp($value, "DD.MM.YYYY") < mktime(0, 0, 0)) {
					// 	return Loc::getMessage("IPOLH_DPD_TABLE_PICKUP_DATE_ERROR_LESS");
					// }
				}

				return true;
			}
		);
	}
	
	public static function validatePickupTimePeriod()
	{
		if (isset($_REQUEST['IPOLH_DPD_ORDER']) && $_REQUEST['IPOLH_DPD_ORDER'] == 'Y') {
			return array();
		}

		return array(
			new Main\Entity\Validator\Length(null, 5),
		);
	}
	
	public static function validateDeliveryTimePeriod()
	{
		if (isset($_REQUEST['IPOLH_DPD_ORDER']) && $_REQUEST['IPOLH_DPD_ORDER'] == 'Y') {
			return array();
		}

		return array(
			new Main\Entity\Validator\Length(null, 5),
		);
	}
	
	public static function validateTerminalCode()
	{
		if (isset($_REQUEST['IPOLH_DPD_ORDER']) && $_REQUEST['IPOLH_DPD_ORDER'] == 'Y') {
			return array();
		}

		return array(
			new Main\Entity\Validator\Length(null, 4),
		);
	}
	
	public static function validateReceiverFio()
	{
		if (isset($_REQUEST['IPOLH_DPD_ORDER']) && $_REQUEST['IPOLH_DPD_ORDER'] == 'Y') {
			return array();
		}

		return array(
			new Main\Entity\Validator\Length(null, 255),
		);
	}
	
	public static function validateReceiverName()
	{
		if (isset($_REQUEST['IPOLH_DPD_ORDER']) && $_REQUEST['IPOLH_DPD_ORDER'] == 'Y') {
			return array();
		}

		return array(
			new Main\Entity\Validator\Length(null, 255),
		);
	}
	
	public static function validateReceiverPhone()
	{
		if (isset($_REQUEST['IPOLH_DPD_ORDER']) && $_REQUEST['IPOLH_DPD_ORDER'] == 'Y') {
			return array();
		}
		
		return array(
			new Main\Entity\Validator\Length(null, 20),
		);
	}

	public static function validateSenderLocation()
	{
		if (isset($_REQUEST['IPOLH_DPD_ORDER']) && $_REQUEST['IPOLH_DPD_ORDER'] == 'Y') {
			return array();
		}

		return array();
	}

	public static function validateReceiverLocation()
	{
		return array();
	}
	
	public static function validateReceiverStreet()
	{
		if (isset($_REQUEST['IPOLH_DPD_ORDER']) && $_REQUEST['IPOLH_DPD_ORDER'] == 'Y') {
			return array();
		}

		return array(
			new Main\Entity\Validator\Length(null, 50),
		);
	}
	
	public static function validateReceiverStreetAbbr()
	{
		if (isset($_REQUEST['IPOLH_DPD_ORDER']) && $_REQUEST['IPOLH_DPD_ORDER'] == 'Y') {
			return array();
		}

		return array(
			new Main\Entity\Validator\Length(null, 10),
		);
	}
	
	public static function validateReceiverHouse()
	{
		if (isset($_REQUEST['IPOLH_DPD_ORDER']) && $_REQUEST['IPOLH_DPD_ORDER'] == 'Y') {
			return array();
		}

		return array(
			new Main\Entity\Validator\Length(null, 10),
		);
	}
	
	public static function validateReceiverKorpus()
	{
		if (isset($_REQUEST['IPOLH_DPD_ORDER']) && $_REQUEST['IPOLH_DPD_ORDER'] == 'Y') {
			return array();
		}

		return array(
			new Main\Entity\Validator\Length(null, 10),
		);
	}
	
	public static function validateReceiverStr()
	{
		if (isset($_REQUEST['IPOLH_DPD_ORDER']) && $_REQUEST['IPOLH_DPD_ORDER'] == 'Y') {
			return array();
		}

		return array(
			new Main\Entity\Validator\Length(null, 10),
		);
	}
	
	public static function validateReceiverVlad()
	{
		if (isset($_REQUEST['IPOLH_DPD_ORDER']) && $_REQUEST['IPOLH_DPD_ORDER'] == 'Y') {
			return array();
		}

		return array(
			new Main\Entity\Validator\Length(null, 10),
		);
	}
	
	public static function validateReceiverOffice()
	{
		if (isset($_REQUEST['IPOLH_DPD_ORDER']) && $_REQUEST['IPOLH_DPD_ORDER'] == 'Y') {
			return array();
		}

		return array(
			new Main\Entity\Validator\Length(null, 10),
		);
	}
	
	public static function validateReceiverFlat()
	{
		if (isset($_REQUEST['IPOLH_DPD_ORDER']) && $_REQUEST['IPOLH_DPD_ORDER'] == 'Y') {
			return array();
		}

		return array(
			new Main\Entity\Validator\Length(null, 10),
		);
	}
	
	public static function validateReceiverSms()
	{
		if (isset($_REQUEST['IPOLH_DPD_ORDER']) && $_REQUEST['IPOLH_DPD_ORDER'] == 'Y') {
			return array();
		}

		return array(
			new Main\Entity\Validator\Length(null, 25),
		);
	}
	
	public static function validateReceiverEml()
	{
		if (isset($_REQUEST['IPOLH_DPD_ORDER']) && $_REQUEST['IPOLH_DPD_ORDER'] == 'Y') {
			return array();
		}

		return array(
			new Main\Entity\Validator\Length(null, 50),
		);
	}
	
	public static function validateReceiverEsd()
	{
		if (isset($_REQUEST['IPOLH_DPD_ORDER']) && $_REQUEST['IPOLH_DPD_ORDER'] == 'Y') {
			return array();
		}

		return array(
			new Main\Entity\Validator\Length(null, 50),
		);
	}
	
	public static function validateReceiverEsz()
	{
		if (isset($_REQUEST['IPOLH_DPD_ORDER']) && $_REQUEST['IPOLH_DPD_ORDER'] == 'Y') {
			return array();
		}

		return array(
			new Main\Entity\Validator\Length(null, 50),
		);
	}
	
	public static function validateReceiverOgd()
	{
		if (isset($_REQUEST['IPOLH_DPD_ORDER']) && $_REQUEST['IPOLH_DPD_ORDER'] == 'Y') {
			return array();
		}

		return array(
			new Main\Entity\Validator\Length(null, 4),
		);
	}
	
	public static function validateReceiverDeliveryTimePeriod()
	{
		if (isset($_REQUEST['IPOLH_DPD_ORDER']) && $_REQUEST['IPOLH_DPD_ORDER'] == 'Y') {
			return array();
		}

		return array(
			new Main\Entity\Validator\Length(null, 25),
		);
	}	
		
	public static function validateReceiverPOD()
	{
		if (isset($_REQUEST['IPOLH_DPD_ORDER']) && $_REQUEST['IPOLH_DPD_ORDER'] == 'Y') {
			return array();
		}

		return array(
			new Main\Entity\Validator\Length(null, 50),
		);
	}
	
	public static function validateLabelFile()
	{
		if (isset($_REQUEST['IPOLH_DPD_ORDER']) && $_REQUEST['IPOLH_DPD_ORDER'] == 'Y') {
			return array();
		}

		return array(
			new Main\Entity\Validator\Length(null, 255),
		);
	}
	
	public static function validateInvoiceFile()
	{
		if (isset($_REQUEST['IPOLH_DPD_ORDER']) && $_REQUEST['IPOLH_DPD_ORDER'] == 'Y') {
			return array();
		}
		
		return array(
			new Main\Entity\Validator\Length(null, 255),
		);
	}

	/**
	 * Возвращает одну запись по ID
	 * 
	 * @param  $orderId
	 * @return \Ipolh\DPD\DB\OrderTableItem
	 */
	public static function findByOrder($orderId, $autoCreate = false)
	{
		$item = self::getList(array(
			'filter' => array('=ORDER_ID' => $orderId)
		))->Fetch();

		if ($item) {
			return new Model($item);
		} elseif (!$autoCreate) {
			return false;
		}
		
		$item = new Model();
		$item->fillFromConfig();
		$item->fillFromOrder($orderId);

		return $item;
	}
}
?>