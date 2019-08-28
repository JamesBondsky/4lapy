<?php

use Adv\Bitrixtools\Exception\IblockNotFoundException;
use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use FourPaws\AppBundle\Entity\BaseEntity;
use FourPaws\Catalog\Model\Offer;
use FourPaws\Catalog\Query\OfferQuery;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;
use FourPaws\PersonalBundle\Service\StampService;

class CStampsProductsComponent extends \CBitrixComponent
{
    private $iblockId;

    private $offerIds = [];
    private $fileIds = [];
    private $sectionIds = [];

    /**
     * @param $params
     * @return array
     * @throws IblockNotFoundException
     */
    public function onPrepareComponentParams($params): array
    {
        if (!isset($params['CACHE_TIME'])) {
            $params['CACHE_TIME'] = 86400;
        }

        $this->iblockId = IblockUtils::getIblockId(IblockType::GRANDIN, IblockCode::STAMPS_PRODUCTS);

        return parent::onPrepareComponentParams($params);
    }

    public function executeComponent()
    {
        if ($this->startResultCache()) {
            $this->arResult = [
                'sections' => [],
                'offers' => [],
                'products' => [],
                'images' => [],
            ];

            $this->getSections();
            $this->getProducts();
            $this->getOffers();
            $this->getFiles();

            $this->includeComponentTemplate();
        }
    }

    private function getSections()
    {
        $rsSection = CIBlockSection::GetList(
            ['SORT' => 'ASC'],
            [
                '=IBLOCK_ID' => $this->iblockId,
                '=ACTIVE' => BaseEntity::BITRIX_TRUE,
                '=GLOBAL_ACTIVE' => BaseEntity::BITRIX_TRUE,
            ],
            false,
            ['ID', 'IBLOCK_ID', 'NAME']
        );

        while ($arSection = $rsSection->Fetch()) {
            $this->sectionIds[] = $arSection['ID'];

            $this->arResult['sections'][] = [
                'id' => $arSection['ID'],
                'name' => $arSection['NAME'],
            ];
        }
    }

    private function getProducts()
    {
        $arSelect = [
            'ID',
            'IBLOCK_ID',
            'IBLOCK_SECTION_ID',
            'NAME',
            'PREVIEW_TEXT',
            'PREVIEW_PICTURE',
            'PROPERTY_VIDEO_LINK_MP4',
            'PROPERTY_VIDEO_LINK_OGV',
            'PROPERTY_VIDEO_LINK_WEBM',
            'PROPERTY_VIDEO_PREVIEW',
            'PROPERTY_OFFER_XML_ID',
            'PROPERTY_PREVIEW_PICTURE_MOBILE',
        ];

        $rsElement = CIBlockElement::GetList(
            ['SORT' => 'ASC'],
            [
                '=IBLOCK_ID' => $this->iblockId,
                '=IBLOCK_SECTION_ID' => $this->sectionIds,
                '=ACTIVE' => BaseEntity::BITRIX_TRUE,
                '=GLOBAL_ACTIVE' => BaseEntity::BITRIX_TRUE,
            ],
            false, false,
            $arSelect
        );

        while ($arElement = $rsElement->Fetch()) {
            $this->offerIds[] = $arElement['PROPERTY_OFFER_XML_ID_VALUE'];

            $this->fileIds[] = $arElement['PREVIEW_PICTURE'];
            $this->fileIds[] = $arElement['PROPERTY_PREVIEW_PICTURE_MOBILE_VALUE'];
            $this->fileIds[] = $arElement['PROPERTY_VIDEO_PREVIEW_VALUE'];

            $this->arResult['products'][$arElement['IBLOCK_SECTION_ID']][] = [
                'name' => $arElement['NAME'],
                'description' => $arElement['PREVIEW_TEXT'],
                'preview_img' => [
                    'desktop' => $arElement['PREVIEW_PICTURE'],
                    'mobile' => $arElement['PROPERTY_PREVIEW_PICTURE_MOBILE_VALUE'],
                ],
                'video' => [
                    'preview' => $arElement['PROPERTY_VIDEO_PREVIEW_VALUE'],
                    'links' => [
                        'mp4' => (!empty($arElement['PROPERTY_VIDEO_LINK_MP4_VALUE'])) ? $arElement['PROPERTY_VIDEO_LINK_MP4_VALUE'] : null,
                        'ogv' => (!empty($arElement['PROPERTY_VIDEO_LINK_OGV_VALUE'])) ? $arElement['PROPERTY_VIDEO_LINK_OGV_VALUE'] : null,
                        'webm' => (!empty($arElement['PROPERTY_VIDEO_LINK_WEBM_VALUE'])) ? $arElement['PROPERTY_VIDEO_LINK_WEBM_VALUE'] : null,
                    ]
                ],
                'offer_xml_id' => $arElement['PROPERTY_OFFER_XML_ID_VALUE'],
            ];
        }
    }

    private function getOffers()
    {
        $offerCollection = (new OfferQuery())
            ->withFilter(['XML_ID' => array_unique($this->offerIds)])
            ->withSelect(['ID', 'IBLOCK_ID', 'DETAIL_PAGE_URL'])
            ->exec();

        /** @var Offer $offer */
        foreach ($offerCollection as $offer) {
            $this->arResult['offers'][$offer->getXmlId()] = [
                'id' => $offer->getId(),
                'product_id' => $offer->getProduct()->getId(),
                'href' => $offer->getDetailPageUrl(),
                'base_price' => $offer->getBasePrice(),
                'stamp_levels' => StampService::EXCHANGE_RULES[$offer->getXmlId()],
            ];
        }
    }

    private function getFiles()
    {
        $rsFile = CFile::GetList(false, ['@ID' => array_unique($this->fileIds)]);

        while ($arFile = $rsFile->GetNext()) {
            $this->arResult['images'][$arFile['ID']] = CFile::GetFileSRC($arFile);
        }
    }
}
