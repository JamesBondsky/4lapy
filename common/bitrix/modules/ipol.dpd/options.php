<?php
use \Bitrix\Main\Loader;
use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\Config\Option;
use \Bitrix\Main\SystemException;

use \Ipolh\DPD\API\User as API;
use \Ipolh\DPD\Shipment;

if (!$USER->IsAdmin()
	|| !Loader::includeModule('ipol.dpd')
) {
	return false;
}

Loc::loadMessages(__FILE__);

$arSites = array();
$arSitesFull = array();
$arPersoneTypes = array();

$dbSites = CSite::GetList($by = "active", $order = "desc", array("ACTIVE" => "Y"));
while($site = $dbSites->Fetch())
{	
	$arSites[] = $site["ID"];
	$arSitesFull[] = $site;

	$resPersoneTypes = CSalePersonType::GetList(array("SORT" => "ASC"), array("LID" => $site['ID'], 'ACTIVE' => 'Y'));
	while($arPersoneType = $resPersoneTypes->Fetch()) {
		$arPersoneType['SITE_ID'] = $site['ID'];
		$arPersoneTypes[] = $arPersoneType;
	}
}


$rsOrderStatuses = \CSaleStatus::GetList(array('SORT' => 'ASC'), array('LID'  => LANGUAGE_ID));
$arOrderStatuses = array();

while($arOrderStatus = $rsOrderStatuses->Fetch()) {
	$arOrderStatus['NAME'] = $arOrderStatus['NAME'] .' ['. $arOrderStatus['ID'] .']';
	$arOrderStatuses[] = $arOrderStatus;
}

$rsCurrencies = CCurrency::GetList($by = '', $order = '');
$arCurrencies = array();
while ($arCurrency = $rsCurrencies->GetNext()) {
	$arCurrencies[$arCurrency['CURRENCY']] = $arCurrency['FULL_NAME'];
}

$aTabs = array(
	array(
		"DIV" => "IPOLH_DPD_OPTIONS_TAB_COMMON",
		"TAB" => Loc::getMessage("IPOLH_DPD_OPTIONS_TAB_COMMON"),
		"ICON" => "support_settings",
		"TITLE" => Loc::getMessage("IPOLH_DPD_OPTIONS_TAB_COMMON_TITLE"),
		"HELP" => Loc::getMessage("IPOLH_DPD_OPTIONS_TAB_COMMON_HELP"),
		"OPTIONS" => array(),
		"CONTROLS" => array(
			"API_TABS" => array(
				"TYPE"  => "TABS",
				"ITEMS" => array(
					array(
						"DIV"      => "IPOLH_DPD_OPTIONS_TAB_COMMON_TAB_RU",
						"TAB"      => Loc::getMessage('IPOLH_DPD_OPTIONS_TAB_COMMON_TAB_RU'),
						"ICON"     => "support_settings",
						"TITLE"    => Loc::getMessage('IPOLH_DPD_OPTIONS_TAB_COMMON_TAB_RU_TITLE'),
						"HELP"     => Loc::getMessage("IPOLH_DPD_OPTIONS_TAB_COMMON_TAB_RU_HELP"),
						"OPTIONS"  => array(),
						"CONTROLS" => array(
							"KLIENT_NUMBER" => array(
								"TYPE" => "STRING",
								"TITLE" => Loc::getMessage("IPOLH_DPD_OPTIONS_CLIENT_NUMBER"),
								"HELP" => Loc::getMessage("IPOLH_DPD_OPTIONS_CLIENT_NUMBER_HELP"),
							),

							"KLIENT_KEY" => array(
								"TYPE" => "STRING",
								"TITLE" => Loc::getMessage("IPOLH_DPD_OPTIONS_CLIENT_KEY"),
								"HELP" => Loc::getMessage("IPOLH_DPD_OPTIONS_CLIENT_KEY_HELP"),
							),

							"KLIENT_CURRENCY" => array(
								"TITLE"   => Loc::getMessage("IPOLH_DPD_OPTIONS_KLIENT_CURRENCY"),
								"COMMENT" => Loc::getMessage("IPOLH_DPD_OPTIONS_KLIENT_CURRENCY_RU_HELP"),
								"TYPE"    => "SELECT",
								"ITEMS"   => $arCurrencies,
								"DEFAULT" => "RUB",
							),
						),
					),

					array(
						"DIV"      => "IPOLH_DPD_OPTIONS_TAB_COMMON_TAB_KZ",
						"TAB"      => Loc::getMessage('IPOLH_DPD_OPTIONS_TAB_COMMON_TAB_KZ'),
						"ICON"     => "support_settings",
						"TITLE"    => Loc::getMessage('IPOLH_DPD_OPTIONS_TAB_COMMON_TAB_KZ_TITLE'),
						"HELP"     => Loc::getMessage("IPOLH_DPD_OPTIONS_TAB_COMMON_TAB_KZ_HELP"),
						"OPTIONS"  => array(),
						"CONTROLS" => array(
							"KLIENT_NUMBER_KZ" => array(
								"TYPE" => "STRING",
								"TITLE" => Loc::getMessage("IPOLH_DPD_OPTIONS_CLIENT_NUMBER"),
								"HELP" => Loc::getMessage("IPOLH_DPD_OPTIONS_CLIENT_NUMBER_HELP"),
							),

							"KLIENT_KEY_KZ" => array(
								"TYPE" => "STRING",
								"TITLE" => Loc::getMessage("IPOLH_DPD_OPTIONS_CLIENT_KEY"),
								"HELP" => Loc::getMessage("IPOLH_DPD_OPTIONS_CLIENT_KEY_HELP"),
							),

							"KLIENT_CURRENCY_KZ" => array(
								"TITLE"   => Loc::getMessage("IPOLH_DPD_OPTIONS_KLIENT_CURRENCY"),
								"COMMENT" => Loc::getMessage("IPOLH_DPD_OPTIONS_KLIENT_CURRENCY_KZ_HELP"),
								"TYPE"    => "SELECT",
								"ITEMS"   => $arCurrencies,
								"DEFAULT" => "KZT",
							),
						),
					),

					array(
						"DIV"      => "IPOLH_DPD_OPTIONS_TAB_COMMON_TAB_BY",
						"TAB"      => Loc::getMessage('IPOLH_DPD_OPTIONS_TAB_COMMON_TAB_BY'),
						"ICON"     => "support_settings",
						"TITLE"    => Loc::getMessage('IPOLH_DPD_OPTIONS_TAB_COMMON_TAB_BY_TITLE'),
						"HELP"     => Loc::getMessage("IPOLH_DPD_OPTIONS_TAB_COMMON_TAB_BY_HELP"),
						"OPTIONS"  => array(),
						"CONTROLS" => array(
							"KLIENT_NUMBER_BY" => array(
								"TYPE" => "STRING",
								"TITLE" => Loc::getMessage("IPOLH_DPD_OPTIONS_CLIENT_NUMBER"),
								"HELP" => Loc::getMessage("IPOLH_DPD_OPTIONS_CLIENT_NUMBER_HELP"),
							),

							"KLIENT_KEY_BY" => array(
								"TYPE" => "STRING",
								"TITLE" => Loc::getMessage("IPOLH_DPD_OPTIONS_CLIENT_KEY"),
								"HELP" => Loc::getMessage("IPOLH_DPD_OPTIONS_CLIENT_KEY_HELP"),
							),

							"KLIENT_CURRENCY_BY" => array(
								"TITLE"   => Loc::getMessage("IPOLH_DPD_OPTIONS_KLIENT_CURRENCY"),
								"COMMENT" => Loc::getMessage("IPOLH_DPD_OPTIONS_KLIENT_CURRENCY_BY_HELP"),
								"TYPE"    => "SELECT",
								"ITEMS"   => $arCurrencies,
								"DEFAULT" => "BYR",
							),
						),
					),
				),
			),

			"API_DEF_COUNTRY" => array(
				"TITLE" => Loc::getMessage("IPOLH_DPD_OPTIONS_API_DEF_COUNTRY"),
				"HELP"  => Loc::getMessage("IPOLH_DPD_OPTIONS_API_DEF_COUNTRY_HELP"),
				"TYPE"  => "SELECT",
				"ITEMS" => array(
					""   => Loc::getMessage('IPOLH_DPD_OPTIONS_TAB_COMMON_TAB_RU'),
					"KZ" => Loc::getMessage('IPOLH_DPD_OPTIONS_TAB_COMMON_TAB_KZ'),
					"BY" => Loc::getMessage('IPOLH_DPD_OPTIONS_TAB_COMMON_TAB_BY'),
				),
			),

			"IS_TEST" => array(
				"TYPE" => "CHECKBOX",
				"TITLE" => Loc::getMessage("IPOLH_DPD_OPTIONS_IS_TEST"),
				"HELP" => Loc::getMessage("IPOLH_DPD_OPTIONS_IS_TEST_HELP"),
			),

			"SHOW_ADMIN_BUTTON" => array(
				"TITLE" => Loc::getMessage("IPOLH_DPD_OPTIONS_SHOW_ADMIN_BUTTON"),
				"HELP"  => '',
				"TYPE"  => "SELECT",
				"ITEMS" => array(
					""       => Loc::getMessage('IPOLH_DPD_OPTIONS_SHOW_ADMIN_BUTTON_ONLY_DPD'),
					"ALWAYS" => Loc::getMessage('IPOLH_DPD_OPTIONS_SHOW_ADMIN_BUTTON_ALWAYS'),
				),
				"DEFAULT" => "",
			),
		),
	),

	array(
		"DIV" => "IPOLH_DPD_OPTIONS_TAB_LOCATION",
		"TAB" => Loc::getMessage("IPOLH_DPD_OPTIONS_TAB_LOCATION"),
		"ICON" => "support_settings",
		"TITLE" => Loc::getMessage("IPOLH_DPD_OPTIONS_TAB_LOCATION_TITLE"),
		"HELP" => Loc::getMessage("IPOLH_DPD_OPTIONS_TAB_LOCATION_HELP"),
		"OPTIONS" => array(),
		"CONTROLS" => array(
			"LOCATION_COUNTRY" => array(
				"TITLE" => Loc::getMessage("IPOLH_DPD_OPTIONS_LOCATION_COUNTRY"),
				"TYPE" => "SELECT",
				"ITEMS" => $arLocationTypes = array_flip(\CSaleLocation::getTypes()),
				"DEFAULT" => "1",
			),
			
			"LOCATION_REGION" => array(
				"TITLE" => Loc::getMessage("IPOLH_DPD_OPTIONS_LOCATION_REGION"),
				"TYPE" => "SELECT",
				"ITEMS" => $arLocationTypes = array_flip(\CSaleLocation::getTypes()),
				"DEFAULT" => "3",
			),

			"LOCATION_CITY" => array(
				"TITLE" => Loc::getMessage("IPOLH_DPD_OPTIONS_LOCATION_CITY"),
				"TYPE" => "SELECT",
				"ITEMS" => $arLocationTypes,
				"DEFAULT" => "5",
			),

			"LOCATION_VILLAGE" => array(
				"TITLE" => Loc::getMessage("IPOLH_DPD_OPTIONS_LOCATION_VILLAGE"),
				"TYPE" => "SELECT",
				"ITEMS" => $arLocationTypes,
				"DEFAULT" => "6",
			),
		),
	),

	array(
		'DIV'      => 'IPOLH_DPD_OPTIONS_TAB_DIMENSIONS',
		'TAB'      => Loc::getMessage("IPOLH_DPD_OPTIONS_TAB_DIMENSIONS"),
		'ICON'     => '',
		'TITLE'    => Loc::getMessage("IPOLH_DPD_OPTIONS_TAB_DIMENSIONS_TITLE"),
		'HELP'     => Loc::getMessage("IPOLH_DPD_OPTIONS_TAB_DIMENSIONS_HELP"),
		'OPTIONS'  => array(),
		'CONTROLS' => array(
			'WEIGHT' => array(
				'TYPE' => 'TEXT',
				'TITLE' => Loc::getMessage("IPOLH_DPD_OPTIONS_WEIGHT"),
				'DEFAULT' =>  '1000',
				"VALIDATORS" => array(
					"required" => Loc::getMessage("IPOLH_DPD_OPTIONS_WEIGHT_REQUIRED"),
				),
			),

			'LENGTH' => array(
				'TYPE' => 'TEXT',
				'TITLE' => Loc::getMessage("IPOLH_DPD_OPTIONS_LENGTH"),
				'DEFAULT' =>  '200',
				"VALIDATORS" => array(
					"required" => Loc::getMessage("IPOLH_DPD_OPTIONS_LENGTH_REQUIRED"),
				),
			),

			'WIDTH' => array(
				'TYPE' => 'TEXT',
				'TITLE' => Loc::getMessage("IPOLH_DPD_OPTIONS_WIDTH"),
				'DEFAULT' =>  '100',
				"VALIDATORS" => array(
					"required" => Loc::getMessage("IPOLH_DPD_OPTIONS_WIDTH_REQUIRED"),
				),
			),

			'HEIGHT' => array(
				'TYPE' => 'TEXT',
				'TITLE' => Loc::getMessage("IPOLH_DPD_OPTIONS_HEIGHT"),
				'DEFAULT' =>  '200',
				"VALIDATORS" => array(
					"required" => Loc::getMessage("IPOLH_DPD_OPTIONS_HEIGHT_REQUIRED"),
				),
			),
		),
	),

	array(
		"DIV" => "IPOLH_DPD_OPTIONS_TAB_CALCULATE",
		"TAB" => Loc::getMessage("IPOLH_DPD_OPTIONS_TAB_CALCULATE"),
		"ICON" => "support_settings",
		"TITLE" => Loc::getMessage("IPOLH_DPD_OPTIONS_TAB_CALCULATE_TITLE"),
		"OPTIONS" => array(),
		"CONTROLS" => array(
			"TARIFF_OFF" => array(
				"TITLE" => Loc::getMessage("IPOLH_DPD_OPTIONS_EXCLUDE_TARIFF"),
				"TYPE" => "SELECT",
				"ITEMS" => \Ipolh\DPD\Calculator::TariffList(),
				"MULTIPLE" => true,
				"NULL" => "",
			),

			"DEFAULT_TARIFF_CODE" => array(
				"TITLE" => Loc::getMessage("IPOLH_DPD_OPTIONS_DEFAULT_TARIFF_CODE"),
				"TYPE" => "SELECT",
				"ITEMS" => \Ipolh\DPD\Calculator::TariffList(),
				"NULL" => "",
				"HELP" => Loc::getMessage("IPOLH_DPD_OPTIONS_DEFAULT_TARIFF_CODE_HELP"),
				"DEFAULT" => "PCL",
			),

			"DEFAULT_TARIFF_THRESHOLD" => array(
				"TITLE" => Loc::getMessage("IPOLH_DPD_OPTIONS_DEFAULT_TARIFF_THRESHOLD"),
				"TYPE" => "STRING",
				"DEFAULT" => "500",
			),

			"DECLARED_VALUE" => array(
				"TITLE"   => Loc::getMessage("IPOLH_DPD_OPTIONS_DECLARED_VALUE"),
				"TYPE"    => "CHECKBOX",
				"DEFAULT" => 1,
				"HELP"    => Loc::getMessage("IPOLH_DPD_OPTIONS_DECLARED_VALUE_HELP"),
			),

			"COMMISSIONS_HEADER" => array(
				"TITLE"   => Loc::getMessage("IPOLH_DPD_OPTIONS_COMMISSION_HEADER"),
				"TYPE"    => "HEADER",
			),

			'COMMISSION_HELP' => array(
				'TYPE' => 'COMMENT',
				'HELP' => Loc::getMessage("IPOLH_DPD_OPTIONS_COMMISSION_HELP"),
			),

			"COMMISSIONS_TABS" => array(
				"TYPE"  => "TABS",
				"ITEMS" => function($formValues) use ($arPersoneTypes) {
					$ret = array();

					foreach ($arPersoneTypes as $arPersoneType) {
						$rsPayments = \CSalePaySystem::GetList(
							$arOrder = array('SORT' => 'ASC', 'NAME' => 'ASC'),
							$arFilter = array('ACTIVE' => 'Y', 'PERSON_TYPE_ID' => $arPersoneType['ID'])
						);

						$arPayments = array();
						while ($arPayment = $rsPayments->Fetch()) {
							$arPayments[$arPayment['ID']] = $arPayment['NAME'];
						}

						$disabled = !($formValues && $formValues['COMMISSION_NPP_CHECK_'. $arPersoneType['ID'] .'_'. $arPersoneType['SITE_ID']]);

						$ret[] = array(
							'DIV'      => sprintf('IPOLH_DPD_OPTIONS_CALCULATE_SUBTAB_COMMISSIONS_%s_%s', $arPersoneType['ID'], $arPersoneType['SITE_ID']),
							'TAB'      => sprintf('(%s) %s (%s)', $arPersoneType['ID'], $arPersoneType['NAME'], $arPersoneType['SITE_ID']),
							'CONTROLS' => array(
								'COMMISSION_NPP_CHECK_'. $arPersoneType['ID'] .'_'. $arPersoneType['SITE_ID'] => array(
									'TYPE'  => 'CHECKBOX',
									'TITLE' => Loc::getMessage("IPOLH_DPD_OPTIONS_COMMISSION_NPP_CHECK"),
									'ATTRS' => array(
										'onchange' => ""
											."BX('IPOLH_DPD_OPTIONS_COMMISSION_NPP_PERCENT_". $arPersoneType['ID'] .'_'. $arPersoneType['SITE_ID'] ."').disabled = !this.checked;"
											."BX('IPOLH_DPD_OPTIONS_COMMISSION_NPP_MINSUM_". $arPersoneType['ID'] .'_'. $arPersoneType['SITE_ID'] ."').disabled = !this.checked;"
									),
									'DEFAULT' => 1,
								),

								'COMMISSION_NPP_PERCENT_'. $arPersoneType['ID'] .'_'. $arPersoneType['SITE_ID'] => array(
									'TITLE'   => Loc::getMessage("IPOLH_DPD_OPTIONS_COMMISSION_NPP_PERSENT"),
									'TYPE'    => 'STRING',
									'DEFAULT' => 2,
									'ATTRS'   => array(
										'disabled' => $disabled,
									),
								),

								'COMMISSION_NPP_MINSUM_'. $arPersoneType['ID'] .'_'. $arPersoneType['SITE_ID'] => array(
									'TITLE'   => Loc::getMessage("IPOLH_DPD_OPTIONS_COMMISSION_NPP_MINSUMM"),
									'TYPE'    => 'STRING',
									'DEFAULT' => 0,
									'ATTRS'   => array(
										'disabled' => $disabled,
									),
								),

								'COMMISSION_NPP_PAYMENT_'. $arPersoneType['ID'] .'_'. $arPersoneType['SITE_ID'] => array(
									'TITLE'    => Loc::getMessage("IPOLH_DPD_OPTIONS_COMMISSION_NPP_PAYMENT"),
									'TYPE'     => 'SELECT',
									'ITEMS'    => $arPayments,
									'MULTIPLE' => true,
									'HELP'     => '',
									'NULL'     => '',
								),

								'COMMISSION_NPP_DEFAULT_'. $arPersoneType['ID'] .'_'. $arPersoneType['SITE_ID'] => array(
									'TITLE'    => Loc::getMessage("IPOLH_DPD_OPTIONS_COMMISSION_NPP_DEFAULT"),
									'TYPE'     => 'CHECKBOX',
									'HELP'     => Loc::getMessage("IPOLH_DPD_OPTIONS_COMMISSION_NPP_DEFAULT_HELP"),
									'DEFAULT'  => 'Y',
								),
							),
						);
					}

					return $ret;
				}
			),
		),
	),

	array(
		"DIV" => "IPOLH_DPD_OPTIONS_TAB_SENDER",
		"TAB" => Loc::getMessage("IPOLH_DPD_OPTIONS_TAB_SENDER"),
		"ICON" => "support_settings",
		"TITLE" => Loc::getMessage("IPOLH_DPD_OPTIONS_TAB_SENDER_TITLE"),
		"OPTIONS" => array(),
		'CONTROLS' => array(
			'SENDER_FIO' => array(
				'TYPE'  => 'STRING',
				'TITLE' => Loc::getMessage("IPOLH_DPD_OPTIONS_SENDER_FIO"),
				'HELP'  => Loc::getMessage("IPOLH_DPD_OPTIONS_SENDER_FIO_HELP"),
				"VALIDATORS" => array(
					"required" => Loc::getMessage("IPOLH_DPD_OPTIONS_SENDER_FIO_REQUIRED"),
				),
			),

			'SENDER_NAME' => array(
				'TYPE'  => 'STRING',
				'TITLE' => Loc::getMessage("IPOLH_DPD_OPTIONS_SENDER_NAME"),
				'HELP'  => Loc::getMessage("IPOLH_DPD_OPTIONS_SENDER_NAME_HELP"),
				"VALIDATORS" => array(
					"required" => Loc::getMessage("IPOLH_DPD_OPTIONS_SENDER_NAME_REQUIRED"),
				),
			),

			'SENDER_PHONE' => array(
				'TYPE'  => 'STRING',
				'TITLE' => Loc::getMessage("IPOLH_DPD_OPTIONS_SENDER_PHONE"),
				"VALIDATORS" => array(
					"required" => Loc::getMessage("IPOLH_DPD_OPTIONS_SENDER_PHONE_REQUIRED"),
				),
			),

			'SENDER_REGULAR_NUM' => array(
				'TYPE'  => 'STRING',
				'TITLE' => Loc::getMessage("IPOLG_DPD_OPTIONS_SENDER_REGULAR_NUM"),
			),

			'SENDER_SUBHEADER_ADDRESS' => array(
				'TYPE'  => 'HEADER',
				'TITLE' => Loc::getMessage("IPOLH_DPD_OPTIONS_SENDER_ADDRESS_SUBHEADER"),
			),

			'SENDER_LOCATION' => array(
				'TITLE'   => Loc::getMessage("IPOLH_DPD_OPTIONS_SENDER_LOCATION"),
				'TYPE'    => 'location',
				'DEFAULT' => \Ipolh\DPD\Utils::getSaleLocationId(),
			),

			// 'SENDER_LOCATION_TEXT' => array(
			// 	'TITLE' => Loc::getMessage("IPOLH_DPD_OPTIONS_SENDER_LOCATION"),
			// 	'TYPE'  => function() {
			// 		$locationId = \Ipolh\DPD\Utils::getSaleLocationId();
					
			// 		try {
			// 			$arLocation = \Ipolh\DPD\DB\Location\Table::getByLocationId($locationId);

			// 			return implode(', ', array_filter(array_unique(array(
			// 				'COUNTRY_NAME' => $arLocation['COUNTRY_NAME'],
			// 				'REGION_NAME'  => $arLocation['REGION_NAME'],
			// 				'CITY'         => $arLocation['CITY_NAME'],
			// 			))));
			// 		} catch (SystemException $e) {}
					
			// 		return $locationId;
			// 	},
			// 	'HELP'  => Loc::getMessage("IPOLH_DPD_OPTIONS_SENDER_LOCATION_HELP"),
			// ),

			'SENDER_TABS' => array(
				'TYPE'  => 'TABS',
				'ITEMS' => array(
					array(
						'DIV'      => "IPOLH_DPD_OPTIONS_SENDER_SUBTAB_COURIER",
						'TAB'      => Loc::getMessage("IPOLH_DPD_OPTIONS_SENDER_SUBTAB_COURIER"),
						'ICON'     => '',
						'TITLE'    => Loc::getMessage("IPOLH_DPD_OPTIONS_SENDER_SUBTAB_COURIER_TITLE"),
						'OPTIONS'  => array(),
						'CONTROLS' => array(
							'SENDER_STREET' => array(
								'TYPE'  => 'STRING',
								'TITLE' => Loc::getMessage("IPOLH_DPD_OPTIONS_SENDER_STREET"),
							),

							'SENDER_STREETABBR' => array(
								'TYPE'  => 'STRING',
								'TITLE' => Loc::getMessage("IPOLH_DPD_OPTIONS_SENDER_STREETABBR"),
								'HELP'  => Loc::getMessage("IPOLH_DPD_OPTIONS_SENDER_STREETABBR_HELP"),
							),

							'SENDER_HOUSE' => array(
								'TYPE'  => 'STRING',
								'TITLE' => Loc::getMessage("IPOLH_DPD_OPTIONS_SENDER_HOUSE"),
							),

							'SENDER_KORPUS' => array(
								'TYPE'  => 'STRING',
								'TITLE' => Loc::getMessage("IPOLH_DPD_OPTIONS_SENDER_KORPUS"),
							),

							'SENDER_STR' => array(
								'TYPE'  => 'STRING',
								'TITLE' => Loc::getMessage("IPOLH_DPD_OPTIONS_SENDER_STR"),
							),

							'SENDER_VLAD' => array(
								'TYPE'  => 'STRING',
								'TITLE' => Loc::getMessage("IPOLH_DPD_OPTIONS_SENDER_VLAD"),
							),

							'SENDER_OFFICE' => array(
								'TYPE'  => 'STRING',
								'TITLE' => Loc::getMessage("IPOLH_DPD_OPTIONS_SENDER_OFFICE"),
							),

							'SENDER_FLAT' => array(
								'TYPE'  => 'STRING',
								'TITLE' => Loc::getMessage("IPOLH_DPD_OPTIONS_SENDER_FLAT"),
							),
						),
					),

					array(
						'DIV'      => "IPOLH_DPD_OPTIONS_SENDER_SUBTAB_PICKUP",
						'TAB'      => Loc::getMessage("IPOLH_DPD_OPTIONS_SENDER_SUBTAB_PICKUP"),
						'ICON'     => '',
						'TITLE'    => Loc::getMessage("IPOLH_DPD_OPTIONS_SENDER_SUBTAB_PICKUP_HELP"),
						'OPTIONS'  => array(),
						'CONTROLS' => array(
							'SENDER_TERMINAL_CODE' => array(
								'TITLE' => Loc::getMessage("IPOLH_DPD_OPTIONS_SENDER_TERMINAL_CODE"),
								'TYPE'  => 'SELECT',
								'ITEMS' => function() {
									$ret = \Ipolh\DPD\DB\Terminal\Table::getList([
										'select' => [
											'CODE',
											'NAME',
										],

										'filter' => [
											'LOCATION_ID' => \Ipolh\DPD\Utils::getSaleLocationId(),
											'!SCHEDULE_SELF_PICKUP' => false,
										],

										'order' => ['NAME' => 'ASC']
									]);

									$ret->addReplacedAliases(['CODE' => 'ID']);

									return $ret->fetchAll();
								},
							),
						),
					),
				),
			),
		),
	),

	array(
		"DIV" => "IPOLH_DPD_OPTIONS_TAB_RECEIVER",
		"TAB" => Loc::getMessage("IPOLH_DPD_OPTIONS_TAB_RECEIVER"),
		"ICON" => "",
		"TITLE" => Loc::getMessage("IPOLH_DPD_OPTIONS_TAB_RECEIVER_TITLE"),
		"HELP" => Loc::getMessage("IPOLH_DPD_OPTIONS_TAB_RECEIVER_HELP"),
		"OPTIONS" => array(),
		"CONTROLS" => array(
			"IPOLH_DPD_ORDER_PROPERTY_TABS" => array(
				"TYPE"  => "TABS",
				"ITEMS" => function() use ($arPersoneTypes) {
					$tabs = array();
					$controlNames = array(
						'FIO' => true, 'NAME' => true, 'PHONE' => true, 'EMAIL' => true, 'LOCATION' => true, 'CITYALT' => false, 'STREET' => false, 'STREETABBR' => false, 
						'HOUSE' => false, 'KORPUS' => false, 'STR' => false, 'VLAD' => false, 'FLAT' => false, 'OFFICE' => false, 'PVZ_FIELD' => false,
					);

					foreach ($arPersoneTypes as $arPersoneType) {
						$listItems = array('' => Loc::getMessage('IPOLH_DPD_ORDER_PROPERTY_CUSTOM'));
						$orderProps = \Ipolh\DPD\Utils::GetOrderProps($arPersoneType["ID"]);
						foreach($orderProps as $orderProp) {
							$code = $orderProp['CODE'] ?: 'PROP_'. $orderProp['ID'];
							$listItems[$code] = $orderProp['NAME'];
						}

						$controls = array();
						foreach ($controlNames as $controlName => $isRequired) {
							$controls['RECEIVER_'. $controlName .'_'. $arPersoneType["ID"]] = array(
								'TITLE'      => Loc::getMessage('IPOLH_DPD_OPTIONS_RECEIVER_'. $controlName),
								'TYPE'       => 'SELECT',
								'ITEMS'      => $listItems,
								'HELP'       => Loc::getMessage('IPOLH_DPD_OPTIONS_RECEIVER_'. $controlName .'_HELP'),
								'VALIDATORS' => $isRequired ? array(
									'required' => Loc::getMessage('IPOLH_DPD_OPTIONS_RECEIVER_'. $controlName .'_REQUIRED', ['#PERSONE_TYPE_NAME#' => $arPersoneType['NAME'], '#PERSON_TYPE_ID#' => $arPersoneType['ID']]),
								) : array(),
							);
						}

						$tabs[] = array(
							'DIV'      => sprintf('ptype_%s_%s', $arPersoneType['ID'], $arPersoneType['SITE_ID']),
							'TAB'      => sprintf('(%s) %s (%s)', $arPersoneType['ID'], $arPersoneType['NAME'], $arPersoneType['SITE_ID']),
							'TITLE'    => '',
							'CONTROLS' => $controls,
						);
					}

					return $tabs;
				},
			),
		),
	),

	array(
		'DIV'      => "IPOLH_DPD_OPTIONS_TAB_OPTIONS",
		'TAB'      => Loc::getMessage("IPOLH_DPD_OPTIONS_TAB_OPTIONS"),
		'ICON'     => '',
		'TITLE'    => Loc::getMessage("IPOLH_DPD_OPTIONS_TAB_OPTIONS_TITLE"),
		'OPTIONS'  => array(),
		'CONTROLS' => array(
			"SELF_PICKUP" => array(
				"TITLE" => Loc::getMessage("IPOLH_DPD_OPTIONS_SELF_PICKUP"),
				"TYPE" => "SELECT",
				"ITEMS" => array(
					1 => Loc::getMessage("IPOLH_DPD_OPTIONS_SELF_PICKUP_YES"),
					0 => Loc::getMessage("IPOLH_DPD_OPTIONS_SELF_PICKUP_NO"),
				),
				"DEFAULT" => 1,
				"HELP" => Loc::getMessage("IPOLH_DPD_OPTIONS_SELF_PICKUP_HELP"),
			),

			"PAYMENT_TYPE" => array(
				"TITLE" => Loc::getMessage("IPOLH_DPD_OPTIONS_PAYMENT_TYPE"),
				"TYPE" => "SELECT",
				"ITEMS" => array(
					''    => Loc::getMessage("IPOLH_DPD_OPTIONS_PAYMENT_TYPE_AUTO"),
					'OUP' => Loc::getMessage("IPOLH_DPD_OPTIONS_PAYMENT_TYPE_OUP"),
					'OUO' => Loc::getMessage("IPOLH_DPD_OPTIONS_PAYMENT_TYPE_OUO"),
				),
				"DEFAULT" => '',
				"HELP" => Loc::getMessage("IPOLH_DPD_OPTIONS_PAYMENT_TYPE_HELP"),
			),

			'PICKUP_TIME_PERIOD' => array(
				'TITLE' => Loc::getMessage("IPOLH_DPD_OPTIONS_PICKUP_TIME_PERIOD"),
				'TYPE'  => 'SELECT',
				'ITEMS' => array(
					'9-18'  => Loc::getMessage("IPOLH_DPD_OPTIONS_PICKUP_TIME_PERIOD_9_18"),
					'9-13'  => Loc::getMessage("IPOLH_DPD_OPTIONS_PICKUP_TIME_PERIOD_9_13"),
					'13-18' => Loc::getMessage("IPOLH_DPD_OPTIONS_PICKUP_TIME_PERIOD_13_18"),
				),
				'DEFAULT' => '9-18',
			),

			'DELIVERY_TIME_PERIOD' => array(
				'TITLE' => Loc::getMessage("IPOLH_DPD_OPTIONS_DELIVERY_TIME_PERIOD"),
				'TYPE'  => 'SELECT',
				'ITEMS' => array(
					'9-18'  => Loc::getMessage("IPOLH_DPD_OPTIONS_DELIVERY_TIME_PERIOD_9_18"),
					'9-13'  => Loc::getMessage("IPOLH_DPD_OPTIONS_DELIVERY_TIME_PERIOD_9_13"),
					'13-18' => Loc::getMessage("IPOLH_DPD_OPTIONS_DELIVERY_TIME_PERIOD_13_18"),
					'18-21' => Loc::getMessage("IPOLH_DPD_OPTIONS_DELIVERY_TIME_PERIOD_18_21"),
				),
				'DEFAULT' => '9-18',
			),

			'CARGO_NUM_PACK' => array(
				'TITLE'   => Loc::getMessage("IPOLH_DPD_OPTIONS_CARGO_NUM_PACK"),
				'TYPE'    => 'STRING',
				'DEFAULT' => 1,
			),

			'CARGO_CATEGORY' => array(
				'TITLE'   => Loc::getMessage("IPOLH_DPD_OPTIONS_CARGO_CATEGORY"),
				'TYPE'    => 'STRING',
				'DEFAULT' => Loc::getMessage("IPOLH_DPD_OPTIONS_CARGO_CATEGORY_DEFAULT"),
				"VALIDATORS" => array(
					"required" => Loc::getMessage("IPOLH_DPD_OPTIONS_CARGO_CATEGORY_REQUIRED"),
				),
			),

			'SUBHEAER_OPTIONS_OPTIONS' => array(
				'TYPE'  => 'HEADER',
				'TITLE' => Loc::getMessage("IPOLH_DPD_OPTIONS_TAB_OPTIONS_SUBTAB_OPTIONS"),
			),

			'OPTIONS_OPTIONS_HELP' => array(
				'TYPE' => 'COMMENT',
				'HELP' => Loc::getMessage("IPOLH_DPD_OPTIONS_TAB_OPTIONS_HELP"),
			),

			'CARGO_REGISTERED' => array(
				'TYPE'  => 'CHECKBOX',
				'TITLE' => Loc::getMessage("IPOLH_DPD_OPTIONS_CARGO_REGISTERED"),
				'HELP'  => Loc::getMessage("IPOLH_DPD_OPTIONS_CARGO_REGISTERED_HELP"),
				'VALUE' => 'Y',
				'UNCHECK_VALUE' => 'N',
			),

			'DVD' => array(
				'TYPE'  => 'CHECKBOX',
				'TITLE' => Loc::getMessage("IPOLH_DPD_OPTIONS_DVD"),
				'HELP'  => Loc::getMessage("IPOLH_DPD_OPTIONS_DVD_HELP"),
				'COMMENT' => Loc::getMessage("IPOLH_DPD_OPTIONS_PAID_COMMENT"),			
				'VALUE' => 'Y',
				'UNCHECK_VALUE' => 'N',
			),

			'TRM' => array(
				'TYPE'  => 'CHECKBOX',
				'TITLE' => Loc::getMessage("IPOLH_DPD_OPTIONS_TRM"),
				'COMMENT' => Loc::getMessage("IPOLH_DPD_OPTIONS_PAID_COMMENT"),
				'VALUE' => 'Y',
				'UNCHECK_VALUE' => 'N',
			),

			'PRD' => array(
				'TYPE'  => 'CHECKBOX',
				'TITLE' => Loc::getMessage("IPOLH_DPD_OPTIONS_PRD"),
				'HELP'  => Loc::getMessage("IPOLH_DPD_OPTIONS_PRD_HELP"),
				'COMMENT' => Loc::getMessage("IPOLH_DPD_OPTIONS_PAID_COMMENT"),
				'VALUE' => 'Y',
				'UNCHECK_VALUE' => 'N',
			),

			'VDO' => array(
				'TYPE'  => 'CHECKBOX',
				'TITLE' => Loc::getMessage("IPOLH_DPD_OPTIONS_VDO"),
				'HELP'  => Loc::getMessage("IPOLH_DPD_OPTIONS_VDO_HELP"),
				'COMMENT' => Loc::getMessage("IPOLH_DPD_OPTIONS_PAID_COMMENT"),
				'VALUE' => 'Y',
				'UNCHECK_VALUE' => 'N',
			),

			'OGD' => array(
				'TITLE' => Loc::getMessage("IPOLH_DPD_OPTIONS_OGD"),
				'COMMENT' => Loc::getMessage("IPOLH_DPD_OPTIONS_PAID_COMMENT"),
				'TYPE'  => 'SELECT',
				'ITEMS' => array(
					''     => Loc::getMessage("IPOLH_DPD_OPTIONS_OGD_EMPTY"),
					Loc::getMessage("IPOLH_DPD_OPTIONS_OGD_VNESH") => Loc::getMessage("IPOLH_DPD_OPTIONS_OGD_VNESH_TITLE"),
					Loc::getMessage("IPOLH_DPD_OPTIONS_OGD_PRIM") => Loc::getMessage("IPOLH_DPD_OPTIONS_OGD_PRIM_TITLE"),
					Loc::getMessage("IPOLH_DPD_OPTIONS_OGD_RAB") => Loc::getMessage("IPOLH_DPD_OPTIONS_OGD_RAB_TITLE"),
					Loc::getMessage("IPOLH_DPD_OPTIONS_OGD_SOOT") => Loc::getMessage("IPOLH_DPD_OPTIONS_OGD_SOOT_TITLE"),
				),
			),

			'SUBHEADER_OPTIONS_NOTIFY' => array(
				'TYPE'  => 'HEADER',
				'TITLE' => Loc::getMessage("IPOLH_DPD_OPTIONS_TAB_OPTIONS_SUBTAB_NOTIFY"),
			),

			'SMS' => array(
				'TYPE'  => 'HIDDEN',
				'TITLE' => Loc::getMessage("IPOLH_DPD_OPTIONS_SMS"),
				'HELP'  => Loc::getMessage("IPOLH_DPD_OPTIONS_SMS_HELP"),
			),

			'EML' => array(
				'TYPE'  => 'HIDDEN',
				'TITLE' => Loc::getMessage("IPOLH_DPD_OPTIONS_EML"),
			),

			'ESD' => array(
				'TYPE'  => 'HIDDEN',
				'TITLE' => Loc::getMessage("IPOLH_DPD_OPTIONS_ESD"),
			),

			'ESZ' => array(
				'TYPE'  => 'STRING',
				'TITLE' => Loc::getMessage("IPOLH_DPD_OPTIONS_ESZ"),
				'HELP'  => Loc::getMessage("IPOLH_DPD_OPTIONS_ESZ_HELP"),
			),

			'POD' => array(
				'TYPE'  => 'HIDDEN',
				'TITLE' => Loc::getMessage("IPOLH_DPD_OPTIONS_POD"),
				'HELP'  => Loc::getMessage("IPOLH_DPD_OPTIONS_POD_HELP"),
				'COMMENT' => Loc::getMessage("IPOLH_DPD_OPTIONS_PAID_COMMENT"),
			),
		),
	),

	array(
		'DIV'      => "IPOLH_DPD_OPTIONS_TAB_STATUS",
		'TAB'      => Loc::getMessage("IPOLH_DPD_OPTIONS_TAB_STATUS"),
		'ICON'     => '',
		'TITLE'    => Loc::getMessage("IPOLH_DPD_OPTIONS_TAB_STATUS_TITLE"),
		'HELP'     => Loc::getMessage("IPOLH_DPD_OPTIONS_TAB_STATUS_HELP"),
		'CONTROLS' => array(
			'SET_TRACKING_NUMBER' => array(
				'TYPE'  => 'CHECKBOX',
				'TITLE' => Loc::getMessage("IPOLH_DPD_OPTIONS_SET_TRACKING_NUMBER"),
				'DEFAULT' => 0,
			),

			'MARK_PAYED' => array(
				'TYPE'  => 'CHECKBOX',
				'TITLE' => Loc::getMessage("IPOLH_DPD_OPTIONS_MARK_PAYED"),
				'HELP' => Loc::getMessage("IPOLH_DPD_OPTIONS_MARK_PAYED_HELP")
			),

			'STATUS_ORDER_CHECK' => array(
				'TYPE'     => 'checkbox',
				'TITLE'    => Loc::getMessage('IPOLH_DPD_OPTIONS_STATUS_ORDER_CHECK'),
				'DEFAULT'  => 0,
				'ATTRS'    => array(
					'onchange' => ""
						. "BX('IPOLH_DPD_OPTIONS_STATUS_ORDER_PICKUP').disabled = !this.checked;"
						. "BX('IPOLH_DPD_OPTIONS_STATUS_ORDER_TRANSIT').disabled = !this.checked;"
						. "BX('IPOLH_DPD_OPTIONS_STATUS_ORDER_READY').disabled = !this.checked;"
						. "BX('IPOLH_DPD_OPTIONS_STATUS_ORDER_COURIER').disabled = !this.checked;"
						. "BX('IPOLH_DPD_OPTIONS_STATUS_ORDER_DELIVERED').disabled = !this.checked;"
						. "BX('IPOLH_DPD_OPTIONS_STATUS_ORDER_CANCEL').disabled = !this.checked;"
					,
				),
			),

			'STATUS_ORDER_PICKUP' => array(
				'TITLE' => Loc::getMessage("IPOLH_DPD_OPTIONS_STATUS_ORDER_PICKUP"),
				'TYPE'  => 'SELECT',
				'ITEMS' => $arOrderStatuses,
				'NULL'  => Loc::getMessage("IPOLH_DPD_OPTIONS_STATUS_ORDER_EMPTY"),
				'ATTRS' => array(
					'disabled' => !Option::get(IPOLH_DPD_MODULE, 'STATUS_ORDER_CHECK', 0),
				),
			),

			'STATUS_ORDER_TRANSIT' => array(
				'TITLE' => Loc::getMessage("IPOLH_DPD_OPTIONS_STATUS_ORDER_TRANSIT"),
				'TYPE'  => 'SELECT',
				'ITEMS' => $arOrderStatuses,
				'NULL'  => Loc::getMessage("IPOLH_DPD_OPTIONS_STATUS_ORDER_EMPTY"),
				'ATTRS' => array(
					'disabled' => !Option::get(IPOLH_DPD_MODULE, 'STATUS_ORDER_CHECK', 0),
				),
			),

			'STATUS_ORDER_READY' => array(
				'TITLE' => Loc::getMessage("IPOLH_DPD_OPTIONS_STATUS_ORDER_READY"),
				'TYPE'  => 'SELECT',
				'ITEMS' => $arOrderStatuses,
				'NULL'  => Loc::getMessage("IPOLH_DPD_OPTIONS_STATUS_ORDER_EMPTY"),
				'ATTRS' => array(
					'disabled' => !Option::get(IPOLH_DPD_MODULE, 'STATUS_ORDER_CHECK', 0),
				),
			),

			'STATUS_ORDER_COURIER' => array(
				'TITLE' => Loc::getMessage("IPOLH_DPD_OPTIONS_STATUS_ORDER_COURIER"),
				'TYPE'  => 'SELECT',
				'ITEMS' => $arOrderStatuses,
				'NULL'  => Loc::getMessage("IPOLH_DPD_OPTIONS_STATUS_ORDER_EMPTY"),
				'ATTRS' => array(
					'disabled' => !Option::get(IPOLH_DPD_MODULE, 'STATUS_ORDER_CHECK', 0),
				),
			),

			'STATUS_ORDER_DELIVERED' => array(
				'TITLE' => Loc::getMessage("IPOLH_DPD_OPTIONS_STATUS_ORDER_DELIVERED"),
				'TYPE'  => 'SELECT',
				'ITEMS' => $arOrderStatuses,
				'NULL'  => Loc::getMessage("IPOLH_DPD_OPTIONS_STATUS_ORDER_EMPTY"),
				'ATTRS' => array(
					'disabled' => !Option::get(IPOLH_DPD_MODULE, 'STATUS_ORDER_CHECK', 0),
				),
			),

			'STATUS_ORDER_CANCEL' => array(
				'TITLE' => Loc::getMessage("IPOLH_DPD_OPTIONS_STATUS_ORDER_CANCEL"),
				'TYPE'  => 'SELECT',
				'ITEMS' => $arOrderStatuses,
				'NULL'  => Loc::getMessage("IPOLH_DPD_OPTIONS_STATUS_ORDER_EMPTY"),
				'ATTRS' => array(
					'disabled' => !Option::get(IPOLH_DPD_MODULE, 'STATUS_ORDER_CHECK', 0),
				),
			),
		),
	),
);

$helper = new \Ipolh\DPD\Admin\ModuleOptions(IPOLH_DPD_MODULE, $aTabs);
$helper->processAndShow();