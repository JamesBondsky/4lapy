<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\MobileApiBundle\Services\Api;

use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use Bitrix\Main\Type\Date;
use CIBlockElement;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use FourPaws\BitrixOrm\Collection\ImageCollection;
use FourPaws\BitrixOrm\Model\IblockElement;
use FourPaws\BitrixOrm\Query\IblockElementQuery;
use FourPaws\Catalog\Query\OfferQuery;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;
use FourPaws\Helpers\HighloadHelper;
use FourPaws\MobileApiBundle\Dto\Object\Catalog\ShortProduct;
use FourPaws\MobileApiBundle\Dto\Object\Info;
use FourPaws\MobileApiBundle\Enum\InfoEnum;
use FourPaws\MobileApiBundle\Exception\RuntimeException;
use Psr\Log\LoggerAwareInterface;

class InfoService implements LoggerAwareInterface
{
    use LazyLoggerAwareTrait;

    /** @var ImageProcessor */
    private $imageProcessor;

    /** @var string */
    private $bitrixPhpDateTimeFormat;

    /** @var ProductService */
    private $productService;

    /** @var ShortProduct[]  */
    private static $cache = [];

    public function __construct(ImageProcessor $imageProcessor, ProductService $productService)
    {
        $this->imageProcessor = $imageProcessor;
        $this->productService = $productService;
        $this->bitrixPhpDateTimeFormat = Date::convertFormatToPhp(\FORMAT_DATETIME) ?: '';
    }

    public function getInfo(string $type, string $id, array $select = [], $offerTypeCode = '')
    {
        try {
            switch ($type) {
                case InfoEnum::ACTION:
                    $return = $this->getActions($id, $select, $offerTypeCode)->getValues();
                    break;
                case InfoEnum::NEWS:
                    $return = $this->getNews($id, $select)->getValues();
                    break;
                case InfoEnum::LETTERS:
                    $return = $this->getArticles($id, $select)->getValues();
                    break;
                case InfoEnum::DELIVERY:
                case InfoEnum::REGISTER_TERMS:
                case InfoEnum::BONUS_CARD_INFO:
                case InfoEnum::OBTAIN_BONUS_CARD:
                case InfoEnum::CONTACTS:
                case InfoEnum::ABOUT:
                    $return = $this->getInfoItem($type, $id, $select);
                    break;
                default:
                    throw new \RuntimeException(sprintf('No such method to get %s type', $type));
            }
        } catch (\Exception $exception) {
            $return = new ArrayCollection();
            $this->log()->error($exception->getMessage());
        }
        return $return;
    }

    public function getOfferTypes()
    {
        $result = [];
        $hlBlock = \Bitrix\Highloadblock\HighloadBlockTable::getList(
            [
                'filter' => [
                    '=ID' => HighloadHelper::getIdByName('PublicationType'),
                ]
            ]
        )->fetch();
        if ($hlBlock ) {
            $filterTypes = [];
            $filter = [
                'ACTIVE'    => 'Y',
                'ACTIVE_DATE' => 'Y',
                'IBLOCK_ID' => IblockUtils::getIblockId(IblockType::PUBLICATION, IblockCode::SHARES),
                '!PROPERTY_TYPE' => false,
            ];
            $group = [
                'PROPERTY_TYPE',
            ];
            $res = \CIBlockElement::GetList([], $filter, $group);
            while ($row = $res->fetch()) {
                $filterTypes[$row['PROPERTY_TYPE_VALUE']] = $row['PROPERTY_TYPE_VALUE'];
            }

            $result = [];
            $sHlEntityClass = \Bitrix\Highloadblock\HighloadBlockTable::compileEntity($hlBlock)->getDataClass();
            $res = $sHlEntityClass::getList(
                [
                    'select' => [
                        'ID',
                        'UF_NAME',
                        'UF_XML_ID',
                        // для релазиции сортировки asc,nulls
                        new \Bitrix\Main\Entity\ExpressionField(
                            'UF_SORT_ISNULL',
                            'ISNULL(%s)',
                            'UF_SORT'
                        ),
                    ],
                    'order' => [
                        'UF_SORT_ISNULL' => 'asc',
                        'UF_SORT' => 'asc',
                        'UF_NAME' => 'asc',
                    ]
                ]
            );
            while ($row = $res->fetch()) {

                if ($filterTypes[$row['UF_XML_ID']]) {
                    $result[] = [
                        'id' => (int) $row['ID'],
                        'name' => $row['UF_NAME'],
                        'code' => $row['UF_XML_ID']
                    ];
                }
            }

        }
        return $result;
    }

    protected function getInfoItem(string $type, string $id, array $select = []): Info
    {
        try {

            $collection = (new IblockElementQuery(
                    IblockUtils::getIblockId(
                        IblockType::PUBLICATION,
                        IblockCode::MOBILE_APP_CONTENT
                    )
                ))
                ->withFilter([
                    'ACTIVE'    => 'Y',
                    '=CODE' => $type
                ])
                ->exec();

            /** @var IblockElement $info */
            if ($info = $collection->current()) {
                return (new Info())
                    ->setId($info->getXmlId())
                    ->setType($type)
                    ->setName($info->getName())
                    ->setDetailText($info->getDetailText())
                    ->setPreviewText($info->getPreviewText());
            } else {
                throw new RuntimeException("Контент с типом $type не доступен");
            }

        } catch (\Exception $exception) {
            throw new RuntimeException($exception->getMessage());
        }
    }

    /**
     * @param string $id
     *
     * @param array  $select
     *
     * @throws \Adv\Bitrixtools\Exception\IblockNotFoundException
     * @return Collection|Info[]
     */
    protected function getNews(string $id = '', array $select = []): Collection
    {
        $criteria = [
            'ACTIVE'    => 'Y',
            'IBLOCK_ID' => IblockUtils::getIblockId(IblockType::PUBLICATION, IblockCode::NEWS),
        ];

        if ($id) {
            $criteria['ID'] = $id;
        }

        $order = [
            'ACTIVE_FROM' => 'DESC',
            'SORT'        => 'ASC',
        ];

        $select = $select ?: [
            'ID',
            'NAME',
            'DATE_ACTIVE_FROM',
            'PREVIEW_TEXT',
            'PREVIEW_PICTURE',
            'CODE',
            'CANONICAL_PAGE_URL',
            'DETAIL_TEXT',
        ];

        return $this
            ->find($criteria, $order, $select, $id ? 1 : 50)
            ->map(function (Info $info) {
                $info->setType(InfoEnum::NEWS);
                return $info;
            });
    }

    /**
     * @param string $id
     *
     * @param array  $select
     *
     * @throws \Adv\Bitrixtools\Exception\IblockNotFoundException
     * @return Collection|Info[]
     */
    protected function getArticles(string $id = '', array $select = []): Collection
    {
        $criteria = [
            'ACTIVE'    => 'Y',
            'IBLOCK_ID' => IblockUtils::getIblockId(IblockType::PUBLICATION, IblockCode::ARTICLES),
        ];

        if ($id) {
            $criteria['ID'] = $id;
        }

        $order = [
            'ACTIVE_FROM' => 'DESC',
            'SORT'        => 'ASC',
        ];

        $select = $select ?: [
            'ID',
            'NAME',
            'DATE_ACTIVE_FROM',
            'PREVIEW_TEXT',
            'PREVIEW_PICTURE',
            'CODE',
            'CANONICAL_PAGE_URL',
            'DETAIL_TEXT',
        ];

        return $this
            ->find($criteria, $order, $select, $id ? 1 : 50)
            ->map(function (Info $info) {
                $info->setType(InfoEnum::LETTERS);
                return $info;
            });
    }

    /**
     * @param string $id
     *
     * @param array $select
     *
     * @param string $offerTypeCode
     * @return ArrayCollection|Collection
     * @throws \Adv\Bitrixtools\Exception\IblockNotFoundException
     */
    protected function getActions(string $id, array $select = [], $offerTypeCode = ''): Collection
    {
        // костыль
        if ($offerTypeCode === 'vse') {
            $offerTypeCode = '';
        }

        $criteria = [
            'ACTIVE'    => 'Y',
            'ACTIVE_DATE' => 'Y',
            'IBLOCK_ID' => IblockUtils::getIblockId(IblockType::PUBLICATION, IblockCode::SHARES),
        ];
        if ($offerTypeCode) {
            $criteria['PROPERTY_TYPE'] = $offerTypeCode;
        }

        if ($id) {
            $criteria['ID'] = $id;
        }

        $order = [
            'DATE_ACTIVE_FROM' => 'DESC,NULLS',
            'SORT' => 'ASC',
            'ID' => 'DESC',
        ];

        $select = $select ?: [
            'ID',
            'DATE_ACTIVE_FROM',
            'DATE_ACTIVE_TO',
            'NAME',
            'PREVIEW_TEXT',
            'PREVIEW_PICTURE',
            'DETAIL_TEXT',
            'CANONICAL_PAGE_URL',
            'SUB_ITEMS',
        ];

        return $this
            ->find($criteria, $order, $select, $id ? 1 : 50)
            ->map(function (Info $info) {
                $info->setType(InfoEnum::ACTION);
                return $info;
            });
    }

    protected function find(
        array $criteria = [],
        array $orderBy = [],
        array $select = [],
        int $limit = 50
    ) {
        $items = [];
        $dbResult = \CIBlockElement::GetList($orderBy, $criteria, false, ['nTopCount' => $limit], $select);
        while ($dbItem = $dbResult->GetNext()) {
            $items[$dbItem['ID']] = $dbItem;
        }

        $imagesIds = [];
        if (\in_array('PREVIEW_PICTURE', $select, true)) {
            $imagesIds = array_map(function ($item) {
                return $item['PREVIEW_PICTURE'] ?? '';
            }, $items);
            $imagesIds = array_filter($imagesIds);
        }
        $imageCollection = ImageCollection::createFromIds($imagesIds);

        $infoItems = (new ArrayCollection($items))
            ->map(function ($item) use ($imageCollection) {
                $apiView = new Info();
                if ($item['ID'] ?? null) {
                    $apiView->setId((string)$item['ID']);
                }

                if ($item['NAME'] ?? null) {
                    $apiView->setName((string)$item['NAME']);
                }

                if ($item['PREVIEW_TEXT'] ?? null) {
                    $apiView->setPreviewText((string)$item['PREVIEW_TEXT']);
                }

                if ($item['DETAIL_TEXT'] ?? null) {
                    $apiView->setDetailText((string)$item['DETAIL_TEXT']);
                }

                if ($item['CANONICAL_PAGE_URL'] ?? null) {
                    $apiView->setUrl((string)$item['CANONICAL_PAGE_URL']);
                }

                if ($item['PREVIEW_PICTURE'] ?? null) {
                    $apiView->setIcon($this->imageProcessor->findImage($item['PREVIEW_PICTURE'], $imageCollection));
                }

                if ($item['DATE_ACTIVE_FROM'] ?? null) {
                    $dateTime = \DateTime::createFromFormat(
                        $this->bitrixPhpDateTimeFormat,
                        $item['DATE_ACTIVE_FROM']
                    );
                    $apiView->setDateFrom($dateTime ?: null);
                }

                if ($item['DATE_ACTIVE_TO'] ?? null) {
                    $dateTime = \DateTime::createFromFormat(
                        $this->bitrixPhpDateTimeFormat,
                        $item['DATE_ACTIVE_TO']
                    );
                    $apiView->setDateTo($dateTime ?: null);
                }

                if ($item['IBLOCK_CODE'] === IblockCode::SHARES) {
                    $apiView->setGoods($this->getGoods($item['ID']));
                }

                return $apiView;
            });
        return $infoItems;
    }

    /**
     * @param int $specialOfferId
     * @return ArrayCollection
     * @throws \Adv\Bitrixtools\Exception\IblockNotFoundException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     */
    private function getGoods(int $specialOfferId)
    {
        $products = new ArrayCollection();
        $iblockId = IblockUtils::getIblockId(IblockType::PUBLICATION, IblockCode::SHARES);
        $rs = CIBlockElement::GetProperty($iblockId, $specialOfferId, [], ['CODE' => 'PRODUCTS', 'EMPTY' => 'N']);
        while ($property = $rs->fetch()) {
            $offerId = $property['VALUE'];

            if (!array_key_exists($property['VALUE'], self::$cache)) {
                $offer = (new OfferQuery())->withFilter(['=XML_ID' => $offerId])->exec()->current();
                $product = $offer->getProduct();
                self::$cache[$offerId] = $this->productService->convertToShortProduct($product, $offer);
            }

            $products->add(self::$cache[$offerId]);
        }
        return $products;
    }
}
