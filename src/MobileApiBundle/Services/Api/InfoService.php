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
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;
use FourPaws\MobileApiBundle\Dto\Object\Info;
use FourPaws\MobileApiBundle\Enum\InfoEnum;
use Psr\Log\LoggerAwareInterface;

class InfoService implements LoggerAwareInterface
{
    use LazyLoggerAwareTrait;

    /**
     * @var ImageProcessor
     */
    private $imageProcessor;

    /**
     * @var string
     */
    private $bitrixPhpDateTimeFormat;

    public function __construct(ImageProcessor $imageProcessor)
    {
        $this->imageProcessor = $imageProcessor;
        $this->bitrixPhpDateTimeFormat = Date::convertFormatToPhp(\FORMAT_DATETIME) ?: '';
    }

    public function getInfo(string $type, string $id, array $select = []): Collection
    {
        try {
            switch ($type) {
                case InfoEnum::ACTION:
                    $collection = $this->getActions($id, $select);
                    break;
                case InfoEnum::NEWS:
                    $collection = $this->getNews($id, $select);
                    break;
                case InfoEnum::LETTERS:
                    $collection = $this->getActions($id, $select);
                    break;
                default:
                    throw new \RuntimeException(sprintf('No such method to get %s type', $type));
            }
        } catch (\Exception $exception) {
            $collection = new ArrayCollection();
            $this->log()->error($exception->getMessage());
        }
        return $collection->map(function (Info $info) use ($type) {
            $info->setType($type);
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

        return $this->find($criteria, $order, $select, $id ? 1 : 50);
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

        return $this->find($criteria, $order, $select, $id ? 1 : 50);
    }

    /**
     * @param string $id
     *
     * @param array  $select
     *
     * @throws \Adv\Bitrixtools\Exception\IblockNotFoundException
     * @return ArrayCollection|Collection
     */
    protected function getActions(string $id, array $select = []): Collection
    {
        $criteria = [
            'ACTIVE'    => 'Y',
            'IBLOCK_ID' => IblockUtils::getIblockId(IblockType::PUBLICATION, IblockCode::SHARES),
        ];

        if ($id) {
            $criteria['ID'] = $id;
        }

        $order = [
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

        return $this->find($criteria, $order, $select, $id ? 1 : 50);
    }

    protected function find(
        array $criteria = [],
        array $orderBy = [],
        array $select = [],
        int $limit = 50
    ) {
        $items = [];
        $dbResult = CIBlockElement::GetList($orderBy, $criteria, false, ['nTopCount' => $limit], $select);
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

                return $apiView;
            });
        return $infoItems;
    }
}
