<?

class promo_baner extends \APIServer
{

	public function get($arInput)
	{
		\Bitrix\Main\Loader::includeModule('advertising');

		$bannerType = 'mobile_promo';

		if (!$this->hasErrors()) {
			// тянем баннеры определенного типа с сортировкой по весу

			$oCache = \Bitrix\Main\Data\Cache::createInstance();
			$cacheTime = 60 * 1;
			$cacheId = md5("promo_baner_|{$bannerType}");
			$cacheDir = '/promo_baner_';

			if ($oCache->initCache($cacheTime, $cacheId, $cacheDir)) {
				$arResult = $oCache->getVars();
			} elseif ($oCache->startDataCache()) {
				$oBanners = \CAdvBanner::GetList(
					$by = 's_weight',
					$order = 'asc',
					array(
						'ACTIVE' => 'Y',
						'STATUS_SID' => 'PUBLISHED',
						'TYPE_SID' => $bannerType,
						'TYPE_SID_EXACT_MATCH' => 'Y'
					),
					$bIsFiltered,
					'N'
				);

				while ($arBanner = $oBanners->Fetch()) {
					unset($targetUrl);

					if ($arBanner['GROUP_SID'] == 'goods') {
						$methodName = 'goods_item';
						$arQueryData = array(
							'token' => $this->User['token'],
							'id' => $arBanner['URL']
						);
					} elseif ($arBanner['GROUP_SID'] == 'goods_list') {
						$methodName = 'goods_item_list';
						$arQueryData = array(
							'token' => $this->User['token'],
							'id' => explode(";", $arBanner['URL'])
						);
					} elseif ($arBanner['GROUP_SID'] == 'catalog') {
						$methodName = 'categories';
						$arQueryData = array(
							'token' => $this->User['token'],
							'id' => $arBanner['URL']
						);
					} elseif ($arBanner['GROUP_SID'] == 'news') {
						$methodName = 'info';
						$arQueryData = array(
							'token' => $this->User['token'],
							'type' => 'news',
							'info_id' => $arBanner['URL'],
							'city_id' => ($arInput['city_id'] and strlen($arInput['city_id']) > 0)?$arInput['city_id']:null
						);
					} elseif ($arBanner['GROUP_SID'] == 'action') {
						$methodName = 'info';
						$arQueryData = array(
							'token' => $this->User['token'],
							'type' => 'action',
							'info_id' => $arBanner['URL'],
							'city_id' => ($arInput['city_id'] and strlen($arInput['city_id']) > 0)?$arInput['city_id']:null
						);
					} else {
						$methodName = false;
						$targetUrl = $arBanner['URL'];
					}

					if ($methodName) {
						$targetUrlTemp = 'https://'.SITE_SERVER_NAME_API.'/mobile-api-v2/#METHOD_NAME#/?';

						$targetUrl = str_replace("#METHOD_NAME#", $methodName, $targetUrlTemp);
						$targetUrl .= http_build_query($arQueryData);
					}


					$arResult['banners'][] = array(
						'id' => $arBanner['ID'],
						'picture' => ($arBanner['IMAGE_ID']) ? 'https://'.SITE_SERVER_NAME_API.CFile::GetPath($arBanner['IMAGE_ID']) : '',
						'delay' => API_SHOW_BANNER_TIME,
						'title' => ($arBanner['NAME'])?:'',
						'type' => ($arBanner['GROUP_SID'])?:'',
						'target' => ($targetUrl)?:'',
						'target_alt' => ($arBanner['URL'])?:'',
					);
				}

				$oCache->endDataCache($arResult);
			}
		}

		return $arResult;
	}
}
