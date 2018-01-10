<?php

namespace FourPaws\SapBundle\Consumer;

use Bitrix\Catalog\StoreProductTable;
use Bitrix\Catalog\StoreTable;
use Bitrix\Main\Loader;
use Bitrix\Main\SystemException;
use Bitrix\Main\Result;
use Bitrix\Main\Error;
use FourPaws\SapBundle\Dto\In\Stock\Stock;
use FourPaws\SapBundle\Dto\In\Stock\Dc;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;
use FourPaws\SapBundle\Exception\InvalidArgumentException;

class StockConsumer implements ConsumerInterface
{
    private $plantImCode = 'IM01';
    private $plantDcCode = 'DC01';

    /** @var array $offersCache */
    protected $offersCache = [];

    /** @var int $maxOffersCacheSize */
    protected $maxOffersCacheSize = 100;

    /** @var array $storesCache */
    protected $storesCache = [];

    /** @var int $maxStoresCacheSize */
    protected $maxStoresCacheSize = 100;

    /**
     * @param Stock $stock
     *
     * @return bool
     */
    public function consume($stock): bool
    {
        if (!$this->support($stock)) {
            return false;
        }

        if (!$stock->getUploadToIm()) {
            return false;
        }

        if (!$this->checkPlantImCode($stock->getPlantIm())) {
            return false;
        }

        if (!$this->checkPlantDcCode($stock->getPlantDc())) {
            return false;
        }

        foreach ($stock->getDcs() as $dcItem) {
            if (!$dcItem instanceof Dc) {
                throw new InvalidArgumentException(sprintf('Trying to pass not %s object', Dc::class));
            }
            $setStockResult = $this->setOfferStock($dcItem);
        }
        
        return true;
    }

    /**
     * @param $data
     *
     * @return bool
     */
    public function support($data): bool
    {
        return \is_object($data) && $data instanceof Stock;
    }

    /**
     * @return void
     * @throws SystemException
     */
    protected function incModules()
    {
        if (!Loader::includeModule('iblock')) {
            throw new SystemException('Module iblock is not installed');
        }

        if (!Loader::includeModule('catalog')) {
            throw new SystemException('Module catalog is not installed');
        }
    }

    /**
     * @return int
     */
    protected function getOffersIBlockId(): int
    {
        static $iblockId = -1;
        if ($iblockId < 0) {
            $this->incModules();
            $tmpItem = \CIBlock::GetList(
                [
                    'ID' => 'ASC'
                ],
                [
                    'CODE' => IblockCode::OFFERS,
                    'TYPE' => IblockType::CATALOG,
                    'CHECK_PERMISSIONS' => 'N',
                ]
            )->fetch();
            $iblockId = $tmpItem['ID'] ? $tmpItem['ID'] : 0;
        }
        return $iblockId;
    }

    /**
     * @param string $xmlId
     *
     * @return Result
     */
    protected function getOfferElementDataByXmlId($xmlId): Result
    {
        $result = new Result();

        $xmlId = trim($xmlId);
        if (!strlen($xmlId)) {
            $result->addError(new Error('Не задан внешний код торгового предложения', 100));
        }

        if ($result->isSuccess()) {
            $item = $this->getOfferElementByXmlId($xmlId);
            if ($item) {
                $result->setData(
                    [
                        'ID' => $item['ID'],
                        'IBLOCK_ID' => $item['IBLOCK_ID'],
                    ]
                );
            } else {
                $result->addError(new Error('Не найден элемент торгового предложения по внешнему коду: '.$xmlId, 200));
            }
        }

        return $result;
    }

    /**
     * @param string $xmlId
     *
     * @return array
     */
    private function getOfferElementByXmlId($xmlId): array
    {
        $return = [];
        if (!isset($this->offersCache[$xmlId])) {
            $this->incModules();
            $items = \CIBlockElement::GetList(
                [
                    'ID' => 'ASC'
                ],
                [
                    'IBLOCK_ID' => $this->getOffersIBlockId(),
                    '=XML_ID' => $xmlId,
                ],
                false,
                false,
                [
                    'ID', 'IBLOCK_ID',
                ]
            );
            if ($item = $items->fetch()) {
                $return = $item;
            }

            $this->offersCache[$xmlId] = $return;
            
            if ($this->maxOffersCacheSize > 0 && count($this->offersCache) > $this->maxOffersCacheSize) {
                $this->offersCache = array_slice($this->offersCache, 1, null, true);
            }
        } else {
            $return = $this->offersCache[$xmlId];
        }

        return $return;
    }

    /**
     * @param string $xmlId
     * @return Result
     */
    protected function getStoreDataByXmlId(string $xmlId): Result
    {
        $result = new Result();

        $xmlId = trim($xmlId);
        if (!strlen($xmlId)) {
            $result->addError(new Error('Не задан внешний код склада', 100));
        }

        if ($result->isSuccess()) {
            $item = $this->getStoreByXmlId($xmlId);
            if ($item) {
                $result->setData(
                    [
                        'ID' => $item['ID'],
                    ]
                );
            } else {
                $result->addError(new Error('Не найден склад по внешнему коду: '.$xmlId, 200));
            }
        }

        return $result;
    }

    /**
     * @param string $xmlId
     * @return array
     */
    protected function getStoreByXmlId(string $xmlId): array
    {
        $return = [];
        if (!isset($this->storesCache[$xmlId])) {
            $this->incModules();
            $items = StoreTable::getList(
                [
                    'order' => [
                        'ID' => 'ASC'
                    ],
                    'filter' => [
                        '=XML_ID' => $xmlId,
                    ],
                    'select' => [
                        'ID',
                    ],
                ]
            );
            if ($item = $items->fetch()) {
                $return = $item;
            }

            $this->storesCache[$xmlId] = $return;

            if ($this->maxStoresCacheSize > 0 && count($this->storesCache) > $this->maxStoresCacheSize) {
                $this->storesCache = array_slice($this->storesCache, 1, null, true);
            }
        } else {
            $return = $this->storesCache[$xmlId];
        }

        return $return;
    }

    /**
     * @param Dc $dcItem
     * @param bool $getExtResult
     * @return Result
     */
    protected function setOfferStock(Dc $dcItem, $getExtResult = true): Result
    {
        $result = new Result();

        $resultData = [];

        $offerElementDataResult = null;
        if ($result->isSuccess()) {
            $offerElementDataResult = $this->getOfferElementDataByXmlId($dcItem->getOfferXmlId());
            if (!$offerElementDataResult->isSuccess()) {
                $result->addErrors($offerElementDataResult->getErrors());
            }
        }

        $storeDataResult = null;
        if ($result->isSuccess()) {
            $storeDataResult = $this->getStoreDataByXmlId($dcItem->getWerksCode());
            if (!$storeDataResult->isSuccess()) {
                $result->addErrors($storeDataResult->getErrors());
            }
        }

        if ($result->isSuccess()) {
            $this->incModules();

            $offerData = $offerElementDataResult->getData();
            $storeData = $storeDataResult->getData();
            $stockValue = doubleval($dcItem->getStockValue());

            $items = StoreProductTable::getList(
                [
                    'order' => [
                        'ID' => 'ASC'
                    ],
                    'filter' => [
                        '=PRODUCT_ID' => $offerData['ID'],
                        '=STORE_ID' => $storeData['ID'],
                    ]
                ]
            );
            $wasUpdated = false;
            while ($item = $items->fetch()) {
                if ($wasUpdated) {
                    // Удаление возможных дублей
                    $actionResult = StoreProductTable::delete($item['ID']);
                    if ($getExtResult) {
                        $tmpResult = new Result();
                        if (!$actionResult->isSuccess()) {
                            $tmpResult->addErrors($actionResult->getErrors());
                        }
                        $tmpResult->setData($item);
                        $resultData['delete'][] = $tmpResult;
                    }
                } else {
                    // Обновление существующей записи
                    $actionResult = StoreProductTable::update($item['ID'], ['AMOUNT' => $stockValue]);
                    if ($getExtResult) {
                        $tmpResult = new Result();
                        if (!$actionResult->isSuccess()) {
                            $tmpResult->addErrors($actionResult->getErrors());
                        }
                        $tmpResult->setData(array_merge($item, ['AMOUNT' => $stockValue]));
                        $resultData['update'][] = $tmpResult;
                    }
                    $wasUpdated = true;
                }
            }

            if (!$wasUpdated) {
                // Добавление новой записи
                $fields = [
                    'PRODUCT_ID' => $offerData['ID'],
                    'STORE_ID' => $storeData['ID'],
                    'AMOUNT' => $stockValue,
                ];
                $actionResult = StoreProductTable::add($fields);
                if ($getExtResult) {
                    $tmpResult = new Result();
                    if (!$actionResult->isSuccess()) {
                        $tmpResult->addErrors($actionResult->getErrors());
                    }
                    $tmpResult->setData($fields);
                    $resultData['add'][] = $tmpResult;
                }
            }
        }

        $result->setData($resultData);

        return $result;
    }
    
    /**
     * @param string $plantImCode
     * @return bool
     */
    protected function checkPlantImCode(string $plantImCode): bool
    {
        return $plantImCode == $this->plantImCode;
    }

    /**
     * @param string $plantDcCode
     * @return bool
     */
    protected function checkPlantDcCode(string $plantDcCode): bool
    {
        return $plantDcCode == $this->plantDcCode;
    }
}
