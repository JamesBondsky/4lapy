<?php

use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use FourPaws\AppBundle\Entity\BaseEntity;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;

class CHeaderPromoBannerComponent extends \CBitrixComponent
{
    protected $iblockId;

    protected $imageIds = [];

    public function __construct($component = null)
    {
        parent::__construct($component);

        $this->arResult = [
            'ELEMENT' => false,
            'IMAGES' => [],
        ];
    }

    /**
     * @param $params
     * @return array
     * @throws \Adv\Bitrixtools\Exception\IblockNotFoundException
     */
    public function onPrepareComponentParams($params): array
    {
        if (!isset($params['CACHE_TIME'])) {
            $params['CACHE_TIME'] = 86400;
        }

        $this->iblockId = IblockUtils::getIblockId(IblockType::PUBLICATION, IblockCode::HEADER_PROMO_BANNER);

        return parent::onPrepareComponentParams($params);
    }

    public function executeComponent()
    {
        if ($this->startResultCache()) {
            $rsElement = \CIBlockElement::GetList(
                ['SORT' => 'ASC'],
                [
                    'IBLOCK_ID' => $this->iblockId,
                    '=ACTIVE' => BaseEntity::BITRIX_TRUE
                ],
                false, false,
                [
                    'ID', 'IBLOCK_ID', 'NAME', 'PREVIEW_PICTURE', 'PROPERTY_MOBILE_PREVIEW_PICTURE', 'PROPERTY_TABLET_PREVIEW_PICTURE', 'PROPERTY_LINK'
                ]
            );

            $this->arResult['ELEMENT'] = false;

            if ($arElement = $rsElement->GetNext()) {
                $this->arResult['ELEMENT'] = [
                    'NAME' => $arElement['NAME'],
                    'LINK' => $arElement['PROPERTY_LINK_VALUE'],
                    'PICTURE' => $arElement['PREVIEW_PICTURE'],
                    'MOBILE_PICTURE' => $arElement['PROPERTY_MOBILE_PREVIEW_PICTURE_VALUE'],
                    'TABLET_PICTURE' => $arElement['PROPERTY_TABLET_PREVIEW_PICTURE_VALUE'],
                ];

                if ($arElement['PREVIEW_PICTURE']) {
                    $this->imageIds[] = $arElement['PREVIEW_PICTURE'];
                }

                if ($arElement['PROPERTY_MOBILE_PREVIEW_PICTURE_VALUE']) {
                    $this->imageIds[] = $arElement['PROPERTY_MOBILE_PREVIEW_PICTURE_VALUE'];
                }

                if ($arElement['PROPERTY_TABLET_PREVIEW_PICTURE_VALUE']) {
                    $this->imageIds[] = $arElement['PROPERTY_TABLET_PREVIEW_PICTURE_VALUE'];
                }
            }

            $this->fillImages();

            $this->includeComponentTemplate();
        }
    }

    protected function fillImages()
    {
        if (!empty($this->imageIds)) {
            $rsFile = CFile::GetList(false, ['@ID' => $this->imageIds]);

            while ($arFile = $rsFile->GetNext()) {
                $this->arResult['IMAGES'][$arFile['ID']] = CFile::GetFileSRC($arFile);
            }
        }
    }
}