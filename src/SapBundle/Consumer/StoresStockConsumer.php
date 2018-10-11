<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\SapBundle\Consumer;

use Adv\Bitrixtools\Exception\IblockNotFoundException;
use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use Bitrix\Catalog\StoreProductTable;
use Bitrix\Catalog\StoreTable;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Error;
use Bitrix\Main\Result;
use Bitrix\Main\SystemException;
use Exception;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;
use FourPaws\SapBundle\Dto\In\StoresStock\StockItem;
use FourPaws\SapBundle\Dto\In\StoresStock\StoresStock;
use FourPaws\SapBundle\Exception\InvalidArgumentException;
use Psr\Log\LoggerAwareInterface;
use RuntimeException;

/**
 * Class StoresStockConsumer
 *
 * @package FourPaws\SapBundle\Consumer
 */
class StoresStockConsumer implements ConsumerInterface, LoggerAwareInterface
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
     * @param StoresStock $storesStock
     *
     * @throws SystemException
     * @throws Exception
     * @throws RuntimeException
     * @throws IblockNotFoundException
     * @throws ArgumentException
     * @throws InvalidArgumentException
     * @return bool
     */
    public function consume($storesStock): bool
    {
        if (!$this->support($storesStock)) {
            return false;
        }

        $result = true;
        $errorCount = 0;

        $this->log()->info(sprintf('Импортируется %s остатков', $storesStock->getItems()->count()));

        foreach ($storesStock->getItems() as $id => $stockItem) {
            if (!$stockItem instanceof StockItem) {
                throw new InvalidArgumentException(sprintf('Trying to pass not %s object', StockItem::class));
            }

            $setResult = $this->setOfferStock($stockItem);

            if (!$setResult->isSuccess()) {
                $errorCount++;
                $this->log()->error(
                    sprintf(
                        'Ошибка импорта остатка %s для оффера с xml id %s для склада %s: %s',
                        $id + 1,
                        $stockItem->getOfferXmlId(),
                        $stockItem->getStoreCode(),
                        \implode(', ', $setResult->getErrorMessages())
                    )
                );
            }

            if (!($id % 1000)) {
                $this->log()->info(
                    \sprintf(
                        'Проимпортировано остатков %d, ошибок: %d, успешно %d',
                        $id + 1,
                        $errorCount,
                        $id + 1 - $errorCount
                    )
                );
            }
        }

        $this->log()->info(
            \sprintf(
                'Импорт завершен. Проимпортировано остатков %d, ошибок: %d, успешно %d',
                $id ?? 0 + 1,
                $errorCount,
                $id ?? 0 + 1 - $errorCount
            )
        );

        return $result;
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
     * @throws IblockNotFoundException
     * @return int
     */
    protected function getOffersIBlockId(): int
    {
        return IblockUtils::getIblockId(IblockType::CATALOG, IblockCode::OFFERS);
    }

    /**
     * @param string $xmlId
     *
     * @throws IblockNotFoundException
     * @return Result
     */
    protected function getOfferElementDataByXmlId($xmlId): Result
    {
        $result = new Result();

        $xmlId = trim($xmlId);
        if ($xmlId === '') {
            $result->addError(
                new Error(
                    'Не задан внешний код торгового предложения',
                    'emptyOfferXmlId'
                )
            );
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
                $result->addError(
                    new Error(
                        'Не найден элемент торгового предложения по внешнему коду: ' . $xmlId,
                        'offerElementNotFound'
                    )
                );
            }
        }

        return $result;
    }

    /**
     * @param string $xmlId
     *
     * @throws IblockNotFoundException
     * @return array
     */
    private function getOfferElementByXmlId($xmlId): array
    {
        $return = [];

        if (isset($this->offersCache[$xmlId])) {
            $return = $this->offersCache[$xmlId];
        } else {
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
        }

        return $return;
    }

    /**
     * @param string $xmlId
     * @param bool $refreshCache
     *
     * @throws SystemException
     * @throws ArgumentException
     * @return Result
     */
    protected function getStoreDataByXmlId(string $xmlId, $refreshCache = false): Result
    {
        $result = new Result();

        $xmlId = trim($xmlId);
        if ($xmlId === '') {
            $result->addError(
                new Error(
                    'Не задан внешний код склада',
                    'emptyStoreXmlId'
                )
            );
        }

        if ($result->isSuccess()) {
            $item = $this->getStoreByXmlId($xmlId, $refreshCache);
            if ($item) {
                $result->setData(
                    [
                        'ID' => $item['ID'],
                    ]
                );
            } else {
                $result->addError(
                    new Error(
                        'Не найден склад по внешнему коду: ' . $xmlId,
                        'storeNotFound'
                    )
                );
            }
        }

        return $result;
    }

    /**
     * @param string $xmlId
     * @param bool $refreshCache
     *
     * @throws SystemException
     * @throws ArgumentException
     * @return array
     */
    protected function getStoreByXmlId(string $xmlId, $refreshCache = false): array
    {
        $return = [];
        if ($refreshCache || !isset($this->storesCache[$xmlId])) {
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
     *
     * @throws RuntimeException
     * @return Result
     *
     */
    protected function createStore($stockItem): Result
    {
        $result = new Result();

        $resultData = [];
        $xmlId = trim($stockItem->getStoreCode());

        $fields = [
            'TITLE' => $xmlId,
            'XML_ID' => $xmlId,
            'ACTIVE' => 'Y',
            'UF_IS_SHOP' => 1,
            'ADDRESS' => '-',
        ];

        $addResult = null;
        try {
            $addResult = StoreTable::add($fields);
        } catch (Exception $exception) {
            $errorMsg = sprintf(
                'Ошибка создания склада с внешним кодом %s: %s',
                $xmlId,
                $exception->getMessage()
            );
            $result->addError(
                new Error(
                    $errorMsg,
                    'createStoreErrorException'
                )
            );

            $this->log()->error($errorMsg);
        }

        $id = 0;
        if ($addResult) {
            if ($addResult->isSuccess()) {
                $id = $addResult->getId();

                $this->log()->info(
                    sprintf(
                        'Создан склад с внешним кодом %s; ID: %s',
                        $xmlId,
                        $id
                    )
                );
            } else {
                $errorMsg = sprintf(
                    'Ошибка создания склада с внешним кодом %s: %s',
                    $xmlId,
                    implode('; ', $addResult->getErrorMessages())
                );
                $result->addError(
                    new Error(
                        $errorMsg,
                        'createStoreError'
                    )
                );

                $this->log()->error($errorMsg);
            }
        }

        $resultData['id'] = $id;
        $resultData['fields'] = $fields;
        $result->setData($resultData);

        return $result;
    }

    /**
     * @param StockItem $stockItem
     * @param bool $getExtResult
     *
     * @throws SystemException
     * @throws IblockNotFoundException
     * @throws ArgumentException
     * @throws Exception
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
            // ищется склад по коду,
            // если склад не будет найден, то создается склад с незаполненными полями
            $storeDataResult = $this->getStoreDataByXmlId($stockItem->getStoreCode());

            if (!$storeDataResult->isSuccess()) {
                foreach ($storeDataResult->getErrors() as $error) {
                    if ($error->getCode() === 'storeNotFound') {
                        $createStoreResult = $this->createStore($stockItem);
                        if ($createStoreResult->isSuccess()) {
                            // повторный поиск с обновлением кеша метода
                            $storeDataResult = $this->getStoreDataByXmlId($stockItem->getStoreCode(), true);
                        }
                        break;
                    }
                }
                /** @noinspection NotOptimalIfConditionsInspection */
                if (!$storeDataResult->isSuccess()) {
                    $result->addErrors($storeDataResult->getErrors());
                }
            }
        }

        if ($result->isSuccess()) {
            $offerData = $offerElementDataResult->getData();
            $storeData = $storeDataResult->getData();
            $stockValue = floor($stockItem->getStockValue());

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

            /**
             * @var array $item
             */
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
                    $actionResult = StoreProductTable::update($item['ID'], [
                        'AMOUNT' => $stockValue,
                        'PRODUCT_ID' => $item['PRODUCT_ID'] // передаем productId в event
                    ]);
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
