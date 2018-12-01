<?

use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use FourPaws\Enum\IblockType;
use FourPaws\Enum\IblockCode;

class social extends \APIServer
{
	public function get($arInput)
	{
		\Bitrix\Main\Loader::includeModule('iblock');

		$oCache = \Bitrix\Main\Data\Cache::createInstance();
		$cacheTime = 60 * 60 * 24 * 7;
		$cacheId = md5('social');
		$cacheDir = '/social';

		if ($oCache->initCache($cacheTime, $cacheId, $cacheDir)) {
			$arResult = $oCache->getVars();
		} elseif ($oCache->startDataCache()) {
			// получаем список соцсетей
			$oSocials = \CIBlockElement::GetList(
				array('SORT' => 'ASC'),
				array(
					'IBLOCK_ID' => IblockUtils::getIblockId(IblockType::REFERENCE_BOOKS, IblockCode::SOCIALS),
					'ACTIVE' => 'Y'
				),
				false,
				false,
				array('ID', 'NAME', 'CODE', 'PROPERTY_IOS_LINK', 'PROPERTY_ANDROID_LINK')
			);

			while ($arSocial = $oSocials->Fetch()) {
				$arResult['social'][$arSocial['CODE']] = array(
					'web' => $arSocial['NAME'],
					'ios' => ($arSocial['PROPERTY_IOS_LINK_VALUE'] ?: ''),
					'android' => ($arSocial['PROPERTY_ANDROID_LINK_VALUE'] ?: '')
				);
			}

			// вешаем тегированный кеш на ИБ магазинов
			$GLOBALS['CACHE_MANAGER']->StartTagCache($cacheDir);
			$GLOBALS['CACHE_MANAGER']->RegisterTag('iblock_id_'.IblockUtils::getIblockId(IblockType::REFERENCE_BOOKS, IblockCode::SOCIALS));
			$GLOBALS['CACHE_MANAGER']->EndTagCache();

			$oCache->endDataCache($arResult);
		}

		return $arResult;
	}
}
