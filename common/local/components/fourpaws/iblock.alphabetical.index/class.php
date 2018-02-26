<?php

use Bitrix\Iblock\ElementTable;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Entity\ExpressionField;
use Bitrix\Main\LoaderException;
use Bitrix\Main\Result;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
/**
 * Class CFourPawsIBlockAlphabeticalIndex
 * Компонент алфавитного указателя
 */

class CFourPawsIBlockAlphabeticalIndex extends \CBitrixComponent
{
    const DIGITS = 'digits';
    const SPECIAL = 'special';

    /** @var int $iIBlockId */
    private $iIBlockId = -1;
    /** @var array $extElementFilter */
    private $extElementFilter;

    /**
     * @param null|\CBitrixComponent $obParentComponent
     */
    public function __construct($obParentComponent = null)
    {
        parent::__construct($obParentComponent);
    }

    /**
     * @param array $arParams
     * @return array
     */
    public function onPrepareComponentParams($arParams)
    {
        $arParams['IBLOCK_TYPE'] = isset($arParams['IBLOCK_TYPE']) ? trim($arParams['IBLOCK_TYPE']) : '';
        $arParams['IBLOCK_CODE'] = isset($arParams['IBLOCK_CODE']) ? trim($arParams['IBLOCK_CODE']) : '';

        $arParams['CACHE_TIME'] = isset($arParams['CACHE_TIME']) ? (int)$arParams['CACHE_TIME'] : 43200 ;
        if ($arParams['CACHE_TYPE'] === 'N' || ($arParams['CACHE_TYPE'] === 'A' && \COption::GetOptionString('main', 'component_cache_on', 'Y') === 'N')) {
            $arParams['CACHE_TIME'] = 0;
        }

        $arParams['CHARS_COUNT'] = isset($arParams['CHARS_COUNT']) ? (int)$arParams['CHARS_COUNT'] : 1;
        $arParams['CHARS_COUNT'] = $arParams['CHARS_COUNT'] > 1 ? $arParams['CHARS_COUNT'] : 1;

        $arParams['TEMPLATE_NO_CACHE'] = isset($arParams['TEMPLATE_NO_CACHE']) && $arParams['TEMPLATE_NO_CACHE'] === 'Y' ? 'Y' : 'N';
        $arParams['LETTER_PAGE_URL'] = isset($arParams['LETTER_PAGE_URL']) ? trim($arParams['LETTER_PAGE_URL']) : '';

        $arParams['ELEMENT_FILTER_NAME'] = $arParams['ELEMENT_FILTER_NAME'] ?? '';

        return $arParams;
    }

    /**
     * @return array
     * @throws ArgumentException
     * @throws LoaderException
     */
    public function executeComponent()
    {
        $arParams =& $this->arParams;
        $arResult =& $this->arResult;

        if ($arParams['IBLOCK_TYPE'] === '' || $arParams['IBLOCK_CODE'] === '') {
            return [];
        }

        $arGroups = [];

        $sCacheDir = SITE_ID.'/'.basename(__DIR__);
        $sCacheDir = '/'.ltrim($sCacheDir, '/');
        $sCachePath = $sCacheDir;

        $sCacheId = md5(serialize([$arGroups, $this->getExtFilter()]));

        if ($this->startResultCache($arParams['CACHE_TIME'], $sCacheId, $sCachePath)) {
            $arParams['IBLOCK_ID'] = $this->getIBlockId();

            if ($arParams['IBLOCK_ID'] <= 0) {
                $this->abortResultCache();
                return $arResult;
            }

            if (defined('BX_COMP_MANAGED_CACHE') && is_object($GLOBALS['CACHE_MANAGER'])) {
                $GLOBALS['CACHE_MANAGER']->startTagCache($sCachePath);
                $GLOBALS['CACHE_MANAGER']->registerTag('iblock_id_'.$arParams['IBLOCK_ID']);
            }

            $obLettersResult = $this->getLettersList();
            $arResult = $obLettersResult->getData();

            if ($arParams['TEMPLATE_NO_CACHE'] !== 'Y') {
                $this->includeComponentTemplate();
            }

            if (defined('BX_COMP_MANAGED_CACHE') && is_object($GLOBALS['CACHE_MANAGER'])) {
                $GLOBALS['CACHE_MANAGER']->endTagCache();
            }

            $this->endResultCache();
        }

        if ($arParams['TEMPLATE_NO_CACHE'] === 'Y') {
            $this->includeComponentTemplate();
            //$this->templateCachedData = $this->GetTemplateCachedData();
        }

        return $arResult;
    }

    /**
     * @return int
     * @throws LoaderException
     */
    public function getIBlockId()
    {
        if ($this->iIBlockId < 0) {
            $this->iIBlockId = $this->getIBlockIdByCode($this->arParams['IBLOCK_CODE'], $this->arParams['IBLOCK_TYPE']);
        }

        return $this->iIBlockId;
    }

    /**
     * @param string $sIBlockCode
     * @param string $sIBlockType
     * @return int
     * @throws LoaderException
     */
    protected function getIBlockIdByCode($sIBlockCode, $sIBlockType = '')
    {
        $arFilter = [
            'CHECK_PERMISSIONS' => 'N',
            'CODE' => $sIBlockCode,
            'SITE_ID' => SITE_ID,
        ];
        if ($sIBlockType !== '') {
            $arFilter['TYPE'] = $sIBlockType;
        }
        $arIBlock = \CIBlock::GetList(['ID' => 'ASC'], $arFilter)->fetch();
        $iReturn = $arIBlock ? $arIBlock['ID'] : 0;

        return $iReturn;
    }

    /**
     * @return Result
     * @throws LoaderException
     * @throws ArgumentException
     */
    public function getLettersList()
    {
        $obResult = new Result();

        $iIBlockId = $this->getIBlockId();
        if (!$iIBlockId) {
            return $obResult;
        }

        $filter = [
            '=IBLOCK_ID' => $iIBlockId,
            '=ACTIVE' => 'Y',
        ];
        $filter = array_merge($filter, $this->getExtFilter());

        $arData = [];
        $arData['LIST'] = [];
        $arData['IS_NUM_EXISTS'] = 'N';
        $arData['IS_SPEC_EXISTS'] = 'N';
        $dbItems = ElementTable::getList(
            [
                'order' => [
                    'LETTER' => 'asc',
                ],
                'select' => [
                    'IBLOCK_ID',
                    new ExpressionField(
                        'LETTER',
                        'UPPER(LEFT(LTRIM(%s), '.$this->arParams['CHARS_COUNT'].'))',
                        'NAME'
                    ),
                ],
                'filter' => $filter,
                'group' => [
                    'LETTER',
                ],
            ]
        );
        while ($arItem = $dbItems->fetch()) {
            $arItem['LETTER_REDUCED'] = $this->getReducedLetter($arItem['LETTER']);
            switch ($arItem['LETTER_REDUCED']) {
                case static::DIGITS:
                    $arData['IS_NUM_EXISTS'] = 'Y';
                    break;

                case static::SPECIAL:
                    $arData['IS_SPEC_EXISTS'] = 'Y';
                    break;
            }

            $arItem['LETTER_PAGE_URL'] = '';
            if ($this->arParams['LETTER_PAGE_URL']) {
                $arItem['LETTER_PAGE_URL'] = str_replace(
                    ['#LETTER#', '#LETTER_REDUCED#', '#SITE_DIR#', '#SERVER_NAME#', '#IBLOCK_ID#', '#IBLOCK_CODE#'],
                    [urlencode($arItem['LETTER']), urlencode($arItem['LETTER_REDUCED']), SITE_DIR, SITE_SERVER_NAME, $iIBlockId, $this->arParams['IBLOCK_CODE']],
                    $this->arParams['LETTER_PAGE_URL']
                );
            }

            $arData['LIST'][$arItem['LETTER']] = $arItem;
        }
        $obResult->setData($arData);

        return $obResult;
    }

    /**
     * @param string $sItemLetter
     * @return string
     */
    protected function getReducedLetter($sItemLetter)
    {
        $sReturn = $sItemLetter;
        $sFirstLetter = substr($sItemLetter, 0, 1);
        if (preg_match('#[^\p{L}]+#'.BX_UTF_PCRE_MODIFIER, $sFirstLetter)) {
            // символы, не являющиеся буквами
            if (preg_match('#[0-9]+#'.BX_UTF_PCRE_MODIFIER, $sFirstLetter)) {
                $sReturn = static::DIGITS;
            } else {
                $sReturn = static::SPECIAL;
            }
        }

        return $sReturn;
    }

    protected function getExtFilter()
    {
        if (!isset($this->extElementFilter)) {
            $this->extElementFilter = [];
            if ($this->arParams['ELEMENT_FILTER_NAME'] !== '') {
                $this->extElementFilter = $GLOBALS[$this->arParams['ELEMENT_FILTER_NAME']] ?? [];
                if (!is_array($this->extElementFilter)) {
                    $this->extElementFilter = [];
                }
            }
        }

        return $this->extElementFilter;
    }
}
