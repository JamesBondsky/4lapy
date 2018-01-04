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

class PricesConsumer implements ConsumerInterface
{
    /** @var string $defaultCurrencyCode */
    protected $defaultCurrencyCode = 'RUB';

    /** @var string $recalcPrices */
    protected $recalcPrices = true;

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

        if ($prices->getUploadToIm()) {
            return false;
        }

        foreach ($prices->getItems() as $obPriceItem) {
            $obOfferElementData = $this->getOfferElementDataByXmlId($obPriceItem->getOfferXmlId());
            if (!$obOfferElementData->isSuccess()) {
                continue;
            }
            $this->setOfferPrices($obOfferElementData, $obPriceItem, $prices->getRegionCode());
        }

        return $bReturn;
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
            $this->incModules();
            $arItem = \CIBlockElement::GetList(
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
                    'ID'
                ]
            )->fetch();
            if ($arItem) {
                $obResult->setData(
                    [
                        'ID' => $arItem['ID'],
                    ]
                );
            } else {
                $obResult->addError(new Error('Не найден элемент торгового предложения по внешнему коду: '.$sXmlId, 200));
            }
        }

        return $obResult;
    }

    /**
     * @param Result $obOfferElementData
     * @param Item $obPriceItem
     * @param string $sRegionCode
     *
     * @return Result
     */
    protected function setOfferPrices(Result $obOfferElementData, Item $obPriceItem, $sRegionCode): Result
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

            $arSetPricesList = [];
            $iBasePriceTypeId = $this->getBasePriceTypeId();
            if ($iBasePriceTypeId) {
                $arSetPricesList[$iBasePriceTypeId] = [
                    'PRODUCT_ID' => $iProductId,
                    'CATALOG_GROUP_ID' => $iBasePriceTypeId,
                    'PRICE' => doubleval($obPriceItem->getRetailPrice()),
                    'CURRENCY' => $sCurrency,
                ];
            }

            $iRegionPriceTypeId = $this->getPriceTypeIdByXmlId($sRegionCode);
            if ($iRegionPriceTypeId) {
                $arSetPricesList[$iRegionPriceTypeId] = [
                    'PRODUCT_ID' => $iProductId,
                    'CATALOG_GROUP_ID' => $iRegionPriceTypeId,
                    'PRICE' => doubleval($obPriceItem->getRetailPrice()),
                    'CURRENCY' => $sCurrency,
                ];
            }

            $arDelPrices = [];
            // обновление существующих цен
            $dbItems = \CPrice::GetListEx(
                [
                    'ID' => 'ASC'
                ],
                [
                    'PRODUCT_ID' => $iProductId,
                    'CURRENCY' => $sCurrency,
                    //'QUANTITY_FROM' => false,
                    //'QUANTITY_TO' => false,
                ]
            );
            while ($arItem = $dbItems->fetch()) {
// !!!
// непонятно что делать с наценками, если таковые вернет выборка
// непонятно что делать с ценами, зависящими от количества, если таковые вернет выборка
// !!!
                if (isset($arSetPricesList[$arItem['CATALOG_GROUP_ID']])) {
                    if ($arSetPricesList[$arItem['CATALOG_GROUP_ID']]['PRICE'] <= 0) {
                        $arDelPrices[] = $arItem['ID'];
                    } else {
                        $bTmpResult = \CPrice::Update($arItem['ID'], $arSetPricesList[$arItem['CATALOG_GROUP_ID']], $this->recalcPrices);

                        $obTmpResult = new Result();
                        if (!$bTmpResult) {
                            $obTmpResult->addError(new Error($GLOBALS['APPLICATION']->GetException()));
                        }
                        $obTmpResult->setData(array_merge($arSetPricesList[$arItem['CATALOG_GROUP_ID']], ['ID' => $arItem['ID']]));
                        $arResultData['update'][] = $obTmpResult;

                        unset($arSetPricesList[$arItem['CATALOG_GROUP_ID']]);
                    }
                } else {
// !!!
// если цена есть в каталоге, но нет в выгрузке, то такая цена удаляется?
// !!!
                    //$arDelPrices[] = $arItem['ID'];
                }
            }

            // добавление новых цен
            if ($arSetPricesList) {
                foreach ($arSetPricesList as $arFields) {
                    $bTmpResult = \CPrice::Add($arFields);

                    $obTmpResult = new Result();
                    if (!$bTmpResult) {
                        $obTmpResult->addError(new Error($GLOBALS['APPLICATION']->GetException()));
                    }
                    $obTmpResult->setData($arFields);
                    $arResultData['add'][] = $obTmpResult;
                }
            }

            // удаление цен
            if ($arDelPrices) {
                foreach ($arDelPrices as $iPriceId) {
                    $bTmpResult = \CPrice::Delete($iPriceId);

                    $obTmpResult = new Result();
                    if (!$bTmpResult) {
                        $obTmpResult->addError(new Error($GLOBALS['APPLICATION']->GetException()));
                    }
                    $obTmpResult->setData(['ID' => $iPriceId]);
                    $arResultData['delete'][] = $obTmpResult;
                }
            }
        }

        $obResult->setData($arResultData);

        return $obResult;
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
        $arPriceTypesList = $this->getPriceTypesList();
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
