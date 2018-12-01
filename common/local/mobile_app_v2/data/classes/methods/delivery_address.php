<?php
class delivery_address extends APIServer
{
	protected $type = 'token';
	protected $profileProps = null;

	private function loadOrderUserProps()
	{
		\Bitrix\Main\Loader::includeModule('sale');

		if (is_null($this->profileProps)) {
			$oOrderProps = \CSaleOrderProps::GetList(
				array(),
				array(
					'ACTIVE' => 'Y',
					'USER_PROPS' => 'Y',
				),
				false,
				false,
				array('ID', 'NAME', 'CODE')
			);

			while ($arOrderProp = $oOrderProps->Fetch()) {
				$this->profileProps[$arOrderProp['CODE']] = array(
					'ID' => $arOrderProp['ID'],
					'NAME' => $arOrderProp['NAME'],
					'CODE' => $arOrderProp['CODE']
				);
			}
		}
	}

	//получение списка адресов доставки
	public function get($arInput)
	{
		\Bitrix\Main\Loader::includeModule('sale');

		$arResult = null;

		if ($this->User['user_id'] > 0) {
			$oProfilesDb = \CSaleOrderUserProps::GetList(
				array('DATE_UPDATE' => 'DESC'),
				array('USER_ID' => $this->User['user_id'])
			);

			while ($arProfileDb = $oProfilesDb->Fetch()) {
				$arProfileValues = array();

				$oProfileProps = \CSaleOrderUserPropsValue::GetList(
					array('USER_PROPS_ID' => 'DESC'),
					array(
						'USER_PROPS_ID' => $arProfileDb['ID'],
						'CODE' => array('DELIVERY_CITY', 'STREET', 'HOME', 'KVART', 'DETAILS'),
					)
				);

				while ($arProfileProp = $oProfileProps->Fetch()) {
					$arProfileValues[$arProfileProp['PROP_CODE']] = $arProfileProp['VALUE'];
				}

				$arAddress = array(
					'id' => $arProfileDb['ID'],
					'title' => $arProfileDb['NAME'],
					'city' => (\city::getById($arProfileValues['DELIVERY_CITY']) ?: array()),
					'street_name' => ($arProfileValues['STREET'] ?: ''),
					'house' => ($arProfileValues['HOME'] ?: ''),
					'flat' => ($arProfileValues['KVART'] ?: ''),
					'details' => ($arProfileValues['DETAILS'] ?: ''),
				);

				$arResult['address'][] = $arAddress;
			}
		} else {
			$this->addError('user_not_authorized');
		}

		return $arResult;
	}

	//обновление адреса доставки (добавить обновление названия профиля а так же проверку на изменение данных профиля)
	public function post($arInput)
	{
		\Bitrix\Main\Loader::includeModule('sale');

		$arResult = null;

		if ($this->User['user_id'] > 0) {
			if ($arInput['address']['id']
				&& $arInput['address']['title']
				&& $arInput['address']['city']['id']
				&& $arInput['address']['street_name']
				&& $arInput['address']['house']
			) {
				$profileId = $arInput['address']['id'];
				$profileTitle = $arInput['address']['title'];
				$arProfileValues = array(
					'DELIVERY_CITY' => $arInput['address']['city']['id'],
					'STREET' => $arInput['address']['street_name'],
					'HOME' => $arInput['address']['house'],
					'KVART' => $arInput['address']['flat'],
					'DETAILS' => $arInput['address']['details'],
				);

				$arProfileDb = \CSaleOrderUserProps::GetList(
					array(),
					array(
						'USER_ID' => $this->User['user_id'],
						'ID' => $profileId
					)
				)->Fetch();

				if ($arProfileDb) {
					if ($arProfileDb['NAME'] != $profileTitle) {
						$arProfileFields = array(
							 'NAME' => $profileTitle,
							 'USER_ID' => $this->User['user_id'],
							 'PERSON_TYPE_ID' => $arProfileDb['PERSON_TYPE_ID']
						);

						if (!\CSaleOrderUserProps::Update($profileId, $arProfileFields)) {
							$this->addError('update_delivery_profile_prop_error');
						}
					}

					//получаем данные по свойствам профиля доставки
					$oProfileValuesDb = \CSaleOrderUserPropsValue::GetList(
						array(),
						array(
							'USER_PROPS_ID' => $profileId,
							'CODE' => array_keys($arProfileValues),
						),
						false,
						false,
						array('ID', 'NAME', 'USER_PROPS_ID', 'ORDER_PROPS_ID', 'PROP_CODE', 'VALUE')
					);

					while ($arProfileValueDb = $oProfileValuesDb->Fetch()) {
						if ($arProfileValueDb['VALUE'] != $arProfileValues[$arProfileValueDb['PROP_CODE']]) {
							if ($arProfileValues[$arProfileValueDb['PROP_CODE']]) {
								$arProfileValueFields = array(
									'USER_PROPS_ID'	=> $arProfileValueDb['USER_PROPS_ID'],
									'ORDER_PROPS_ID' => $arProfileValueDb['ORDER_PROPS_ID'],
									'NAME' => $arProfileValueDb['NAME'],
									'VALUE' => $arProfileValues[$arProfileValueDb['PROP_CODE']]
								);

								if (!\CSaleOrderUserPropsValue::Update($arProfileValueDb['ID'], $arProfileValueFields)) {
									$this->addError('update_delivery_profile_prop_error');
								}
							} else {
								if (!\CSaleOrderUserPropsValue::Delete($arProfileValueDb['ID'])) {
									$this->addError('update_delivery_profile_prop_error');
								}
							}
						}

						unset($arProfileValues[$arProfileProps['PROP_CODE']]);
					}

					if (!empty($arProfileValues)) {
						$this->loadOrderUserProps();

						foreach ($arProfileValues as $propCode => $propValue) {
							if ($propValue) {
								$arProfileValueFields = array(
									 'USER_PROPS_ID' => $profileId,
									 'ORDER_PROPS_ID' => $this->profileProps[$propCode]['ID'],
									 'NAME' => $this->profileProps[$propCode]['NAME'],
									 'VALUE' => $propValue
								);

								if (!\CSaleOrderUserPropsValue::Add($arProfileValueFields)) {
									$this->addError('add_delivery_profile_prop_error');
								}
							}
						}
					}

					if (empty($this->res['errors'])) {
						$arResult['feedback_text'] = 'Адрес доставки успешно обновлен';
					}
				} else {
					$this->addError('delivery_profile_not_found');
				}
			} else {
				$this->addError('required_params_missed');
			}
		} else {
			$this->addError('user_not_authorized');
		}

		return $arResult;
	}

	//добавление нового адреса доставки
	public function put($arInput)
	{
		\Bitrix\Main\Loader::includeModule('sale');

		$arResult = null;

		if ($this->User['user_id'] > 0) {
			if ($arInput['address']['title']
				&& $arInput['address']['city']['id']
				&& $arInput['address']['street_name']
				&& $arInput['address']['house']
			) {
				$profileTitle = $arInput['address']['title'];
				$arProfileValues = array(
					'DELIVERY_CITY' => $arInput['address']['city']['id'],
					'STREET' => $arInput['address']['street_name'],
					'HOME' => $arInput['address']['house'],
					'KVART' => $arInput['address']['flat'],
					'DETAILS' => $arInput['address']['details'],
				);

				$this->loadOrderUserProps();

				$arProfileFields = array(
					'NAME' => $profileTitle,
					'USER_ID' => $this->User['user_id'],
					'PERSON_TYPE_ID' => 1
				);

				if ($profileId = \CSaleOrderUserProps::Add($arProfileFields)) {
					foreach ($arProfileValues as $propCode => $propValue) {
						if ($propValue) {
							$arProfileValueFields = array(
								'USER_PROPS_ID' => $profileId,
								'ORDER_PROPS_ID' => $this->profileProps[$propCode]['ID'],
								'NAME' => $this->profileProps[$propCode]['NAME'],
								'VALUE' => $propValue
							);

							if (!\CSaleOrderUserPropsValue::Add($arProfileValueFields)) {
								$this->addError('add_delivery_profile_prop_error');
							}
						}
					}

					if (empty($this->res['errors'])) {
						$arResult['feedback_text'] = 'Адрес доставки успешно добавлен';
					}
				} else {
					$this->addError('add_delivery_profile_error');
				}
			} else {
				$this->addError('required_params_missed');
			}
		} else {
			$this->addError('user_not_authorized');
		}

		return $arResult;
	}

	//удаление заданного адреса доставки
	public function delete($arInput)
	{
		\Bitrix\Main\Loader::includeModule('sale');

		$arResult = null;

		if ($this->User['user_id'] > 0) {
			// проверяем существование ключей и формат
			if (
				$arInput['id']
				&& is_numeric($arInput['id'])
			) {
				if (\CSaleOrderUserProps::Delete($arInput['id'])) {
					$arResult['feedback_text'] = 'Адрес доставки успешно удален';
				} else {
					$this->addError('del_delivery_profile_error');
				}
			} else {
				$this->addError('required_params_missed');
			}
		} else {
			$this->addError('user_not_authorized');
		}

		return $arResult;
	}
}
