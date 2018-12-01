<?
class delivery_range extends \APIServer
{
	const INTERVAL_DAYS = 4; // за сколько дней, включая текущий, выводить интервалы доставки

	// получение списка интервалов и дат доставки
	public function get($arInput)
	{
		\Bitrix\Main\Loader::includeModule('sale');

		$arResult = null;

		if (!(isset($arInput['city_id']) && $cityId = intval($arInput['city_id']))) {
			$this->addError($this->ERROR['required_params_missed']);
		}

		if (!$this->hasErrors()) {
			$arDeliveryList = \MyCAjax::GetDeliveryList($cityId, '', '', 0);
			$arDeliveryList = ($arDeliveryList['result'] ? $arDeliveryList['data'] : array());

			if (!empty($arDeliveryList)
				&& array_intersect(\order::DELIVERY_ID_COURIER_DPD, array_keys($arDeliveryList))
			) {
				$arDeliveryInfo = \CDeliveryDPD::Calculate(
					\CDeliveryDPD::GetDeliveryCode().'_DD',
					array('without_cache' => false),
					array(
						'LOCATION_FROM' =>  \Bitrix\Main\Config\Option::get('sale', 'location', 12),
						'LOCATION_TO' => CSaleLocation::getLocationCODEbyID($cityId),
						'FUSER_ID' => $this->getFuserId(),
					)
				);

				if ($arDeliveryInfo && $arDeliveryInfo['days']) {
					$oDate = new \Bitrix\Main\Type\Date();
					$oDateDelivery = new \Bitrix\Main\Type\Date();
					$oDate->add('1 days');
					$oDateDelivery->add("{$arDeliveryInfo['days']} days")->add('1 days');

					$arResult[] = array(
						'id' => 666,
						'title' => "Срок доставки от {$arDeliveryInfo['days']} раб. дн.",
						'delivery_date' => $oDateDelivery->format(API_DATE_FORMAT),
						'available' => array(
							'day' => $oDate->format(API_DATE_FORMAT),
							'time' => $oDate->format(API_TIME_FORMAT),
						)
					);
				} else {
					$arResult['feedback_text'] = 'К сожалению, в вашей корзине нет товаров, которые мы могли бы доставить в указанный город';
				}
			} else {
				if ($arDeliveryTime = \GeoCatalog::GetDeliveryTime($cityId)) {
					$oDate = new \Bitrix\Main\Type\Date();

					for ($ii = 0; $ii < $this::INTERVAL_DAYS; $ii++) {
						foreach ($arDeliveryTime as $arItem) {
							$oDateDelivery = clone $oDate;
							$oDateDelivery = $oDateDelivery->add($arItem['available']['day'].' days');

							if ($arItem['available']['day'] + $ii == 0) {
								$title = 'Сегодня';
							} elseif ($arItem['available']['day'] + $ii == 1) {
								$title = 'Завтра';
							} else {
								$title = $oDateDelivery->format(API_DATE_FORMAT);
							}

							$arResult[] = array(
								'id' => $arItem['id'],
								'title' => $title.' '.$arItem['label'],
								'sort' => $oDate->format('Y-m-d').' '.$arItem['available']['time'],
								'delivery_date' => $oDateDelivery->format(API_DATE_FORMAT),
								'available' => array(
									'day' => $oDate->format(API_DATE_FORMAT),
									'time' => $arItem['available']['time'],
								)
							);
						}

						$oDate->add('1 days');
					}

					usort($arResult, function ($a, $b) {
						if ($a['sort'] > $b['sort']) {
							return 1;
						} elseif ($a['sort'] < $b['sort']) {
							return -1;
						} elseif ($a['title'] > $b['title']) {
							return 1;
						} elseif ($a['title'] < $b['title']) {
							return -1;
						} else {
							return 0;
						}
					});

					$arDay_if = array('01.02.2018', '02.02.2018', '03.02.2018', '04.02.2018', '05.02.2018');

					foreach ($arResult as $key => $arItem) {
						unset($arResult[$key]['sort']);
						if(in_array($arItem['delivery_date'], $arDay_if)){
							unset($arResult[$key]);
						}
					}
					$arResult = array_values($arResult);
				}
			}
		}

		return $arResult;
	}
}
