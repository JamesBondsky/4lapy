<?php

namespace FourPaws\SapBundle\Consumer;

use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use Bitrix\Catalog\StoreProductTable;
use Bitrix\Catalog\StoreTable;
use Bitrix\Main\Error;
use Bitrix\Main\Result;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;
use FourPaws\SapBundle\Dto\In\DcStock\DcStock;
use FourPaws\SapBundle\Dto\In\DcStock\StockItem;
use FourPaws\SapBundle\Exception\InvalidArgumentException;
use Psr\Log\LoggerAwareInterface;

class DcStockConsumer implements ConsumerInterface, LoggerAwareInterface
{
    use LazyLoggerAwareTrait;

    /** @var array $offersCache */
    private $offersCache = [];

    /** @var int $maxOffersCacheSize */
    private $maxOffersCacheSize = 100;

    /** @var array $storesCache */
    protected $storesCache = [];

    /** @var int $maxStoresCacheSize */
    protected $maxStoresCacheSize = 100;

    /**
     * @param DcStock $dcStock
     *
     * @throws \Adv\Bitrixtools\Exception\IblockNotFoundException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \FourPaws\SapBundle\Exception\InvalidArgumentException
     * @throws \RuntimeException
     * @return bool
     */
    public function consume($dcStock): bool
    {
        if (!$this->support($dcStock)) {
            return false;
        }

        $result = true;

        $this->log()->info(sprintf('Импортируется %s остатков', $dcStock->getItems()->count()));
        foreach ($dcStock->getItems() as $id => $stockItem) {
            $this->log()->debug(sprintf(
                'Импортируется остаток %s для оффера с xml id %s для склада %s',
                $id + 1,
                $stockItem->getOfferXmlId(),
                $stockItem->getPlantCode()
            ));
            if (!$stockItem instanceof StockItem) {
                throw new InvalidArgumentException(sprintf('Trying to pass not %s object', StockItem::class));
            }
            $setResult = $this->setOfferStock($stockItem);
            $result &= $setResult->isSuccess();
            if ($setResult->isSuccess()) {
                $this->log()->debug(sprintf(
                    'Проимпортирован остаток %s для оффера с xml id %s  для склада %s',
                    $id + 1,
                    $stockItem->getOfferXmlId(),
                    $stockItem->getPlantCode()
                ));
            } else {
                foreach ($setResult->getErrors() as $error) {
                    $this->log()->error(sprintf(
                        'Ошибка импорта остатка %s для оффера с xml id %s для склада %s: %s',
                        $id + 1,
                        $stockItem->getOfferXmlId(),
                        $stockItem->getPlantCode(),
                        $error->getMessage()
                    ));
                }
            }
        }

        return $result;
    }

    /**
     * @param $data
     *
     * @return bool
     */
    public function support($data): bool
    {
        return \is_object($data) && $data instanceof DcStock;
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
                        'ID'        => $item['ID'],
                        'IBLOCK_ID' => $item['IBLOCK_ID'],
                    ]
                );
            } else {
                $result->addError(new Error(
                    'Не найден элемент торгового предложения по внешнему коду: ' . $xmlId,
                    200
                ));
            }
        }

        return $result;
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
                $result->addError(new Error('Не найден склад по внешнему коду: ' . $xmlId, 200));
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
                    'order'  => [
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
        // !!!
        // Непонятно как следует обрабатывать значение $stockItem->getStockType()
        // !!!
        $offerElementDataResult = null;
        if ($result->isSuccess()) {
            $offerElementDataResult = $this->getOfferElementDataByXmlId($stockItem->getOfferXmlId());
            if (!$offerElementDataResult->isSuccess()) {
                $result->addErrors($offerElementDataResult->getErrors());
            }
        }

        $storeDataResult = null;
        if ($result->isSuccess()) {
            $storeDataResult = $this->getStoreDataByXmlId($stockItem->getPlantCode());
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
                    'order'  => [
                        'ID' => 'ASC',
                    ],
                    'filter' => [
                        '=PRODUCT_ID' => $offerData['ID'],
                        '=STORE_ID'   => $storeData['ID'],
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
                    'STORE_ID'   => $storeData['ID'],
                    'AMOUNT'     => $stockValue,
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
                    '=XML_ID'   => $xmlId,
                ],
                false,
                false,
                [
                    'ID',
                    'IBLOCK_ID',
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
}
