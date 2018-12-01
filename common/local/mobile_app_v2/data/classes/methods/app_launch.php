<?

use FourPaws\MobileApiOldBundle\Push\SessionTable;
use FourPaws\MobileApiOldBundle\Card;

class app_launch extends \APIServer
{
	public function get($arInput)
	{
		// log_(array('Заходим', $arInput));
		if (($card = $arInput['card']) && ($userId = $this->getUserId())) {
			Card::SetDataCard($card, $userId);
		}
		// log_(array('Получаем карту', $card));
		if($userId){
			if(!MyCUser::checkMakingOrderInMPGroups($userId))
			{
				if(MyCUser::checkOrdersFromMP($userId))
				{
					MyCUser::setMakingOrderInMP($userId);
				}
				else
				{
					MyCUser::setNotMakingOrderInMP($userId);
				}
			}
		}
		// log_(array('Кидаем в нужные группы', $userId));
		if ($cardNumber = $this->User['UF_DISC']) {
			// записываем в манзану наличие у юзера приложения и дату последнего входа в него
			try {
				ini_set('default_socket_timeout', 5);

				$oSoapClient = new \SoapClient(API_ML_WSDL, array(
					'trace' => 1,
					'exceptions'=> 1,
					"connection_timeout" => 5
				));

				$oAuth = $oSoapClient->Authenticate(array(
					'login' => API_ML_LOGIN,
					'password' => API_ML_PASSWORD,
					'ip' => IP_ADDRESS,
					'innerLogin' => 'mob_api',
				));
				$sessionId = $oAuth->AuthenticateResult->SessionId;
			} catch(Exception $e) {
			}

			if ($sessionId) {
				$oResponse = new \CDataXML();

				try {
					$oResponse->LoadString(
						$oSoapClient->Execute(array(
							'sessionId' => $sessionId,
							'contractName' => 'search_cards_by_number',
							'parameters' => array(
								array('Name' => 'cardnumber', 'Value' => $cardNumber),
							)
						))->ExecuteResult->Value
					);
					$contactId = $oResponse->SelectNodes('/Cards/Card/contactid')->textContent();
				} catch (Exception $e) {
				}
				// log_(array('К манзане вроде подключились', $contactId));
				if ($contactId) {
					$oDateTime = new \Bitrix\Main\Type\DateTime();

					try {
						$oSoapClient->Execute(array(
							'sessionId' => $sessionId,
							'contractName' => 'contact_update',
							'parameters' => array(
								array('Name' => 'contactid', 'Value' => $contactId),
								array('Name' => 'ff_mobile_app', 'Value' => 1),
								array('Name' => 'ff_mobile_app_date', 'Value' => $oDateTime->format('c')),
							)
						));
					} catch (Exception $e) {
					}
				}
				//тут будем запрашивать чеки по карте
				CModule::IncludeModule("iblock");
				$arLoadProductArray = Array(
					"IBLOCK_ID"      => 54,
					"NAME"           => $cardNumber,
					"CODE"           => $cardNumber,
					"ACTIVE"         => "Y",
					);

				$REQUEST_ID = $el->Add($arLoadProductArray);
				//!тут будем запрашивать чеки по карте
			}
		}
		// log_(array('Ну вроде и все...', $cardNumber));

		//Давайте попробуем актуализировать fuser пользователя
		$arFuser0 = SessionTable::getList(array(
			'filter' => array('=USER_ID' => $this->getUserId()),
			'select' => array('FUSER_ID'),
		))->fetch();

		CModule::IncludeModule("sale");
		$arFUser = CSaleUser::GetList(array('USER_ID' => $this->getUserId()));

		\APIServer::createUserSession($this->getUserId(), null, $arInput['token'], ($arFUser['ID'])?:$arFuser0['FUSER_ID']);
	}
}
