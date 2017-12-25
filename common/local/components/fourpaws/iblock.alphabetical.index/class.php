<?if(!defined('B_PROLOG_INCLUDED')||B_PROLOG_INCLUDED!==true)die();
/**
 * Class CFourPawsIBlockAlphabeticalIndex
 * Компонент алфавитного указателя
 *
 * @updated: 25.12.2017
 */

class CFourPawsIBlockAlphabeticalIndex extends \CBitrixComponent {

	/**
	 * @param \CBitrixComponent|null $obParentComponent
	 */
	public function __construct($obParentComponent = null) {
		parent::__construct($obParentComponent);
	}

	/**
	 * @param array $arParams
	 * @return array
	 */
	public function onPrepareComponentParams($arParams) {
		$arParams['IBLOCK_TYPE'] = isset($arParams['IBLOCK_TYPE']) ? trim($arParams['IBLOCK_TYPE']) : '';
		$arParams['IBLOCK_CODE'] = isset($arParams['IBLOCK_CODE']) ? trim($arParams['IBLOCK_CODE']) : '';

		$arParams['CACHE_TIME'] = isset($arParams['CACHE_TIME']) ? intval($arParams['CACHE_TIME']) : 43200 ;
		if($arParams['CACHE_TYPE'] === 'N' || ($arParams['CACHE_TYPE'] === 'A' && \COption::GetOptionString('main', 'component_cache_on', 'Y') === 'N')) {
			$arParams['CACHE_TIME'] = 0;
		}

		$arParams['CHARS_COUNT'] = isset($arParams['CHARS_COUNT']) ? intval($arParams['CHARS_COUNT']) : 1;
		$arParams['CHARS_COUNT'] = $arParams['CHARS_COUNT'] > 1 ? $arParams['CHARS_COUNT'] : 1; 

		$arParams['TEMPLATE_NO_CACHE'] = isset($arParams['TEMPLATE_NO_CACHE']) && $arParams['TEMPLATE_NO_CACHE'] === 'Y' ? 'Y' : 'N';
		$arParams['LETTER_PAGE_URL'] = isset($arParams['LETTER_PAGE_URL']) ? trim($arParams['LETTER_PAGE_URL']) : '';

		return $arParams;
	}

	/**
	 * @return void
	 */
	public function executeComponent() {
		$arParams =& $this->arParams;
		$arResult =& $this->arResult;

		if(!strlen($arParams['IBLOCK_TYPE']) || !strlen($arParams['IBLOCK_CODE'])) {
			return false;
		}

		$arGroups = array();

		$sCacheDir = SITE_ID.'/'.basename(__DIR__);
		$sCacheDir = '/'.ltrim($sCacheDir, '/');
		$sCachePath = $sCacheDir; 

		$sCacheId = md5(serialize(array($arGroups)));

		if($this->startResultCache($arParams['CACHE_TIME'], $sCacheId, $sCachePath)) {
			if(!\Bitrix\Main\Loader::includeModule('iblock')) {
				$this->abortResultCache();
				return $arResult;
			}

			$arIBlock = \CIBlock::GetList(
				array(
					'ID' => 'ASC'
				),
				array(
					'CHECK_PERMISSIONS' => 'N',
					'CODE' => $arParams['IBLOCK_CODE'],
					'SITE_ID' => SITE_ID,
					'TYPE' => $arParams['IBLOCK_TYPE']
				)
			)->fetch();
			$arParams['IBLOCK_ID'] = $arIBlock ? $arIBlock['ID'] : 0;

			if($arParams['IBLOCK_ID'] <= 0) {
				$this->abortResultCache();
				return $arResult;
			}

			$arResult['LIST'] = array();
			$arResult['IS_NUM_EXISTS'] = 'N';
			$arResult['IS_SPEC_EXISTS'] = 'N';

			if(defined('BX_COMP_MANAGED_CACHE') && is_object($GLOBALS['CACHE_MANAGER'])) {
				$GLOBALS['CACHE_MANAGER']->StartTagCache($sCachePath);
				$GLOBALS['CACHE_MANAGER']->RegisterTag('iblock_id_'.$arParams['IBLOCK_ID']);
			}

			$dbItems = \Bitrix\Iblock\ElementTable::getList(
				array(
					'order' => array(
						'LETTER' => 'asc'
					),
					'select' => array(
						'IBLOCK_ID',
						new \Bitrix\Main\Entity\ExpressionField(
							'LETTER',
							'UPPER(LEFT(LTRIM(%s), '.$arParams['CHARS_COUNT'].'))',
							'NAME'
						),
					),
					'filter' => array(
						'=IBLOCK_ID' => $arParams['IBLOCK_ID'],
						'=ACTIVE' => 'Y',
					),
					'group' => array(
						'LETTER'
					),
				)
			);
			while($arItem = $dbItems->fetch()) {
				// символы, не являющиеся буквами
				$sFirstLetter = substr($arItem['LETTER'], 0, 1);
				$arItem['LETTER_REDUCED'] = $arItem['LETTER'];
				if(preg_match('#[^\p{L}]+#'.BX_UTF_PCRE_MODIFIER, $sFirstLetter)) {
					if(preg_match('#[0-9]+#'.BX_UTF_PCRE_MODIFIER, $sFirstLetter)) {
						$arItem['LETTER_REDUCED'] = 'digits';
						$arResult['IS_NUM_EXISTS'] = 'Y';
					} else {
						$arItem['LETTER_REDUCED'] = 'special';
						$arResult['IS_SPEC_EXISTS'] = 'Y';
					}
				}

				$arItem['LETTER_PAGE_URL'] = '';
				if($arParams['LETTER_PAGE_URL']) {
					$arItem['LETTER_PAGE_URL'] = str_replace(
						array('#LETTER#', '#LETTER_REDUCED#', '#SITE_DIR#', '#SERVER_NAME#', '#IBLOCK_ID#', '#IBLOCK_CODE#'),
						array(urlencode($arItem['LETTER']), urlencode($arItem['LETTER_REDUCED']), SITE_DIR, SITE_SERVER_NAME, $arParams['IBLOCK_ID'], $arParams['IBLOCK_CODE']),
						$arParams['LETTER_PAGE_URL']
					);
				}

				$arResult['LIST'][$arItem['LETTER']] = $arItem;
			}

			if($arParams['TEMPLATE_NO_CACHE'] !== 'Y') {
				$this->IncludeComponentTemplate();
			}

			if(defined('BX_COMP_MANAGED_CACHE') && is_object($GLOBALS['CACHE_MANAGER'])) {
				$GLOBALS['CACHE_MANAGER']->EndTagCache();
			}

			$this->endResultCache();
		}

		if($arParams['TEMPLATE_NO_CACHE'] === 'Y') {
			$this->IncludeComponentTemplate();
			//$this->templateCachedData = $this->GetTemplateCachedData();
		}

		return $arResult;
	}
}
