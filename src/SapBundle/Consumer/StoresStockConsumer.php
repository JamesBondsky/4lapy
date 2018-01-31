<?php

namespace FourPaws\SapBundle\Consumer;

use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use Bitrix\Catalog\StoreProductTable;
use Bitrix\Catalog\StoreTable;
use Bitrix\Main\Error;
use Bitrix\Main\Result;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;
use FourPaws\SapBundle\Dto\In\StoresStock\StockItem;
use FourPaws\SapBundle\Dto\In\StoresStock\StoresStock;

class StoresStockConsumer implements ConsumerInterface
{
    /** @var array $offersCache */
    private $offersCache = [];

    /** @var int $maxOffersCacheSize */
    private $maxOffersCacheSize = 100;

    /** @var array $storesCache */
    protected $storesCache = [];

    /** @var int $maxStoresCacheSize */
    protected $maxStoresCacheSize = 100;

    /**
     * @param StoresStock $storesStock
     *
     * @throws \Adv\Bitrixtools\Exception\IblockNotFoundException
     * @throws \Bitrix\Main\ArgumentException
     * @return bool
     */
    public function consume($storesStock): bool
    {
        if (!$this->support($storesStock)) {
            return false;
        }

        foreach ($storesStock->getItems() as $stockItem) {
            $this->setOfferStock($stockItem);
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
        return \is_object($data) && $data instanceof StoresStock;
    }

    /**
     * @throws \Adv\Bitrixtools\Exception\IblockNotFoundException
     * @return int
     */
    protected function getOffersIBlockId(): int
    {
        return IblockUtils::getIblockId(IblockType::CATALOG, IblockCode::OFFERS);
    }

    /**
     * @param string $xmlId
     *
     * @throws \Adv\Bitrixtools\Exception\IblockNotFoundException
     * @return Result
     */
    protected function getOfferElementDataByXmlId($xmlId): Result
    {
        $result = new Result();

        $xmlId = trim($xmlId);
        if ('' === $xmlId) {
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
     * @throws \Adv\Bitrixtools\Exception\IblockNotFoundException
     * @return array
     */
    private function getOfferElementByXmlId($xmlId): array
    {
        $return = [];
        if (!isset($this->offersCache[$xmlId])) {
            $items = \CIBlockElement::GetList(
                [
                    'ID' => 'ASC',
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
            if ($item = $items->Fetch()) {
                $return = $item;
            }

            $this->offersCache[$xmlId] = $return;
            
            if ($this->maxOffersCacheSize > 0 && \count($this->offersCache) > $this->maxOffersCacheSize) {
                $this->offersCache = \array_slice($this->offersCache, 1, null, true);
            }
        } else {
            $return = $this->offersCache[$xmlId];
        }

        return $return;
    }

    /**
     * @param string $xmlId
     *
     * @throws \Bitrix\Main\ArgumentException
     * @return Result
     */
    protected function getStoreDataByXmlId(string $xmlId): Result
    {
        $result = new Result();

        $xmlId = trim($xmlId);
        if ('' === $xmlId) {
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
     *
     * @throws \Bitrix\Main\ArgumentException
     * @return array
     */
    protected function getStoreByXmlId(string $xmlId): array
    {
        $return = [];
        if (!isset($this->storesCache[$xmlId])) {
            $items = StoreTable::getList(
                [
                    'order' => [
                        'ID' => 'ASC',
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

            if ($this->maxStoresCacheSize > 0 && \count($this->storesCache) > $this->maxStoresCacheSize) {
                $this->storesCache = \array_slice($this->storesCache, 1, null, true);
            }
        } else {
            $return = $this->storesCache[$xmlId];
        }

        return $return;
    }

    /**
     * @param StockItem $stockItem
     * @param bool      $getExtResult
     *
     * @throws \Adv\Bitrixtools\Exception\IblockNotFoundException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Exception
     * @return Result
     */
    protected function setOfferStock(StockItem $stockItem, $getExtResult = true): Result
    {
        $result = new Result();

        $resultData = [];

        $offerElementDataResult = null;
        if ($result->isSuccess()) {
            $offerElementDataResult = $this->getOfferElementDataByXmlId($stockItem->getOfferXmlId());
            if (!$offerElementDataResult->isSuccess()) {
                $result->addErrors($offerElementDataResult->getErrors());
            }
        }

        $storeDataResult = null;
        if ($result->isSuccess()) {
            $storeDataResult = $this->getStoreDataByXmlId($stockItem->getStoreCode());
            if (!$storeDataResult->isSuccess()) {
                $result->addErrors($storeDataResult->getErrors());
            }
        }

        if ($result->isSuccess()) {
            $offerData = $offerElementDataResult->getData();
            $storeData = $storeDataResult->getData();
            $stockValue = $stockItem->getStockValue();

            $items = StoreProductTable::getList(
                [
                    'order' => [
                        'ID' => 'ASC',
                    ],
                    'filter' => [
                        '=PRODUCT_ID' => $offerData['ID'],
                        '=STORE_ID' => $storeData['ID'],
                    ],
                ]
            );
            $wasUpdated = false;
            while ($item = $items->fetch()) {
                if ($wasUpdated) {
                    // Удаление возможных дублей
                    // (если структура таблицы не будет испорчена, то такой ситуации никогда не будет)
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
}
