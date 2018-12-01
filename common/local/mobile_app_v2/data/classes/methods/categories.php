<?

use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use FourPaws\Enum\IblockType;
use FourPaws\Enum\IblockCode;

class categories extends \APIServer
{
	public function get($arInput)
	{
		$arResult['categories'] = array();

		\Bitrix\Main\Loader::includeModule('iblock');
        
		$parentSectionId = intval($arInput['id']);

		$oCache = \Bitrix\Main\Data\Cache::createInstance();
		$cacheTime = 60 * 60;
		$cacheId = md5("categories3|{$parentSectionId}");
		$cacheDir = '/categories3';

		if ($oCache->initCache($cacheTime, $cacheId, $cacheDir)) {
			$arResult = $oCache->getVars();
		} elseif ($oCache->startDataCache()) {
			$arLinks = array(
				1 => &$arResult['categories']
			);
			$corrDepthLevel = 0;
			$arFilter = array(
				'=IBLOCK_ID' => IblockUtils::getIblockId(IblockType::CATALOG, IblockCode::PRODUCTS),
				'=ACTIVE' => 'Y',
				'!XML_ID' => 323
			);

			if ($parentSectionId > 0) {
				$arSection = \Bitrix\Iblock\SectionTable::getList(array(
					'filter' => array('=ID' => $parentSectionId),
					'select' => array('LEFT_MARGIN', 'RIGHT_MARGIN', 'DEPTH_LEVEL'),
				))->fetch();

				if ($arSection) {
					$arFilter['>LEFT_MARGIN'] = $arSection['LEFT_MARGIN'];
					$arFilter['<RIGHT_MARGIN'] = $arSection['RIGHT_MARGIN'];
					$corrDepthLevel = $arSection['DEPTH_LEVEL'];
				}
			}

			$oSections = \Bitrix\Iblock\SectionTable::getList(array(
				'order' => array('LEFT_MARGIN' => 'ASC'),
				'filter' => $arFilter,
				'select' => array('ID', 'NAME', 'SORT', 'PICTURE', 'DEPTH_LEVEL', 'IBLOCK_SECTION_ID'),
			));

			while ($arSection = $oSections->fetch()) {
				$dl = $arSection['DEPTH_LEVEL'] - $corrDepthLevel;

				if ($dl != 1 && empty($arLinks[$dl])) {
					$arParentElem = end($arLinks[$dl - 1]);

					$arElem = array(
						'id' => $arParentElem['id'],
						'picture' => $arParentElem['picture'],
						'title' => 'Все',
						'SORT' => 0,
						'child' => array(),
					);

					$arLinks[$dl][] = &$arElem;
					$arLinks[$dl + 1] = &$arElem['child'];
					unset($arElem);
				}

				$arElem = array(
					'id' => $arSection['ID'],
					'picture' => ($arSection['PICTURE'] ? 'https://'.SITE_SERVER_NAME_API.\CFile::GetPath($arSection['PICTURE']) : ''),
					'title' => $arSection['NAME'],
					'SORT' => $arSection['SORT'],
					'child' => array(),
				);

				$arLinks[$dl][] = &$arElem;
				$arLinks[$dl + 1] = &$arElem['child'];
				unset($arElem);
			}

			// сортировка и удаление лишних данных
			function recursiveFormat(&$item)
			{
				if (isset($item['child'])) {
					if (!empty($item['child'])) {
						usort($item['child'], function ($a, $b) {
							if ($a['SORT'] > $b['SORT']) {
								return 1;
							} elseif ($a['SORT'] < $b['SORT']) {
								return -1;
							} elseif ($a['id'] > $b['id']) {
								return 1;
							} elseif ($a['id'] < $b['id']) {
								return -1;
							} else {
								return 0;
							}
						});

						array_walk($item['child'], 'recursiveFormat');
					} else {
						unset($item['child']);
					}
				}

				unset($item['SORT']);
			}

			array_walk($arResult['categories'], 'recursiveFormat');

			// вешаем тегированный кеш на ИБ товаров
			$GLOBALS['CACHE_MANAGER']->StartTagCache($cacheDir);
			$GLOBALS['CACHE_MANAGER']->RegisterTag('iblock_id_'.ROOT_CATALOG_ID);
			$GLOBALS['CACHE_MANAGER']->EndTagCache();

			$oCache->endDataCache($arResult);
		}

		return $arResult;
	}
}
