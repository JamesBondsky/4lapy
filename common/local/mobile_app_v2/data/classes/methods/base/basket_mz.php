<?
class basket_mz extends \basket
{
	public function clear()
	{
	}

	public function deleteBasketItem()
	{
	}

	public function getBasketItems()
	{
		if (is_null($this->items) && $this->order_id) {
			try {
				ini_set('default_socket_timeout', 5);

				$oSoapClient = new \SoapClient(API_ML_WSDL, array(
					'trace' => 1,
					'exceptions'=> 1,
					'connection_timeout' => 5
				));

				$sessionId = $oSoapClient->Authenticate(array(
					'login' => API_ML_LOGIN,
					'password' => API_ML_PASSWORD,
					'ip' => IP_ADDRESS,
					'innerLogin' => 'mob_api',
				))->AuthenticateResult->SessionId;
			} catch (Exception $e) {
			}

			if ($sessionId) {
				\Bitrix\Main\Loader::includeModule('iblock');

				$arCache = array();
				$oResponce = new \DomDocument('1.0', 'utf-8');

				try {
					$oResponce->loadXML(
						$oSoapClient->Execute(array(
							'sessionId' => $sessionId,
							'contractName' => 'cheque_items',
							'parameters' => array(
								array('Name' => 'cheque_id', 'Value' => $this->order_id),
							),
						))->ExecuteResult->Value
					);

					if ($oResponce->getElementsByTagName('ChequeItem')->length > 0) {
						foreach ($oResponce->getElementsByTagName('ChequeItem') as $oChequeItem) {
							$article = $oChequeItem->getElementsByTagName('ArticleNumber')->item('0')->textContent;

							if (!isset($arCache[$article])) {
								$arProduct = \Bitrix\Iblock\ElementTable::getList(array(
									'filter' => array(
										'=IBLOCK_ID' => \CIBlockTools::GetIBlockId('shop2015'),
										'=XML_ID' => $article,
									),
									'select' => array('ID'),
								))->fetch();

								$arCache[$article] = new \basket_item(array(
									'NAME' => $oChequeItem->getElementsByTagName('ArticleName')->item('0')->textContent,
									'ARTICLE' => $article,
									'PRICE' => round($oChequeItem->getElementsByTagName('Price')->item('0')->textContent, 2),
									'DISCOUNT_PRICE' => 0,
									'QUANTITY' => round($oChequeItem->getElementsByTagName('Quantity')->item('0')->textContent, 0, PHP_ROUND_HALF_UP),
								));

								if ($arProduct) {
									$arCache[$article]->setField('PRODUCT_ID', $arProduct['ID']);
								}
							}

							$this->items[] = $arCache[$article];
						}
					}
				} catch (Exception $e) {
				}
			}
		}

		return $this->items;
	}
}