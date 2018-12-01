<?

class order_mz extends \APIServer
{
	protected $fields;

	public function __construct($arParams)
	{
		if (isset($arParams['card_number'])) {
			$this->fields['CARD_NUMBER'] = $arParams['card_number'];
		}

		if (isset($arParams['user_id'])) {
			$this->fields['USER_ID'] = $arParams['user_id'];
		}

		if (isset($arParams['id'])) {
			// \Bitrix\Main\Loader::includeModule('iblock');

			// $arElement = \CIBlockElement::GetList(
			// 	array(),
			// 	array(
			// 		'IBLOCK_ID' => \CIBlockTools::GetIBlockId('cheques'),
			// 		'XML_ID' => ($cardNumber ?: '---'),
			// 	),
			// 	false,
			// 	false,
			// 	array('ID', 'PROPERTY_CHECKS')
			// )->Fetch();

			// foreach ($arElement['PROPERTY_CHECKS_VALUE'] as $arCheck) {
			// 	$arOrder = unserialize($arCheck['TEXT']);

			// 	if ($arOrder['ChequeNumber'] == $orderId) {
			// 		$this->fields['USER_ID'] = array(
			// 			'ID' => $arOrder['ChequeNumber'],
			// 			'CHEQUE_ID' => $arOrder['ChequeId'],
			// 			'SUM_ACCRUED' => round($arOrder['Bonus'], 2),
			// 			'SUM_PAID' => round($arOrder['PaidByBonus'], 2),
			// 			'PRICE' => round($arOrder['Summ'], 2),
			// 			'PRICE_DELIVERY' => 0,
			// 			'DATE_INSERT' => new \Bitrix\Main\Type\DateTime(strtotime($arOrder['Date']), 'U'),
			// 			'STATUS_ID' => reset(\order::getStatusIdFinal()),
			// 		);
			// 		break;
			// 	}
			// }
		}
	}

	public static function getList(array $arParams)
	{
		$arResult = array();

		if (isset($arParams['filter']['=CARD_NUMBER']) && $arParams['filter']['=CARD_NUMBER']) {
			\Bitrix\Main\Loader::includeModule('iblock');

			$arElementChecks = \CIBlockElement::GetList(
				array(),
				array(
					'IBLOCK_ID' => \CIBlockTools::GetIBlockId('cheques'),
					'XML_ID' => ($arParams['filter']['=CARD_NUMBER']),
				),
				false,
				false,
				array('ID', 'PROPERTY_CHECKS')
			)->Fetch();

			$arCheckues = array();

			foreach ($arElementChecks['PROPERTY_CHECKS_VALUE'] as $arCheck) {
				$arCheckues[] = unserialize($arCheck['TEXT']);
			}

			$arCheckues = array_filter($arCheckues, function ($arOrder) use ($arParams) {
				if ($arOrder['BUSINESS_UNIT'] == 'ishop') {
					// пропускаем "сайтовые" заказы
					return false;
				}

				if (isset($arParams['filter']['=ID'])
					&& $arParams['filter']['=ID']
					&& $arOrder['ChequeNumber'] != $arParams['filter']['=ID']
				) {
					return false;
				}

				return true;
			});

			foreach ($arCheckues as $arCheckue) {
				$arResult[] = array(
					'ID' => $arCheckue['ChequeNumber'],
					'CHEQUE_ID' => $arCheckue['ChequeId'],
					'SUM_ACCRUED' => round($arCheckue['BONUS'], 2),
					'SUM_PAID' => round($arCheckue['PAID_BY_BONUS'], 2),
					'PRICE' => round($arCheckue['SUMM'], 2),
					'PRICE_DISCOUNTED' => round($arCheckue['SUMM_DISCOUNTED'], 2),
					'PRICE_DELIVERY' => 0,
					'DATE_INSERT' => new \Bitrix\Main\Type\DateTime($arCheckue['DATE']),
					'STATUS_ID' => reset(\order::getStatusIdFinal()),
				);
			}
		}

		return $arResult;
	}

	public static function isExist($cardNumber, $orderId)
	{
		if (self::getList(array('filter' => array('=CARD_NUMBER' => $cardNumber, '=ID' => $orderId)))) {
			return true;
		} else {
			return false;
		}
	}
}