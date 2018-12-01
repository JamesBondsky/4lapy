<?

class goods_search extends \APIServer
{
	public function get($arInput)
	{
		$arResult['goods'] = array();

		if (!isset($arInput['request'])) {
			$this->addError('required_params_missed');
		} else {
			$reqStr = $arInput['request'];
		}

		if (!$this->hasErrors()) {
			$oCache = \Bitrix\Main\Data\Cache::createInstance();
			$cacheTime = 60 * 5;
			$cacheId = md5("goods_search|{$reqStr}");
			$cacheDir = '/goods_search';

			if ($oCache->initCache($cacheTime, $cacheId, $cacheDir)) {
				$arResult = $oCache->getVars();
			} elseif ($oCache->startDataCache()) {
				\Bitrix\Main\Loader::includeModule('search');
				\Bitrix\Main\Loader::includeModule('iblock');

				// выполняем поиск по фразе только в каталоге товаров и выводим 50 результатов
				$oSearch = new \CSearch;

				$oSearch->SetOptions(array(
					'ERROR_ON_EMPTY_STEM' => false,
					'NO_WORD_LOGIC' => false,
				));

				$arFilter = array(
					'SITE_ID' => (\Bitrix\Main\Application::getInstance()->getContext()->getSite() ?: 's1'),
					'QUERY' => $reqStr,
					// 'MODULE_ID' => 'iblock',
					// 'PARAM2' => array(ROOT_CATALOG_ID)
				);
				$aSort = array(
					'CUSTOM_RANK' => 'DESC',
					'TITLE_RANK' => 'DESC',
					'RANK' => 'DESC',
					'DATE_CHANGE' => 'DESC',
				);
				$exFILTER = array(
					'=MODULE_ID' => 'iblock',
					'PARAM1' => 'content',
					'PARAM2' => array(ROOT_CATALOG_ID),
					/*'STEMMING' => false*/
				);

				$oSearch->Search($arFilter, $aSort, $exFILTER, false, true);

				if (!$oSearch->selectedRowsCount()) {
					$exFILTER['STEMMING'] = false;
					$oSearch->Search($arFilter, $aSort, $exFILTER);
				}

				$oSearch->NavStart(API_SEARCH_RES_MAX_COUNT, false);

				if ($oSearch->errorno == 0) {
					while ($arSearchItem = $oSearch->GetNext()) {
						$arFindProdId[] = $arSearchItem['ITEM_ID'];
					}
				}

				if (count($arFindProdId) > 0) {
					//получаем список ID товаров по заданным параметрам
					$oProductsList = \CIBlockElement::GetList(
						array('SORT' => 'ASC', 'NAME' => 'ASC'),
						array(
							'IBLOCK_ID' => ROOT_CATALOG_ID,
							'ACTIVE' => 'Y',
							'ID' => $arFindProdId
						),
						false,
						false,
						array('ID', 'NAME')
					);

					while ($arProduct = $oProductsList->GetNext()) {
						$arProdId[] = $arProduct['ID'];
					}

					$oGoodsList = new \goods_list;

					//тащим инфу по выбранным товарам
					if ($arProdInfoList = $oGoodsList->GetProdInfo($arProdId)) {
						foreach ($arProdInfoList as $iProdId => $arProdInfo) {
							//получаем количество бонусов по позиции
							$arProductBonus = $oGoodsList->GetProductBonus($arProdInfo['price'],$arProdInfo);

							//округляем хз как
							$arProdInfo['bonus_user'] = $arProductBonus['bonus_user'];
							$arProdInfo['bonus_all'] = $arProductBonus['bonus_all'];

							//формируем результирующий массив
							$arResult['goods'][] = $arProdInfo;
						}
					} else {
						$this->addError('error_get_prod_info');
					}
				}

				$oCache->endDataCache($arResult);
			}
		}

		return $arResult;
	}
}
