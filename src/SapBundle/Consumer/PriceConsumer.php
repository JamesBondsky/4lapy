<?php

namespace FourPaws\SapBundle\Consumer;

use FourPaws\SapBundle\Dto\In\Prices\Prices;
use FourPaws\SapBundle\Dto\In\Prices\Item;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;
use Bitrix\Main\SystemException;
use Bitrix\Main\Result;
use Bitrix\Main\Error;
use Bitrix\Catalog;

class PriceConsumer implements ConsumerInterface
{
    const BASE_PRICE_REGION_CODE = 'IM01';

    /** @var string $defaultCurrencyCode */
    protected $defaultCurrencyCode = 'RUB';

    /** @var string $recalcPrices */
    protected $recalcPrices = true;

    /** @var array $offersCache */
    protected $offersCache = [];

    /** @var int $maxOffersCacheSize */
    protected $maxOffersCacheSize = 100;

    /**
     * @param Prices $prices
     *
     * @return bool
     */
    public function consume($prices): bool
    {
        if (!$this->support($prices)) {
            return false;
        }

        if (!$prices->getUploadToIm()) {
            return false;
        }

        foreach ($prices->getItems() as $obPriceItem) {
            $obOfferElementData = $this->getOfferElementDataByXmlId($obPriceItem->getOfferXmlId());
            if (!$obOfferElementData->isSuccess()) {
                continue;
            }
            $obSetOffersResult = $this->setOfferPrices($obOfferElementData, $obPriceItem, $prices->getRegionCode());
        }

// !!!
// непонятно при каких условиях какой результат возвращать
// !!!
        return true;
    }

    /**
     * @param $data
     *
     * @return bool
     */
    public function support($data): bool
    {
        return \is_object($data) && $data instanceof Prices;
    }

    /**
     * @return void
     */
    protected function incModules()
    {
        if (!\Bitrix\Main\Loader::includeModule('iblock')) {
            throw new SystemException('Module iblock is not installed');
        }

        if (!\Bitrix\Main\Loader::includeModule('catalog')) {
            throw new SystemException('Module catalog is not installed');
        }
    }

    /**
     * @return int
     */
    protected function getOffersIBlockId(): int
    {
        static $iIBlockId = -1;
        if ($iIBlockId < 0) {
            $this->incModules();
            $arTmpItem = \CIBlock::GetList(
                [
                    'ID' => 'ASC'
                ],
                [
                    'CODE' => IblockCode::OFFERS,
                    'TYPE' => IblockType::CATALOG,
                    'CHECK_PERMISSIONS' => 'N',
                ]
            )->fetch();
            $iIBlockId = $arTmpItem['ID'] ? $arTmpItem['ID'] : 0;
        }
        return $iIBlockId;
    }

    /**
     * @param string $sXmlId
     *
     * @return Result
     */
    protected function getOfferElementDataByXmlId($sXmlId): Result
    {
        $obResult = new Result();

        $sXmlId = trim($sXmlId);
        if (!strlen($sXmlId)) {
            $obResult->addError(new Error('Не задан внешний код торгового предложения', 100));
        }

        if ($obResult->isSuccess()) {
            $arItem = $this->getOfferElementByXmlId($sXmlId);
            if ($arItem) {
                $obResult->setData(
                    [
                        'ID' => $arItem['ID'],
                        'IBLOCK_ID' => $arItem['IBLOCK_ID'],
                    ]
                );
            } else {
                $obResult->addError(new Error('Не найден элемент торгового предложения по внешнему коду: '.$sXmlId, 200));
            }
        }

        return $obResult;
    }

    /**
     * @param string $sXmlId
     *
     * @return array
     */
    private function getOfferElementByXmlId($sXmlId): array
    {
        $arReturn = [];
        if (!isset($this->offersCache[$sXmlId])) {
            $this->incModules();
            $dbItems = \CIBlockElement::GetList(
                [
                    'ID' => 'ASC'
                ],
                [
                    'IBLOCK_ID' => $this->getOffersIBlockId(),
                    '=XML_ID' => $sXmlId,
                ],
                false,
                false,
                [
                    'ID', 'IBLOCK_ID',
                ]
            );
            if ($arItem = $dbItems->fetch()) {
                $arReturn = $arItem;
            }

            $this->offersCache[$sXmlId] = $arReturn;
            
            if ($this->maxOffersCacheSize > 0 && count($this->offersCache) > $this->maxOffersCacheSize) {
                $this->offersCache = array_slice($this->offersCache, 1, null, true);
            }
        } else {
            $arReturn = $this->offersCache[$sXmlId];
        }

        return $arReturn;
    }

    /**
     * @param Result $obOfferElementData
     * @param Item $obPriceItem
     * @param string $sRegionCode
     * @param bool $bGetExtResult
     *
     * @return Result
     */
    protected function setOfferPrices(Result $obOfferElementData, Item $obPriceItem, $sRegionCode, $bGetExtResult = true): Result
    {
        $obResult = new Result();

        $arResultData = [];

        $arOfferElementData = $obOfferElementData->getData();
        if (empty($arOfferElementData['ID'])) {
            $obResult->addError(new Error('Не задан id торгового предложения', 100));
        }

        if ($obResult->isSuccess()) {
            $this->incModules();
            $iProductId = $arOfferElementData['ID'];
            $sCurrency = $this->defaultCurrencyCode;

            $arDelPrices = [];

            $arSetPricesList = [];
            $iTmpPriceTypeId = $this->getPriceTypeIdByXmlId($sRegionCode);
            if ($iTmpPriceTypeId) {
                $arSetPricesList[$iTmpPriceTypeId] = [
                    'PRODUCT_ID' => $iProductId,
                    'CATALOG_GROUP_ID' => $iTmpPriceTypeId,
                    'PRICE' => doubleval($obPriceItem->getRetailPrice()),
                    'CURRENCY' => $sCurrency,
                ];
            }

            $arSetPropsList = [];
            $arSetPropsList['PRICE_ACTION'] = doubleval($obPriceItem->getActionPrice());
            $arSetPropsList['PRICE_ACTION'] = $arSetPropsList['PRICE_ACTION'] > 0 ? $arSetPropsList['PRICE_ACTION'] : '';
            $arSetPropsList['COND_FOR_ACTION'] = trim($obPriceItem->getPriceType());
            $arSetPropsList['COND_VALUE'] = doubleval($obPriceItem->getDiscountValue());
            $arSetPropsList['COND_VALUE'] = $arSetPropsList['COND_VALUE'] == 0 ? '' : $arSetPropsList['COND_VALUE'];

            if ($arSetPricesList) {
                // обновление существующих цен
                $dbItems = \CPrice::GetListEx(
                    [
                        'ID' => 'ASC'
                    ],
                    [
                        'PRODUCT_ID' => $iProductId,
                        'CURRENCY' => $sCurrency,
// !!!
// непонятно что делать с ценами, зависящими от количества, если таковые будут
// !!!
                        'QUANTITY_FROM' => false,
                        'QUANTITY_TO' => false,
// !!!
// непонятно что делать с наценками, если таковые будут
// !!!
                        'EXTRA_ID' => false,
                    ]
                );
                $arProcessedPriceTypes = [];
                while ($arItem = $dbItems->fetch()) {
                    if (isset($arSetPricesList[$arItem['CATALOG_GROUP_ID']])) {
                        if ($arSetPricesList[$arItem['CATALOG_GROUP_ID']]['PRICE'] <= 0) {
                            $arDelPrices[] = $arItem['ID'];
                        } else {
                            $bTmpResult = \CPrice::Update($arItem['ID'], $arSetPricesList[$arItem['CATALOG_GROUP_ID']], $this->recalcPrices);

                            if ($bGetExtResult) {
                                $obTmpResult = new Result();
                                if (!$bTmpResult) {
                                    $obTmpResult->addError(new Error($GLOBALS['APPLICATION']->GetException()));
                                }
                                $obTmpResult->setData(array_merge($arSetPricesList[$arItem['CATALOG_GROUP_ID']], ['ID' => $arItem['ID']]));
                                $arResultData['update'][] = $obTmpResult;
                            }

                            if (!$bTmpResult) {
                                $obResult->addError(new Error('Ошибка при обновлении цены id '.$arItem['ID'].': '.$GLOBALS['APPLICATION']->GetException(), 210));
                            }
                        }

                        unset($arSetPricesList[$arItem['CATALOG_GROUP_ID']]);
                        $arProcessedPriceTypes[$arItem['CATALOG_GROUP_ID']] = $arItem['CATALOG_GROUP_ID'];

                    } elseif (isset($arProcessedPriceTypes[$arItem['CATALOG_GROUP_ID']])) {
                        // удаление цен с уже обработанным типом (возможно, задублировались по какой-то причине)
                        $arDelPrices[] = $arItem['ID'];
                    }
                }
            }

            // добавление новых цен
            if ($arSetPricesList) {
                foreach ($arSetPricesList as $arFields) {
                    if ($arFields['PRICE'] <= 0) {
                        continue;
                    }

                    $mNewPrice = \CPrice::Add($arFields);

                    if ($bGetExtResult) {
                        $obTmpResult = new Result();
                        if (!$mNewPrice) {
                            $obTmpResult->addError(new Error($GLOBALS['APPLICATION']->GetException()));
                        }
                        $obTmpResult->setData(array_merge($arFields, ['ID' => $mNewPrice]));
                        $arResultData['add'][] = $obTmpResult;
                    }

                    if (!$mNewPrice) {
                        $obResult->addError(new Error('Ошибка при добавлении цены: '.$GLOBALS['APPLICATION']->GetException(), 220));
                    }
                }
            }

            // удаление цен
            if ($arDelPrices) {
                foreach ($arDelPrices as $iPriceId) {
                    $bTmpResult = \CPrice::Delete($iPriceId);

                    if ($bGetExtResult) {
                        $obTmpResult = new Result();
                        if (!$bTmpResult) {
                            $obTmpResult->addError(new Error($GLOBALS['APPLICATION']->GetException()));
                        }
                        $obTmpResult->setData(['ID' => $iPriceId]);
                        $arResultData['delete'][] = $obTmpResult;
                    }

                    if (!$bTmpResult) {
                        $obResult->addError(new Error('Ошибка при удалении цены id '.$iPriceId.': '.$GLOBALS['APPLICATION']->GetException(), 230));
                    }
                }
            }

            // обновление свойств
            if ($obResult->isSuccess() && $arSetPropsList) {
                \CIBlockElement::SetPropertyValuesEx($arOfferElementData['ID'], $arOfferElementData['IBLOCK_ID'], $arSetPropsList);
                if ($bGetExtResult) {
                    $obTmpResult = new Result();
                    $obTmpResult->setData($arSetPropsList);
                    $arResultData['offer_props'][] = $obTmpResult;
                }
            }
        }

        $obResult->setData($arResultData);

        return $obResult;
    }

    /**
     * @param string $sRegionCode
     * @return bool
     */
    protected function isBasePriceRegionCode($sRegionCode)
    {
        $sRegionCode = ToUpper(trim($sRegionCode));
        return !strlen($sRegionCode) || $sRegionCode == static::BASE_PRICE_REGION_CODE;
    }

    /**
     * @return array
     */
    protected function getPriceTypesList()
    {
        static $arReturn = array();
        if (empty($arReturn)) {
            $this->incModules();
            $dbItems = Catalog\GroupTable::getList(
                [
                    'select' => ['ID', 'NAME', 'BASE', 'XML_ID'],
                ]
            );
            while ($arItem = $dbItems->fetch()) {
                $arReturn[$arItem['XML_ID']] = $arItem;
            }
        }
        return $arReturn;
    }

    /**
     * @return array
     */
    protected function getBasePriceType()
    {
        static $arReturn = array();
        if (empty($arReturn)) {
            foreach ($this->getPriceTypesList() as $arItem) {
                if ($arItem['BASE'] === 'Y') {
                    $arReturn = $arItem;
                    break;
                }
            }
        }
        return $arReturn;
    }

    /**
     * @return int
     */
    protected function getBasePriceTypeId()
    {
        $arPriceType = $this->getBasePriceType();
        return $arPriceType ? $arPriceType['ID'] : 0;
    }

    /**
     * @return array
     */
    protected function getPriceTypeByXmlId($sXmlId)
    {
        if ($this->isBasePriceRegionCode($sXmlId)) {
            return $this->getBasePriceType();
        } else {
            $arPriceTypesList = $this->getPriceTypesList();
        }

        return isset($arPriceTypesList[$sXmlId]) ? $arPriceTypesList[$sXmlId] : array();
    }

    /**
     * @return int
     */
    protected function getPriceTypeIdByXmlId($sXmlId)
    {
        $arPriceType = $this->getPriceTypeByXmlId($sXmlId);
        return $arPriceType ? $arPriceType['ID'] : 0;
    }
}
