<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;
use FourPaws\Enum\IblockProperty;
use FourPaws\Helpers\TaggedCacheHelper;
use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use Bitrix\Iblock\ElementTable;
use Bitrix\Iblock\PropertyTable;
use Bitrix\Iblock\ElementPropertyTable;
use Bitrix\Main\Entity\Base;


/**
 * Class ComparingListComponent
 */
class ComparingListComponent extends \CBitrixComponent
{
    private $comparingIblockId;
    private $brandsIblockId;
    private $productsIblockId;
    private $offersIblockId;
    private $shareIblockId;

    private $brandIds;
    private $imageIds;
    private $offerXmlIds;

    public const MARK_SALE_IMAGE = '<img class="b-common-item__sticker" src="/static/build/images/inhtml/s-proc.svg" alt="" role="presentation"/>';
    public const MARK_GIFT_IMAGE = '<img class="b-common-item__sticker" src="/static/build/images/inhtml/s-gift.svg" alt="" role="presentation"/>';
    public const MARK_HIT_IMAGE = '<img class="b-common-item__sticker" src="/static/build/images/inhtml/s-fire.svg" alt="" role="presentation"/>';
    public const MARK_NEW_IMAGE = '<img class="b-common-item__sticker" src="/static/build/images/inhtml/new.svg" alt="" role="presentation"/>';

    public const DEFAULT_TRANSPARENT_TEMPLATE = '<span class="b-common-item__sticker-wrap" style="background-color:transparent;data-background:transparent;">%s</span>';
    public const DEFAULT_TEMPLATE = '<span class="b-common-item__sticker-wrap" style="background-color:#da291c;data-background:#da291c;">%s</span>';
    public const YELLOW_TEMPLATE = '<span class="b-common-item__sticker-wrap" style="background-color:#feda24;data-background:#feda24;">%s</span>';
    public const GREEN_TEMPLATE = '<span class="b-common-item__sticker-wrap" style="background-color:#44af2b;data-background:#44af2b;">%s</span>';

    public const SIMPLE_SHARE_SALE_CODE = 'VKA0';
    public const SIMPLE_SHARE_DISCOUNT_CODE = 'ZRBT';


    private $propertyTypes = [
        'integer' => [
            'CML2_LINK',
            'PRICE_ACTION',
            'COND_VALUE',
            'IS_HIT',
            'IS_NEW',
            'IS_SALE',
            'IS_POPULAR',
        ],
        'string' => [
            'COND_FOR_ACTION',
            'IMG',
        ],
    ];

    /**
     * @param $params
     *
     * @return array
     */
    public function onPrepareComponentParams($params): array
    {
        if (!isset($params['CACHE_TIME'])) {
            $params['CACHE_TIME'] = 86400;
        }

        return parent::onPrepareComponentParams($params);
    }

    public function executeComponent()
    {
        if ($this->startResultCache()) {

            $this->comparingIblockId = IblockUtils::getIblockId(IblockType::PUBLICATION, IblockCode::COMPARING);
            $this->brandsIblockId = IblockUtils::getIblockId(IblockType::CATALOG, IblockCode::BRANDS);
            $this->productsIblockId = IblockUtils::getIblockId(IblockType::CATALOG, IblockCode::PRODUCTS);
            $this->offersIblockId = IblockUtils::getIblockId(IblockType::CATALOG, IblockCode::OFFERS);
            $this->shareIblockId = IblockUtils::getIblockId(IblockType::PUBLICATION, IblockCode::SHARES);

            TaggedCacheHelper::addManagedCacheTag('iblock_id_' . $this->comparingIblockId);
            TaggedCacheHelper::addManagedCacheTag('iblock_id_' . $this->brandsIblockId);

            $this->fetchProducts();
            $this->fetchShares();
            $this->fetchImages();
            $this->fetchBrands();

            $this->includeComponentTemplate();
        }
    }


    private function getProperty($iblockId, $code)
    {
        $property = PropertyTable::getList([
            'filter' => [
                'IBLOCK_ID' => $iblockId,
                'CODE' => $code,
            ],
            'select' => ['ID'],
        ])->fetch();

        return $property;
    }


    private function getPropertyEntity($iblockId, $propertyId, $type = 'integer')
    {
        $tableName = 'PROPERTY_ENTITY_'.$propertyId;

        $entity = Base::compileEntity(
            $tableName,
            [
                'IBLOCK_ELEMENT_ID' => ['data_type' => 'integer'],
                'PROPERTY_'.$propertyId => ['data_type' => $type],
            ],
            ['table_name' => 'b_iblock_element_prop_s'.$iblockId]
        );

        return $entity;
    }


    private function fetchProducts()
    {
        $compareLinkProperty = $this->getProperty($this->comparingIblockId, IblockProperty::COMPARING_LINK);
        $compareLinkEntity = $this->getPropertyEntity($this->comparingIblockId, $compareLinkProperty['ID']);

        $brandProperty = $this->getProperty($this->productsIblockId, IblockProperty::PRODUCTS_BRAND);
        $brandEntity = $this->getPropertyEntity($this->productsIblockId, $brandProperty['ID']);

        $imageProperty = $this->getProperty($this->offersIblockId, IblockProperty::OFFERS_IMG);
        $imageEntity = $this->getPropertyEntity($this->offersIblockId, $imageProperty['ID']);

        $rsOfferProperties = PropertyTable::getList([
            'filter' => [
                'IBLOCK_ID' => $this->offersIblockId,
                'CODE' => [
                    'CML2_LINK',
                    'IMG',
                    'PRICE_ACTION',
                    'COND_FOR_ACTION',
                    'COND_VALUE',
                    'IS_HIT',
                    'IS_NEW',
                    'IS_SALE',
                    'IS_POPULAR',
                ],
            ],
            'select' => [
                'ID',
                'NAME',
                'CODE',
            ],
        ]);


        /**
         * TODO: Вынести это в отдельную функцию
         * Форматируется список полей в выборке, чтобы получить все свойства элемента
         * [PROPERTY_#CODE#_VALUE => #VALUE#]
         */
        $selectFields = [
            'ID',
            'NAME' => 'PRODUCT.NAME',
            'SORT',
            'CODE',
            'IBLOCK_SECTION_ID',
            'BRAND_ID' => 'PROPERTY_BRAND.PROPERTY_'.$brandProperty['ID'],
            'IMAGE' => 'PROPERTY_IMAGE.PROPERTY_'.$imageProperty['ID'],
            'PRODUCT_ID' => 'PRODUCT.ID',
            'OFFER_ID' => 'OFFER.ID',
            'OFFER_XML_ID' => 'OFFER.XML_ID',
            'WEIGHT' => 'CATALOG_PRODUCT.WEIGHT',
        ];
        $propertyFields = ['IBLOCK_ELEMENT_ID' => ['data_type' => 'integer']];
        $offerProperties = [];
        while($arProperty = $rsOfferProperties->fetch()){
            $selectFields['PROPERTY_'.$arProperty['CODE'].'_VALUE'] = 'OFFER_PROPERTIES.PROPERTY_'.$arProperty['ID'];
            $propertyFields['PROPERTY_'.$arProperty['ID']] = ['data_type' => $this->getPropertyTypeByCode($arProperty['CODE'])];

            //$offerProperties[$arProperty['CODE']] = $arProperty['ID'];
            if(!in_array($arProperty['CODE'], array_keys($offerProperties))){
                $offerProperties[$arProperty['CODE']] = $arProperty['ID'];
            }
        }

        $offerPropertiesEntity = Base::compileEntity(
            'OFFER_PROPERTIES',
            $propertyFields,
            ['table_name' => 'b_iblock_element_prop_s'.$this->offersIblockId]
        );

        $rsProducts = ElementTable::getList([
            'order' => [
                'SORT' => 'ASC',
                'NAME' => 'ASC',
            ],
            'filter' => [
                'IBLOCK_ID' => $this->comparingIblockId,
                'ACTIVE' => 'Y',
            ],
            'select' => $selectFields,
            'runtime' => [
                'PROPERTY_COMPARING_PRODUCT' => [
                    'data_type' => $compareLinkEntity->getDataClass(),
                    'reference' => ['=this.ID' => 'ref.IBLOCK_ELEMENT_ID'],
                    'join_type' => 'inner',
                ],
                'OFFER_PROPERTIES' => [
                    'data_type' => $offerPropertiesEntity->getDataClass(),
                    'reference' => ['=this.PROPERTY_COMPARING_PRODUCT.PROPERTY_'.$compareLinkProperty['ID'] => 'ref.IBLOCK_ELEMENT_ID'],
                    'join_type' => 'inner',
                ],
                'PROPERTY_BRAND' => [
                    'data_type' => $brandEntity->getDataClass(),
                    'reference' => ['=this.OFFER_PROPERTIES.PROPERTY_'.$offerProperties[IblockProperty::OFFERS_LINK] => 'ref.IBLOCK_ELEMENT_ID'],
                    'join_type' => 'inner',
                ],
                'OFFER' => [
                    'data_type' => 'Bitrix\Iblock\ElementTable',
                    'reference' => ['=this.PROPERTY_COMPARING_PRODUCT.PROPERTY_'.$compareLinkProperty['ID'] => 'ref.ID'],
                    'join_type' => 'left',
                ],
                'PRODUCT' => [
                    'data_type' => 'Bitrix\Iblock\ElementTable',
                    'reference' => ['=this.OFFER_PROPERTIES.PROPERTY_'.$offerProperties[IblockProperty::OFFERS_LINK] => 'ref.ID'],
                    'join_type' => 'left',
                ],
                'PROPERTY_IMAGE' => [
                    'data_type' => $imageEntity->getDataClass(),
                    'reference' => ['=this.OFFER.ID' => 'ref.IBLOCK_ELEMENT_ID'],
                    'join_type' => 'inner',
                ],
                'CATALOG_PRODUCT' => [
                    'data_type' => 'Bitrix\Catalog\ProductTable',
                    'reference' => ['=this.OFFER.ID' => 'ref.ID'],
                    'join_type' => 'inner',
                ],
            ],
        ]);

        $arProducts = [];
        while ($arProduct = $rsProducts->fetch()) {

            TaggedCacheHelper::addManagedCacheTags([
                'catalog:offer:' . $arProduct['OFFER_ID'],
                'catalog:product:' . $arProduct['PRODUCT_ID'],
            ]);

            $brandId = $arProduct['BRAND_ID'];
            if(!in_array($brandId, $this->brandIds)){
                $this->brandIds[] = $brandId;
            }

            if(!in_array($arProduct['OFFER_XML_ID'], $this->offerXmlIds)){
                $this->offerXmlIds[] = $arProduct['OFFER_XML_ID'];
            }

            $arImage = unserialize($arProduct['IMAGE']);

            $imageId = $arImage['VALUE'][0];
            $this->imageIds[] = $imageId;

            $weight = $arProduct['WEIGHT'];
            if($weight < 1000){
                $weight .= ' г';
            }
            else{
                $weight = ($weight/1000);
                $weight .= ' кг';
            }

            if(empty($arProducts[$brandId][$arProduct['PRODUCT_ID']])){
                $arProducts[$brandId][$arProduct['PRODUCT_ID']] = [
                    'ID' => $arProduct['PRODUCT_ID'],
                    'NAME' => $arProduct['NAME'],
                    'BRAND_ID' => $brandId,
                ];
            }

            if(empty($arProducts[$brandId][$arProduct['PRODUCT_ID']]['IMAGE_MARK'])) {
                $arProducts[$brandId][$arProduct['PRODUCT_ID']]['IMAGE_MARK'] = $this->getMarkImage($arProduct);
                $arProducts[$brandId][$arProduct['PRODUCT_ID']]['MARK_TEMPLATE'] = $this->getMarkTemplate($arProduct);
            }

            $arProducts[$brandId][$arProduct['PRODUCT_ID']]['OFFERS'][] = [
                'ID' => $arProduct['ID'],
                'SORT' => $arProduct['SORT'],
                'CODE' => $arProduct['CODE'],
                'IBLOCK_SECTION_ID' => $arProduct['IBLOCK_SECTION_ID'],
                'DETAIL_PAGE_URL' => '/comparing/'.$arProduct['IBLOCK_SECTION_ID'].'/',
                'IMAGE' => $imageId,
                'WEIGHT' => $weight
            ];
        }

        $this->arResult['PRODUCTS'] = $arProducts;
    }

    private function fetchBrands() {
        if(empty($this->imageIds)){
            return false;
        }

        $rsBrands = ElementTable::getList([
            'order' => [
                'SORT' => 'ASC',
                'NAME' => 'ASC',
            ],
            'filter' => [
                'ID' => $this->brandIds,
                'ACTIVE' => 'Y',
            ],
            'select' => [
                'ID',
                'SORT',
                'NAME',
                'CODE',
                'PREVIEW_PICTURE_' => 'PREVIEW_PICTURE_ENTITY',
            ],
            'runtime' => [
                'DETAIL_PICTURE_ENTITY' => [
                    'data_type' => 'Bitrix\Main\FileTable',
                    'reference' => ['=this.DETAIL_PICTURE' => 'ref.ID'],
                    'join_type' => 'left'
                ],
                'PREVIEW_PICTURE_ENTITY' => [
                    'data_type' => 'Bitrix\Main\FileTable',
                    'reference' => ['=this.PREVIEW_PICTURE' => 'ref.ID'],
                    'join_type' => 'left'
                ],
            ],
        ]);

        $arBrands = [];
        while ($arBrand = $rsBrands->fetch()) {
            $arBrands[$arBrand['ID']] = [
                'ID' => $arBrand['ID'],
                'NAME' => $arBrand['NAME'],
                'SORT' => $arBrand['SORT'],
                'CODE' => $arBrand['CODE'],
                'PREVIEW_PICTURE' => $this->formatFile('PREVIEW_PICTURE_', $arBrand),
            ];
        }

        $this->arResult['BRANDS'] = $arBrands;
    }

    private function fetchShares() {
        if(empty($this->offerXmlIds)){
            return false;
        }

        //$shareProperty = $this->getProperty($this->shareIblockId, IblockProperty::SHARE_PRODUCTS);
        //$shareEntity = $this->getPropertyEntity($this->shareIblockId, $shareProperty['ID']);

        /*$sharePropertiesEntity = Base::compileEntity(
            'SHARE_PROPERTIES',
            [
                'ID' => ['data_type' => 'integer'],
                'IBLOCK_PROPERTY_ID' => ['data_type' => 'integer'],
                'IBLOCK_ELEMENT_ID'  => ['data_type' => 'integer'],
                'VALUE'  => ['data_type' => 'string'],
            ],
            ['table_name' => 'b_iblock_element_property']
        );*/

        $rsShareProperties = PropertyTable::getList([
            'filter' => [
                'IBLOCK_ID' => $this->shareIblockId,
                'CODE' => [
                    'LABEL',
                    'LABEL_IMAGE',
                    'PRODUCTS'
                ],
            ],
            'select' => [
                'ID',
                'NAME',
                'CODE',
            ],
        ]);

        $shareProperties = [];
        $addSelectedFields = [];
        while($arProperty = $rsShareProperties->fetch()){
            $shareProperties[$arProperty['CODE']] = $arProperty;
            //$addSelectedFields['PROEPRTY_'.$arProperty['CODE'].'_VALUE'] = $
        }

        $sharePropertiesEntity = Base::compileEntity(
            'SHARE_PROPERTIES',
            [
                'ID' => ['data_type' => 'integer'],
                'IBLOCK_PROPERTY_ID' => ['data_type' => 'integer'],
                'IBLOCK_ELEMENT_ID'  => ['data_type' => 'integer'],
                'VALUE'  => ['data_type' => 'string'],
            ],
            ['table_name' => 'b_iblock_element_property']
        );

        $rsShares = ElementTable::getList([
            'order' => [
                'SORT' => 'ASC',
                'NAME' => 'ASC',
            ],
            'filter' => [
                'ACTIVE'       => 'Y',
                '<=ACTIVE_FROM' => new \Bitrix\Main\Type\DateTime(),
                '>ACTIVE_TO'   => new \Bitrix\Main\Type\DateTime(),
                'PROPERTIES.VALUE' => $this->offerXmlIds,
                'PROPERTIES.IBLOCK_PROPERTY_ID' => $shareProperties['PRODUCTS']['ID'],
            ],
            'select' => [
                'ID',
                'NAME',
            ],
            'runtime' => [
                'PROPERTIES' => [
                    'data_type' => $sharePropertiesEntity->getDataClass(),
                    'reference' => array('=this.ID' => 'ref.IBLOCK_ELEMENT_ID'),
                    'join_type' => 'inner'
                ],
            ],
        ]);

        $arShareIds = [];
        while ($arShare = $rsShares->fetch()) {
            $arShareIds[] = $arShare['ID'];
        }

        $rsShares = ElementTable::getList([
            'order' => [
                'SORT' => 'ASC',
                'NAME' => 'ASC',
            ],
            'filter' => [
                'ID' => 'Y',
            ],
            'select' => [
                'ID',
                'NAME',
            ],
            'runtime' => [
                'PROPERTIES' => [
                    'data_type' => $sharePropertiesEntity->getDataClass(),
                    'reference' => array('=this.ID' => 'ref.IBLOCK_ELEMENT_ID'),
                    'join_type' => 'inner'
                ],
            ],
        ]);

        dump($arShareIds);

        //$this->arResult['BRANDS'] = $arBrands;
    }

    private function fetchImages(){
        if(empty($this->imageIds)) {
            return false;
        }

        $rsImages = \Bitrix\Main\FileTable::getList([
            'filter' => [
                'ID' => $this->imageIds
            ],
            'select' => [
                '*',
            ]
        ]);

        $arImages = [];
        while($arImage = $rsImages->Fetch()){
            $arImages[$arImage['ID']] = '/upload/'.$arImage['SUBDIR'].'/'.$arImage['FILE_NAME'];
        }

        $this->arResult['IMAGES'] = $arImages;
    }

    private function formatFile($fieldName, $entity) {
        $formatted = [];

        if ($entity[$fieldName.'FILE_NAME']) {
            $formatted = [
                'ID' => $entity[$fieldName.'ID'],
                'HEIGHT' => $entity[$fieldName.'HEIGHT'],
                'WIDTH' => $entity[$fieldName.'WIDTH'],
                'FILE_SIZE' => $entity[$fieldName.'FILE_SIZE'],
                'CONTENT_TYPE' => $entity[$fieldName.'CONTENT_TYPE'],
                'SUBDIR' => $entity[$fieldName.'SUBDIR'],
                'FILE_NAME' => $entity[$fieldName.'FILE_NAME'],
                'ORIGINAL_NAME' => $entity[$fieldName.'ORIGINAL_NAME'],
                'SRC' => '/upload/'.$entity[$fieldName.'SUBDIR'].'/'.$entity[$fieldName.'FILE_NAME'],
            ];
        }

        return $formatted;
    }

    private function getPropertyTypeByCode($code) {
        foreach($this->propertyTypes as $type => $properties){
            if(array_search($code, $properties) !== false){
                return $type;
            }
        }

        return false;
    }

    private function getMarkTemplate($arOffer){
        if ($arOffer['PROPERTY_IS_HIT_VALUE'] || $arOffer['PROPERTY_IS_POPULAR_VALUE']) {
            return self::YELLOW_TEMPLATE;
        }

        if ($arOffer['PROPERTY_IS_NEW_VALUE']) {
            return self::GREEN_TEMPLATE;
        }

        if ($arOffer['PROPERTY_IS_SALE_VALUE'] || $arOffer['PROPERTY_COND_FOR_ACTION_VALUE'] == self::SIMPLE_SHARE_SALE_CODE) {
            return self::DEFAULT_TEMPLATE;
        }

        /*$share = null;
        if ($shareId > 0) {
            foreach ($offer->getShare() as $shareItem) {
                if ($shareItem->getId() === $shareId) {
                    $share = $shareItem;
                    if ($share->hasLabelImage()) {
                        return self::DEFAULT_TRANSPARENT_TEMPLATE;
                    }
                }
            }
        }*/

        return self::DEFAULT_TEMPLATE;
    }

    private function getMarkImage($arOffer)
    {
        if ($arOffer['PROPERTY_IS_HIT_VALUE'] > 0 || $arOffer['PROPERTY_IS_POPULAR_VALUE'] > 0) {
            return self::MARK_HIT_IMAGE;
        }

        if ($arOffer['PROPERTY_IS_NEW_VALUE'] > 0) {
            return self::MARK_NEW_IMAGE;
        }

        if ($arOffer['PROPERTY_IS_SALE_VALUE'] > 0 || $arOffer['PROPERTY_COND_FOR_ACTION_VALUE'] == self::SIMPLE_SHARE_SALE_CODE) {
            return self::MARK_SALE_IMAGE;
        }

        /*if ($arOffer->isShare()) {
            $share = null;
            if($shareId > 0){
                foreach ($offer->getShare() as $shareItem) {
                    if($shareItem->getId() === $shareId) {
                        $share = $shareItem;
                    }
                }
            }
            if($share === null) {
                $share = $offer->getShare()->first();
            }

            if($share->hasLabelImage()){
                return '<img class="b-common-item__sticker" src="'.$share->getPropertyLabelImageFileSrc().'" alt="" role="presentation"/>';
            }
            if ($share->hasLabel()) {
                return $share->getPropertyLabel();
            }

            return self::MARK_GIFT_IMAGE;
        }*/

        return '';
    }

}
