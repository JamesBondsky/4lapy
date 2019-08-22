<?php

use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use FourPaws\Catalog\Model\Product;
use FourPaws\Catalog\Query\ProductQuery;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;

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


    public function onPrepareComponentParams($params): array
    {
        if (!isset($params['CACHE_TIME'])) {
            $params['CACHE_TIME'] = 86400;
        }
        $this->iblockId = IblockUtils::getIblockId(IblockType::GRANDIN, IblockCode::FASHION_FOOTER_PRODUCTS);
        return parent::onPrepareComponentParams($params);
    }

    public function executeComponent()
    {
        //if($this->startResultCache()){
            $dbres = \CIBlockElement::GetList([], ['IBLOCK_ID' => $this->iblockId, 'ACTIVE' => 'Y']);
            while($row = $dbres->GetNextElement()){
                $element = $row->GetFields();
                $element['PROPERTIES'] = $row->GetProperties();

                foreach ($element['PROPERTIES']['PRODUCTS']['VALUE'] as $xmlId){
                    $this->productXmlIds[] = $xmlId;
                }

                $this->titleImageIds[] = $element['PROPERTIES']['TITLE_IMAGE']['VALUE'];
                $this->imageIds[] = $element['PROPERTIES']['IMAGE']['VALUE'];

                $this->arResult['ELEMENTS'][] = $element;
            }

            $this->fillProducts();
            $this->fillImages();
        //}
        $this->includeComponentTemplate();
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

    public function getProduct($xmlId)
    {
        return $this->products->filter(function ($product) use ($xmlId) {
            /** @var Product $product */
            return $product->getXmlId() == $xmlId;
        })->first();
    }
}