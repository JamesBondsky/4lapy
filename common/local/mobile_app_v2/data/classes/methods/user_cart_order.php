<?php
class user_cart_order extends APIServer
{
	public function post($arInput)
	{
		$GLOBALS["DB"]->StartUsingMasterOnly();
		if(defined("DEBUG_MP_ORDERS") && DEBUG_MP_ORDERS){
			define("SALE_DEBUG", true);
		}
		log_MP_order(array(
			'Входящие параметры' => $arInput
		));
		$arResult = null;

		$token = $arInput['token'];
		$card = (string)$arInput['cart_param']['card'];
		$cardUsed = intval($arInput['cart_param']['card_used']);
		$deliveryId = intval($arInput['cart_param']['delivery_type']);
		$payment_type = (string)$arInput['cart_param']['payment_type'];
		$communication_type = (string)$arInput['cart_param']['communication_type'];
		$deliveryPlace = (array)$arInput['cart_param']['delivery_place'];
		$deliveryTimeId = (string)$arInput['cart_param']['delivery_range_id'];
		$deliveryDate = (string)$arInput['cart_param']['delivery_range_date'];
		$pickupShopId = (string)$arInput['cart_param']['pickup_place'];
		$comment = (string)$arInput['cart_param']['comment'];
		$userPhone = (string)$arInput['cart_param']['user_phone'];
		$userPhone = preg_replace("/^(?:.*)(?|\((\d{3})\)(\d{3})|\((\d{4})\)(\d{2})|(\d{3})(\d{3}))(\d{2})(\d{2})$/", "$1$2$3$4", $userPhone);
		$userExtraPhone = (string)$arInput['cart_param']['extra_phone'];
		$userExtraPhone = preg_replace("/^(?:.*)(?|\((\d{3})\)(\d{3})|\((\d{4})\)(\d{2})|(\d{3})(\d{3}))(\d{2})(\d{2})$/", "$1$2$3$4", $userExtraPhone);
		$promocode = (string)$arInput['cart_param']['promocode'];
		// $region_courier_from_dc = (bool)$arInput['cart_param']['region_courier_from_dc'];
		if(isset($arInput['cart_param']['region_courier_from_dc'])){
			$region_courier_from_dc = (bool)$arInput['cart_param']['region_courier_from_dc'];
		}elseif(isset($arInput['region_courier_from_dc'])){
			$region_courier_from_dc = (bool)$arInput['region_courier_from_dc'];
		}else{
			$region_courier_from_dc = false;
		}
		
		
		$userAgent = $_SERVER['HTTP_USER_AGENT'];
		if (preg_match('/iOS\s\d+\.\d/', $userAgent)) {
			$platform = 'ios';
		} elseif (preg_match('/^okhttp\/\d+(\.\d+)?(\.\d+)?/', $userAgent)
			|| preg_match('/\(Android .*\)/', $userAgent)) {
			$platform = 'android';
		} else {
			$platform = '';
		}

		//
		$oReqProducts = new \collection;

		foreach ($arInput['cart_param']['goods'] as $arGoods) {
			$oReqProdQty = new \req_product_qty($arGoods);

			if ($oReqProdQty->hasErrors()) {
				$this->addError('required_params_missed');
			} else {
				if ($oProd = $oReqProducts->get($oReqProdQty->getField('PRODUCT_ID'))) {
					$oProd->setField('QUANTITY', $oProd->getField('QUANTITY') + $oReqProdQty->getField('QUANTITY'));
				} else {
					$oReqProducts->addItem($oReqProdQty->getField('PRODUCT_ID'), $oReqProdQty);
				}
			}
		}

		switch ($communication_type) {
			case 'push':
				$communic = 1;
				break;
			
			case 'tel':
				$communic = 2;
				break;
			
			default:
				break;
		}

		//если улица не из справочника communic будет 06
		if (in_array($deliveryId, \order::DELIVERY_ID_COURIER) or in_array($deliveryId, \order::DELIVERY_ID_COURIER_DPD)) {
			if (!(\GeoCatalog::GetStreetLocationId($deliveryPlace['city']['id'], $deliveryPlace['street_name']))) {
				$communic = 5;
			}
		}
		//!если улица не из справочника communic будет 06

		$communic = ($communic)?str_pad($communic, 2, "0", STR_PAD_LEFT):'';

		// общие параметры
		if (!$oReqProducts->count()
			|| !$deliveryId
			|| !$userPhone
			|| (!$card && $cardUsed)
		) {
			$this->addError('required_params_missed');
		}

		// параметры для самовывоза
		if (in_array($deliveryId, \order::DELIVERY_ID_PICKUP) && !$pickupShopId) {
			$this->addError('required_params_missed');
		}

		// параметры для курьера
		if (in_array($deliveryId, \order::DELIVERY_ID_COURIER)
			&& (!$deliveryPlace['city']['id']
				|| !$deliveryPlace['street_name']
				|| !$deliveryPlace['house']
				|| !$deliveryDate
				|| strlen($deliveryTimeId) <= 0
			)
		) {
			$this->addError('required_params_missed');
		}

		// // способ оплаты
		// if (!in_array($payment_type, \order::PAYMENT_CODE)) {
		// 	$this->addError('required_params_missed');
		// }

		if (!$this->hasErrors()) {
			if (in_array($deliveryId, \order::DELIVERY_ID_PICKUP) && !$deliveryDate) {
				// для самовывоза вычисляем дату самостоятельно, если она не указана
				$deliveryDate = \Available::GetDateAvailable($pickupShopId);

				if (!$deliveryDate) {
					$oDate = new \Bitrix\Main\Type\Date;
					$deliveryDate = $oDate->format(API_DATE_FORMAT);
				}
			}

			if (in_array($deliveryId, \order::DELIVERY_ID_RESERVE)) {
				$oDate = new \Bitrix\Main\Type\Date;
				$deliveryDate = $oDate->format(API_DATE_FORMAT);
			}

			if ($this->getUserId()) {
				$userExists = true;
				$oUser = new \user($this->getUserId());
			} else {
				$userExists = false;
				$userLogin = 'im'.md5($userPhone).'@fastorder.ru';

				// пробуем найти пользователя
				if ($userId = \user::getIdByLogin($userLogin)) {
					$oUser = new \user($userId);
				} else {
					// регистрируем пользователя
					$oResult = \user::register(array(
						'LOGIN' => $userLogin,
						'PASSWORD' => randString(20),
					));

					if ($oResult->isSuccess()) {
						$GLOBALS['USER']->Logout();
						$oUser = new \user($oResult->getData()['ID']);
					}
				}
			}

			if (is_object($oUser)) {
				$oUser->setField('FUSER_ID', $this->getFuserId());
			} else {
				return null;
			}

			if($promocode)
				$promocode_result = MyCAjax::AddCouponAPI($promocode, $this->getFuserId(), $this->getUserId());

			$oOrder = new \order;
			$oOrder->setUser($oUser);

			$PAY_SYSTEM_ID = ($payment_type)?\order::MAP_PAYMENT_CODE_INTO_PAYMENT_ID[$payment_type]:'1';

			$oOrder->setFields(array(
				'DELIVERY_ID' => $deliveryId,
				'SITE_ID' => SITE_ID,
				'PERSON_TYPE_ID' => 1,
				// 'PAY_SYSTEM_ID' => 1,
				'PAY_SYSTEM_ID' => $PAY_SYSTEM_ID,
				'SUM_PAID' => $cardUsed,
				'USER_DESCRIPTION' => $comment,
				'region_courier_from_dc' => ($region_courier_from_dc ? 'Y' : 'N'),
				'device_type' => $platform
			));

			//
			$summBasket = 0;
			// log_MP_order(array(
			// 	'Корзина перед добавлением 1' => $oOrder->getBasket()->getBasketItems()
			// ));
			foreach ($oOrder->getBasket()->getBasketItems() as $oBasketItem) {
				$productId = $oBasketItem->getField('PRODUCT_ID');

				if ($oProd = $oReqProducts->get($productId)) {
					if ($oProd->getField('QUANTITY') != $oBasketItem->getField('QUANTITY')) {
						$oBasketItem->setField('QUANTITY', $oProd->getField('QUANTITY'));
					}

					$summBasket += round($oBasketItem->getField('PRICE'), 2, PHP_ROUND_HALF_DOWN) * $oBasketItem->getField('QUANTITY');
				} else {
					$oOrder->getBasket()->deleteBasketItem($oBasketItem);
				}
			}
			// log_MP_order(array(
			// 	'Корзина перед добавлением 2' => $oOrder->getBasket()->getBasketItems(),
			// 	'Сумма позиций перед добавлением' => $summBasket
			// ));
			//
			if (in_array($oOrder->getField('DELIVERY_ID'), \order::DELIVERY_ID_COURIER)) {
				foreach ($oOrder->getProperties() as $oProperty) {
					if ($oProperty->getField('CODE') == 'DELIVERY_CITY') {
						$oProperty->setValue($deliveryPlace['city']['id']);
					} elseif ($oProperty->getField('CODE') == 'STREET') {
						$oProperty->setValue($deliveryPlace['street_name']);
					} elseif ($oProperty->getField('CODE') == 'HOME') {
						$oProperty->setValue($deliveryPlace['house']);
					} elseif ($oProperty->getField('CODE') == 'KVART') {
						$oProperty->setValue($deliveryPlace['flat']);
					} elseif ($oProperty->getField('CODE') == 'contact_phone') {
						$oProperty->setValue($userPhone);
					} elseif ($oProperty->getField('CODE') == 'contact_phone_alt') {
						$oProperty->setValue($userExtraPhone);
					} elseif ($oProperty->getField('CODE') == 'contact_person') {
						$oProperty->setValue(join(' ', array($oUser->getField('LAST_NAME'), $oUser->getField('NAME'))));
					} elseif ($oProperty->getField('CODE') == 'delivery_date') {
						$oProperty->setValue($deliveryDate);
					} elseif ($oProperty->getField('CODE') == 'delivery_time') {
						$oProperty->setValue($deliveryTimeId);
					} elseif ($oProperty->getField('CODE') == 'delivery_address') {
						if ($deliveryPlace['city']['id']) {
							$oCity = \city::getById($deliveryPlace['city']['id']);
							$path = join(', ', array_merge(array_reverse($oCity['path']), array($oCity['title'])));
							$path .= ", ул. {$deliveryPlace['street_name']}, дом {$deliveryPlace['house']}, квартира {$deliveryPlace['flat']}";
							$oProperty->setValue($path);
							$oOrder->setField('USER_DESCRIPTION', $oOrder->getField('USER_DESCRIPTION').($deliveryPlace['flat'] ? ' кв.'.$deliveryPlace['flat'] : ''));
						}
					} elseif ($oProperty->getField('CODE') == 'DETAILS') {
						$oProperty->setValue($deliveryPlace['details']);
					} elseif ($oProperty->getField('CODE') == 'fromAPP') {
						$oProperty->setValue('Y');
					} elseif ($oProperty->getField('CODE') == 'communic') {
						if($communic){
							$oProperty->setValue($communic);
						}
					} elseif ($oProperty->getField('CODE') == 'region_courier_from_dc') {
						$oProperty->setValue(($region_courier_from_dc) ? 'Y' : 'N');
					} elseif ($oProperty->getField('CODE') == 'device_type') {
						$oProperty->setValue($platform);
					}
				}
			}

			if (in_array($oOrder->getField('DELIVERY_ID'), \order::DELIVERY_ID_PICKUP)) {
				foreach ($oOrder->getProperties() as $oProperty) {
					if ($oProperty->getField('CODE') == 'contact_phone') {
						$oProperty->setValue($userPhone);
					} elseif ($oProperty->getField('CODE') == 'contact_phone_alt') {
						$oProperty->setValue($userExtraPhone);
					} elseif ($oProperty->getField('CODE') == 'contact_person') {
						$oProperty->setValue(join(' ', array($oUser->getField('LAST_NAME'), $oUser->getField('NAME'))));
					} elseif ($oProperty->getField('CODE') == 'delivery_date') {
						$oProperty->setValue($deliveryDate);
					} elseif ($oProperty->getField('CODE') == 'delivery_address') {
						$oProperty->setValue($pickupShopId);
					} elseif ($oProperty->getField('CODE') == 'DETAILS') {
						$oProperty->setValue($deliveryPlace['details']);
					} elseif ($oProperty->getField('CODE') == 'fromAPP') {
						$oProperty->setValue('Y');
					} elseif ($oProperty->getField('CODE') == 'communic') {
						if($communic){
							$oProperty->setValue($communic);
						}
					} elseif ($oProperty->getField('CODE') == 'region_courier_from_dc') {
						$oProperty->setValue(($region_courier_from_dc) ? 'Y' : 'N');
					} elseif ($oProperty->getField('CODE') == 'device_type') {
						$oProperty->setValue($platform);
					}
				}
			}

			//
			if ($orderId = $oOrder->doSave()) {
				$oOrderSave = new \order($orderId);
				$arResult['cart_order'] = $oOrderSave->getData();
				log_MP_order(array(
					'Итоговый заказ' => $arResult
				));
				$promocode_result = MyCAjax::AddCouponAPI('', $this->getFuserId(), $this->getUserId());

				if ($userExists) {
					$arGoods = array();
					foreach ($arResult['cart_order']['cart_param']['goods'] as $arGood) {
						$arGoods[] = $arGood['goods']['id'];
					}
					if (!empty($arGoods)) {
						\personal_goods::add(array(
							'goods_id_list' => $arGoods
						));
					}
				}
				// log_($arResult);
				// log_('-------------------------------------------------------------------');
				$GLOBALS["DB"]->StopUsingMasterOnly();
				return $arResult;
			}
		}
		// log_($arResult);
		// log_('-------------------------------------------------------------------');
		$GLOBALS["DB"]->StopUsingMasterOnly();
		return $arResult;
	}
}
