<?php

class street_list extends APIServer
{
	/*
	search_term - строка поиска
	*/

	public function get($arInput)
	{
		$arResult = null;

		if (!isset($arInput['id']) || intval($arInput['id']) <= 0) {
			$this->addError($this->ERROR['required_params_missed']);
			return null;
		} else {
			$cityId = filter_var($arInput['id'], FILTER_VALIDATE_INT);
			if (strlen($arInput['search_term']) > 0) {
				$search = filter_var($arInput['search_term'], FILTER_SANITIZE_STRING);
			}
			$page = filter_var($arInput['page'], FILTER_VALIDATE_INT);
			$page = ($page !== false && $page >= 1) ? $page : 1;
		}

		if (!$this->hasErrors()) {
			$arResult = array();

			if (strlen($search)) {
				require_once(\Bitrix\Main\Application::getDocumentRoot().'/bitrix/components/bitrix/sale.location.selector.search/class.php');

				$data = \CBitrixLocationSelectorSearchComponent::processSearchRequestV2(array(
					'select' => array('ID'),
					'filter' => array(
						'=PHRASE' => $search,
						'=NAME.LANGUAGE_ID' => 'ru',
						'=SITE_ID' => SITE_ID,
						'=PARENT_ID' => $cityId
					),
					'version' => 2,
					'PAGE_SIZE' => 100,
					'PAGE' => $page - 1
				));

				if (!empty($data['ITEMS'])) {
					$arStreetsId = array();

					foreach ($data['ITEMS'] as $arItem) {
						$arStreetsId[] = $arItem['ID'];
					}
					$oCache = \Bitrix\Main\Data\Cache::createInstance();
					$cacheTime = 60 * 60 * 24 * 1;
					$sStreetsId = implode(',', $arStreetsId);
					$cacheId = md5("streets_getlist|{$sStreetsId}");
					$cacheDir = '/streets_getlist';

					if ($oCache->initCache($cacheTime, $cacheId, $cacheDir)) {
						$arResult = $oCache->getVars();
					} elseif ($oCache->startDataCache()) {

						$arResult = \street::getList(array(
							'filter' => array(
								'=ID' => $arStreetsId,
								'=PARENT_ID' => $cityId
							),
							'page' => $page
						));

						$oCache->endDataCache($arResult);
					}

				}
			} else {
				$oCache = \Bitrix\Main\Data\Cache::createInstance();
				$cacheTime = 60 * 60 * 24 * 1;
				$cacheId = md5("streets_list|{$cityId}|{$page}");
				$cacheDir = '/streets_list';

				if ($oCache->initCache($cacheTime, $cacheId, $cacheDir)) {
					$arResult = $oCache->getVars();
				} elseif ($oCache->startDataCache()) {

					$arResult = \street::getList(array(
						'filter' => array(
							'=PARENT_ID' => $cityId
						),
						'page' => $page
					));

					$oCache->endDataCache($arResult);
				}
			}
		} 

		return $arResult;
	}

}
