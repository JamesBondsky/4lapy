<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\SapBundle\Consumer;

use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use Bitrix\Catalog;
use Bitrix\Main\Error;
use Bitrix\Main\Loader;
use Bitrix\Main\Result;
use Bitrix\Main\SystemException;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;
use FourPaws\SapBundle\Dto\In\Prices\Item;
use FourPaws\SapBundle\Dto\In\Prices\Prices;
use Psr\Log\LoggerAwareInterface;

class PriceConsumer implements ConsumerInterface, LoggerAwareInterface
{
    use LazyLoggerAwareTrait;

    const BASE_PRICE_REGION_CODE = 'IM01';

    /** @var string $defaultCurrencyCode */
    protected $defaultCurrencyCode = 'RUB';

    /** @var string $recalcPrices */
    protected $recalcPrices = true;

    /** @var array $offersCache */
    private $offersCache = [];

    /** @var int $maxOffersCacheSize */
    private $maxOffersCacheSize = 100;

    /**
     * @param Prices $prices
     *
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\SystemException
     * @throws \Bitrix\Main\LoaderException
     * @throws \RuntimeException
     * @return bool
     */
    public function consume($prices): bool
    {
        if (!$this->support($prices)) {
            return false;
        }

        if (!$prices->getUploadToIm()) {
            $this->log()->info(sprintf(
                'Импорт для региона %s пропущен. Выставлен флаг не импортировать.',
                $prices->getRegionCode()
            ));
            return false;
        }

        $result = true;

        $this->log()->info(sprintf(
            'Импортируются цены для региона %s. Количество %s',
            $prices->getRegionCode(),
            $prices->getItems()->count()
        ));
        foreach ($prices->getItems() as $id => $priceItem) {
            $offerElementData = $this->getOfferElementDataByXmlId($priceItem->getOfferXmlId());
            if (!$offerElementData->isSuccess()) {
                $this->log()->error(sprintf(
                    'Импорт для региона %s пропущен. Ну найден оффер с xml id %s',
                    $prices->getRegionCode(),
                    $priceItem->getOfferXmlId()
                ));

                continue;
            }
            $setOffersResult = $this->setOfferPrices($offerElementData, $priceItem, $prices->getRegionCode());
            $result &= $setOffersResult->isSuccess();
            if ($setOffersResult->isSuccess()) {
                $this->log()->debug(sprintf(
                    '[%s] Проимпортированы цены для региона %s. Оффер %s.',
                    $id + 1,
                    $prices->getRegionCode(),
                    $priceItem->getOfferXmlId()
                ));
            } else {
                foreach ($setOffersResult->getErrors() as $error) {
                    $this->log()->error(sprintf(
                        'Импорт цен для региона %s. Оффер %s. Ошибка %s',
                        $prices->getRegionCode(),
                        $priceItem->getOfferXmlId(),
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
        return \is_object($data) && $data instanceof Prices;
    }

    /**
     * @throws \Bitrix\Main\LoaderException
     * @throws SystemException
     * @return void
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
     * @throws \Bitrix\Main\SystemException
     * @throws \Bitrix\Main\LoaderException
     * @return int
     */
    protected function getOffersIBlockId(): int
    {
        static $iblockId = -1;
        if ($iblockId < 0) {
            $this->incModules();
            $tmpItem = \CIBlock::GetList(
                [
                    'ID' => 'ASC',
                ],
                [
                    'CODE'              => IblockCode::OFFERS,
                    'TYPE'              => IblockType::CATALOG,
                    'CHECK_PERMISSIONS' => 'N',
                ]
            )->Fetch();
            $iblockId = $tmpItem['ID'] ?: 0;
        }
        return $iblockId;
    }

    /**
     * @param string $xmlId
     *
     * @throws \Bitrix\Main\SystemException
     * @throws \Bitrix\Main\LoaderException
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
     * @param Result $offersResult
     * @param Item   $priceItem
     * @param string $regionCode
     * @param bool   $getExtResult
     *
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\SystemException
     * @throws \Bitrix\Main\LoaderException
     * @return Result
     */
    protected function setOfferPrices(
        Result $offersResult,
        Item $priceItem,
        $regionCode,
        $getExtResult = true
    ): Result {
        $result = new Result();

        $resultData = [];

        $offerElementData = $offersResult->getData();
        if (empty($offerElementData['ID'])) {
            $result->addError(new Error('Не задан id торгового предложения', 100));
        }

        if ($result->isSuccess()) {
            $this->incModules();
            $productId = $offerElementData['ID'];
            $currency = $this->defaultCurrencyCode;

            $delPrices = [];

            $setPricesList = [];
            $tmpPriceTypeId = $this->getPriceTypeIdByXmlId($regionCode);
            if ($tmpPriceTypeId) {
                $setPricesList[$tmpPriceTypeId] = [
                    'PRODUCT_ID'       => $productId,
                    'CATALOG_GROUP_ID' => $tmpPriceTypeId,
                    'PRICE'            => $priceItem->getRetailPrice(),
                    'CURRENCY'         => $currency,
                ];
            }

            $setPropsList = [];
            $setPropsList['PRICE_ACTION'] = $priceItem->getActionPrice();
            $setPropsList['PRICE_ACTION'] = $setPropsList['PRICE_ACTION'] > 0 ? $setPropsList['PRICE_ACTION'] : '';
            $setPropsList['COND_FOR_ACTION'] = trim($priceItem->getPriceType());
            $setPropsList['COND_VALUE'] = $priceItem->getDiscountValue();
            $setPropsList['COND_VALUE'] = $setPropsList['COND_VALUE'] == 0 ? '' : $setPropsList['COND_VALUE'];

            if ($setPricesList) {
                // обновление существующих цен
                $items = \CPrice::GetListEx(
                    [
                        'ID' => 'ASC',
                    ],
                    [
                        'PRODUCT_ID'    => $productId,
                        'CURRENCY'      => $currency,
// !!!
// непонятно что делать с ценами, зависящими от количества, если таковые будут
// !!!
                        'QUANTITY_FROM' => false,
                        'QUANTITY_TO'   => false,
// !!!
// непонятно что делать с наценками, если таковые будут
// !!!
                        'EXTRA_ID'      => false,
                    ]
                );
                $processedPriceTypes = [];
                while ($item = $items->Fetch()) {
                    if (isset($setPricesList[$item['CATALOG_GROUP_ID']])) {
                        if ($setPricesList[$item['CATALOG_GROUP_ID']]['PRICE'] <= 0) {
                            $delPrices[] = $item['ID'];
                        } else {
                            $res = \CPrice::Update(
                                $item['ID'],
                                $setPricesList[$item['CATALOG_GROUP_ID']],
                                $this->recalcPrices
                            );

                            if ($getExtResult) {
                                $tmpResult = new Result();
                                if (!$res) {
                                    $tmpResult->addError(new Error($GLOBALS['APPLICATION']->GetException()));
                                }
                                $tmpResult->setData(array_merge(
                                    $setPricesList[$item['CATALOG_GROUP_ID']],
                                    ['ID' => $item['ID']]
                                ));
                                $resultData['update'][] = $tmpResult;
                            }

                            if (!$res) {
                                $result->addError(new Error(
                                    'Ошибка при обновлении цены id ' . $item['ID'] . ': ' . $GLOBALS['APPLICATION']->GetException(),
                                    210
                                ));
                            }
                        }

                        unset($setPricesList[$item['CATALOG_GROUP_ID']]);
                        $processedPriceTypes[$item['CATALOG_GROUP_ID']] = $item['CATALOG_GROUP_ID'];
                    } elseif (isset($processedPriceTypes[$item['CATALOG_GROUP_ID']])) {
                        // удаление цен с уже обработанным типом (возможно, задублировались по какой-то причине)
                        $delPrices[] = $item['ID'];
                    }
                }
            }

            // добавление новых цен
            if ($setPricesList) {
                foreach ($setPricesList as $fields) {
                    if ($fields['PRICE'] <= 0) {
                        continue;
                    }

                    $newPriceId = \CPrice::Add($fields);

                    if ($getExtResult) {
                        $tmpResult = new Result();
                        if (!$newPriceId) {
                            $tmpResult->addError(new Error($GLOBALS['APPLICATION']->GetException()));
                        }
                        $tmpResult->setData(array_merge($fields, ['ID' => $newPriceId]));
                        $resultData['add'][] = $tmpResult;
                    }

                    if (!$newPriceId) {
                        $result->addError(new Error(
                            'Ошибка при добавлении цены: ' . $GLOBALS['APPLICATION']->GetException(),
                            220
                        ));
                    }
                }
            }

            // удаление цен
            if ($delPrices) {
                foreach ($delPrices as $priceId) {
                    $res = \CPrice::Delete($priceId);

                    if ($getExtResult) {
                        $tmpResult = new Result();
                        if (!$res) {
                            $tmpResult->addError(new Error($GLOBALS['APPLICATION']->GetException()));
                        }
                        $tmpResult->setData(['ID' => $priceId]);
                        $resultData['delete'][] = $tmpResult;
                    }

                    if (!$res) {
                        $result->addError(new Error(
                            'Ошибка при удалении цены id ' . $priceId . ': ' . $GLOBALS['APPLICATION']->GetException(),
                            230
                        ));
                    }
                }
            }

            // обновление свойств
            if ($setPropsList && $result->isSuccess()) {
                \CIBlockElement::SetPropertyValuesEx(
                    $offerElementData['ID'],
                    $offerElementData['IBLOCK_ID'],
                    $setPropsList
                );
                if ($getExtResult) {
                    $tmpResult = new Result();
                    $tmpResult->setData($setPropsList);
                    $resultData['offer_props'][] = $tmpResult;
                }
            }
        }

        $result->setData($resultData);

        return $result;
    }

    /**
     * @param string $regionCode
     *
     * @return bool
     */
    protected function isBasePriceRegionCode($regionCode)
    {
        $regionCode = ToUpper(trim($regionCode));
        return '' === $regionCode || $regionCode == static::BASE_PRICE_REGION_CODE;
    }

    /**
     * @throws \Bitrix\Main\SystemException
     * @throws \Bitrix\Main\LoaderException
     * @throws \Bitrix\Main\ArgumentException
     * @return array
     */
    protected function getPriceTypesList()
    {
        static $return = [];
        if (empty($return)) {
            $this->incModules();
            $items = Catalog\GroupTable::getList(
                [
                    'select' => ['ID', 'NAME', 'BASE', 'XML_ID'],
                ]
            );
            while ($item = $items->fetch()) {
                $return[$item['XML_ID']] = $item;
            }
        }
        return $return;
    }

    /**
     * @throws \Bitrix\Main\SystemException
     * @throws \Bitrix\Main\LoaderException
     * @throws \Bitrix\Main\ArgumentException
     * @return array
     */
    protected function getBasePriceType()
    {
        static $return = [];
        if (empty($return)) {
            foreach ($this->getPriceTypesList() as $item) {
                if ($item['BASE'] === 'Y') {
                    $return = $item;
                    break;
                }
            }
        }
        return $return;
    }

    /**
     * @throws \Bitrix\Main\SystemException
     * @throws \Bitrix\Main\LoaderException
     * @throws \Bitrix\Main\ArgumentException
     * @return int
     */
    protected function getBasePriceTypeId()
    {
        $priceType = $this->getBasePriceType();
        return $priceType ? $priceType['ID'] : 0;
    }

    /**
     * @param string $xmlId
     *
     * @throws \Bitrix\Main\SystemException
     * @throws \Bitrix\Main\LoaderException
     * @throws \Bitrix\Main\ArgumentException
     * @return array
     */
    protected function getPriceTypeByXmlId($xmlId)
    {
        if ($this->isBasePriceRegionCode($xmlId)) {
            return $this->getBasePriceType();
        }
        $priceTypesList = $this->getPriceTypesList();


        return $priceTypesList[$xmlId] ?? [];
    }

    /**
     * @param string $xmlId
     *
     * @throws \Bitrix\Main\SystemException
     * @throws \Bitrix\Main\LoaderException
     * @throws \Bitrix\Main\ArgumentException
     * @return int
     */
    protected function getPriceTypeIdByXmlId($xmlId)
    {
        $priceType = $this->getPriceTypeByXmlId($xmlId);
        return $priceType ? $priceType['ID'] : 0;
    }

    /**
     * @param string $xmlId
     *
     * @throws \Bitrix\Main\SystemException
     * @throws \Bitrix\Main\LoaderException
     * @return array
     */
    private function getOfferElementByXmlId($xmlId): array
    {
        $return = [];
        if (!isset($this->offersCache[$xmlId])) {
            $this->incModules();
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
