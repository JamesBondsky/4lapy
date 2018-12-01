<?
class city_search extends APIServer
{
	/*
	search - строка поиска
	*/

	public function get($arInput)
	{
		if (!isset($arInput['search']) || strlen($arInput['search']) == 0) {
			$this->addError($this->ERROR['required_params_missed']);
			return null;
		} else {
			$search = filter_var($arInput['search'], FILTER_SANITIZE_STRING);
		}

		$arResult = array();

		if (!$this->hasErrors()) {
			require_once(\Bitrix\Main\Application::getDocumentRoot().'/bitrix/components/bitrix/sale.location.selector.search/class.php');

			$data = \CBitrixLocationSelectorSearchComponent::processSearchRequestV2(array(
				'select' => array('ID'),
				'filter' => array(
					'=PHRASE' => $search,
					'=NAME.LANGUAGE_ID' => 'ru',
					'=SITE_ID' => SITE_ID,
				),
				'version' => 2,
				'PAGE_SIZE' => 100,
				'PAGE' => 0
			));

			if (!empty($data['ITEMS'])) {
				$arCitiesId = array();

				$sCitiesId = '';

				foreach ($data['ITEMS'] as $arItem) {
					$arCitiesId[] = $arItem['ID'];
					$sCitiesId .= $arItem['ID'];
				}

				$oCache = \Bitrix\Main\Data\Cache::createInstance();
				$cacheTime = 60 * 60 * 24 * 1;
				$cacheId = md5("city_getlist|{$sCitiesId}");
				$cacheDir = '/city_getlist';

				if ($oCache->initCache($cacheTime, $cacheId, $cacheDir)) {
					$arResult = $oCache->getVars();
				} elseif ($oCache->startDataCache()) {

					$arResult = \city::getList(array(
						'filter' => array('=ID' => $arCitiesId)
					));

					$oCache->endDataCache($arResult);
				}

			}
		}

		return $arResult;
	}
}