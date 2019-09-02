<?php

use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use FourPaws\Catalog\Model\Product;
use FourPaws\Catalog\Query\ProductQuery;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;
use Psr\Log\LoggerAwareTrait;

/**
 * Created by PhpStorm.
 * User: mmasterkov
 * Date: 24.07.2019
 * Time: 12:31
 */

class CFashionProductFooter extends \CBitrixComponent
{
    private $iblockId;
    private $productXmlIds;
    private $products;
    private $imageIds;
    private $titleImageIds;
    private $sectionIds;
    private $sections;

    use LoggerAwareTrait;


    public function onPrepareComponentParams($params): array
    {
        if (!isset($params['CACHE_TIME'])) {
            $params['CACHE_TIME'] = 86400;
        }
        $params['TYPE'] = $params['TYPE'] ?: 'default';
        $this->iblockId = IblockUtils::getIblockId(IblockType::GRANDIN, IblockCode::FASHION_FOOTER_PRODUCTS);
        return parent::onPrepareComponentParams($params);
    }

    public function executeComponent()
    {
        if($this->startResultCache()){
            $filter = [
                'IBLOCK_ID'    => $this->iblockId,
                'ACTIVE'       => 'Y',
                'SECTION_CODE' => $this->arParams['SECTION_CODE'] ?: false,
            ];

            $dbres = \CIBlockElement::GetList([], $filter);
            while($row = $dbres->GetNextElement()){
                $element = $row->GetFields();
                $element['PROPERTIES'] = $row->GetProperties();

                foreach ($element['PROPERTIES']['PRODUCTS']['VALUE'] as $xmlId){
                    $this->productXmlIds[] = $xmlId;
                }

                $this->sectionIds[] = $element['PROPERTIES']['SECTION']['VALUE'];
                $this->titleImageIds[] = $element['PROPERTIES']['TITLE_IMAGE']['VALUE'];
                $this->imageIds[] = $element['PROPERTIES']['IMAGE']['VALUE'];

                $this->arResult['ELEMENTS'][] = $element;
            }

            $this->fillProducts();
            $this->fillImages();
            $this->fillSections();

            $this->includeComponentTemplate();
        }
    }

    private function fillProducts()
    {
        if(empty($this->productXmlIds)){
            return;
        }

        $productCollection = (new ProductQuery())->withFilter(['XML_ID' => $this->productXmlIds])->exec();
        $this->products = $productCollection;
    }

    private function fillImages()
    {
        if(empty($this->imageIds)){
            return;
        }
        $dbres = CFile::GetList([], ['@ID' => implode(',', $this->imageIds)]);
        while($row = $dbres->Fetch()){
            $this->arResult['IMAGES'][$row['ID']] = COption::GetOptionString("main", "upload_dir", "upload") . "/" . $row["SUBDIR"] . "/" . $row["FILE_NAME"];
        }
        if(empty($this->titleImageIds)){
            return;
        }
        $dbres = CFile::GetList([], ['@ID' => implode(',', $this->titleImageIds)]);
        while($row = $dbres->Fetch()){
            $this->arResult['TITLE_IMAGES'][$row['ID']] = COption::GetOptionString("main", "upload_dir", "upload") . "/" . $row["SUBDIR"] . "/" . $row["FILE_NAME"];
        }
    }

    private function fillSections()
    {
        if(empty($this->sectionIds)){
            return;
        }

        $dbres = CIBlockSection::GetList([], ['ID' => $this->sectionIds], false, ['ID', 'NAME', 'SECTION_PAGE_URL']);
        while($row = $dbres->GetNext())
        {
            $this->sections[$row['ID']] = $row;
        }
    }


    /**
     * @param $xmlId
     * @return Product|false
     */
    public function getProduct($xmlId)
    {
        $product = false;

        try {
            $product = $this->products->filter(function ($product) use ($xmlId) {
                if(!($product instanceof Product)){
                    $this->logger->error(sprintf("Товар с внешнем кодом %s не найден", $xmlId));
                    return false;
                }
                /** @var Product $product */
                return $product->getXmlId() == $xmlId;
            })->first();
        } catch (\Throwable $e) {
            $this->logger->error($e->getMessage());
        }

        return $product ? $product : false;
    }

    public function getSectionUrl($id)
    {
        return $this->sections[$id]['SECTION_PAGE_URL'];
    }
}