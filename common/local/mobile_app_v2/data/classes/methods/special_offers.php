<?
use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use FourPaws\Enum\IblockType;
use FourPaws\Enum\IblockCode;

class special_offers extends \APIServer
{
	public function get($arInput)
	{
		$pageNum = intval($arInput['page']);
		$pageNum = ($pageNum ?: 1);
		$pageCount = intval($arInput['count']);
		$pageCount = ($pageCount ?: 10);

		$arResult = array(
			'total_items' => 0,
			'total_pages' => 0,
			'goods' => array()
		);

		$oCache = \Bitrix\Main\Data\Cache::createInstance();
		$cacheTime = 60 * 60 * 24 * 7;
		$cacheTime = 0;
		$cacheId = md5("special_offers|{$pageNum}|{$pageCount}");
		$cacheDir = '/special_offers';

		if ($oCache->initCache($cacheTime, $cacheId, $cacheDir)) {
			$arResult = $oCache->getVars();
		} elseif ($oCache->startDataCache()) {
			\Bitrix\Main\Loader::includeModule('iblock');

			// получаем список ID товаров по заданным параметрам
			$arProductsId = array();

			$oElements = \CIBlockElement::GetList(
				array('SORT' => 'ASC', 'NAME' => 'ASC'),
				array(
					'IBLOCK_ID' => IblockUtils::getIblockId(IblockType::CATALOG, IblockCode::OFFERS),
					'ACTIVE' => 'Y',
					'!PROPERTY_IS_SALE_VALUE' => false
				),
				false,
				array('iNumPage' => $pageNum, 'nPageSize' => $pageCount),
				array('ID', 'NAME')
			);

			while ($arElement = $oElements->GetNext()) {
				$arProductsId[] = $arElement['ID'];
			}
            
			if (count($arProductsId) > 0) {
				$oGoodsList = new \goods_list;

				// тащим инфу по выбранным товарам
				if ($arProdInfoList = $oGoodsList->GetProdInfo($arProductsId)) {
                    var_dump($arProdInfoList);
                    die();
                        
					foreach ($arProdInfoList as $iProdId => $arProdInfo) {
                        
						//получаем количество бонусов по позиции
						$arProductBonus = $oGoodsList->GetProductBonus($arProdInfo['price'],$arProdInfo);

						// округляем хз как
						$arProdInfo['bonus_user'] = ceil($arProductBonus['bonus_user']);
						$arProdInfo['bonus_all'] = ceil($arProductBonus['bonus_all']);

						// формируем результирующий массив
						$arResult['goods'][] = $arProdInfo;

						$arResult['total_items'] = $oProductsList->NavRecordCount;
						$arResult['total_pages'] = $oProductsList->NavPageCount;
					}
				} else {
					$this->addError('error_get_prod_info');
				}
			}

			// вешаем тегированный кеш на ИБ товаров
			$GLOBALS['CACHE_MANAGER']->StartTagCache($cacheDir);
			$GLOBALS['CACHE_MANAGER']->RegisterTag('iblock_id_'.ROOT_CATALOG_ID);
			$GLOBALS['CACHE_MANAGER']->EndTagCache();

			$oCache->endDataCache($arResult);
		}

		return $arResult;
	}
}
