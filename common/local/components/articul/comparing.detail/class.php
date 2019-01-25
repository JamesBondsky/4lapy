<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;
use FourPaws\Enum\IblockProperty;
use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use Bitrix\Main\Entity\Base;
use Bitrix\Iblock\ElementTable;
use Bitrix\Iblock\PropertyTable;
use Bitrix\Catalog\ProductTable;
use FourPaws\Helpers\TaggedCacheHelper;

class ComparingDetailComponent extends \CBitrixComponent
{
    private $comparingIblockId;
    private $offersIblockId;

    private $imageIds;
    private $offerIds;

    private $properties;
    private $propertyMeasure = [
        'PORTION_WEIGHT' => ' г',
        'FRESH_MEAT' => '%',
        'PROTEIN' => '%',
    ];

    public $propertyNames = [
        'FRESH_MEAT' => '% свежего мяса<br>в корме',
        'PROTEIN' => '% белков животного<br>происхождения',
        'MINERALS' => 'Наличие минералов <br>в хелатной форме',
    ];

    public $hiddenProperties = ['PRODUCT', 'COMPOSITION', 'ARTICLE'];

    public const SIMPLE_SHARE_SALE_CODE = 'VKA0';
    public const SIMPLE_SHARE_DISCOUNT_CODE = 'ZRBT';

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
            $this->offersIblockId = IblockUtils::getIblockId(IblockType::CATALOG, IblockCode::OFFERS);

            // TODO: Здесь нужно сделать сброс кеша при изменении секции
            TaggedCacheHelper::addManagedCacheTag('iblock_id_' . $this->comparingIblockId);

            $this->fetchProducts();
            $this->fetchImages();
            $this->fetchPrices();
        }

        $this->includeComponentTemplate();
    }


    private function fetchProducts()
    {
        $imageProperty = PropertyTable::getList([
            'filter' => [
                'IBLOCK_ID' => $this->offersIblockId,
                'CODE' => IblockProperty::OFFERS_IMG,
            ],
            'select' => ['ID'],
        ])->fetch();

        $imageEntity = Base::compileEntity(
            'PROPERTY_IMAGE',
            [
                'IBLOCK_ELEMENT_ID' => ['data_type' => 'integer'],
                'PROPERTY_'.$imageProperty['ID'] => ['data_type' => 'integer'],
            ],
            ['table_name' => 'b_iblock_element_prop_s'.$this->offersIblockId]
        );


        $rsProperties = PropertyTable::getList([
            'filter' => [
                'IBLOCK_ID' => $this->comparingIblockId,
            ],
            'select' => [
                'ID',
                'NAME',
                'CODE',
            ],
        ]);

        $selectFields = [
            'ID',
            'NAME',
            'SORT',
            'CODE',
            'IBLOCK_SECTION_ID',
            'IMAGE' => 'PROPERTY_IMAGE.PROPERTY_'.$imageProperty['ID'],
            'WEIGHT' => 'CATALOG_PRODUCT.WEIGHT',
        ];
        $propertyFields = ['IBLOCK_ELEMENT_ID' => ['data_type' => 'integer']];
        while($arProperty = $rsProperties->fetch()){
            $selectFields['PROPERTY_'.$arProperty['CODE'].'_VALUE'] = 'PROPERTIES.PROPERTY_'.$arProperty['ID'];
            $propertyFields['PROPERTY_'.$arProperty['ID']] = ['data_type' => 'string'];

            if(!in_array($arProperty['CODE'], $this->properties)){
                $this->properties[$arProperty['CODE']] = $arProperty;
            }
        }
        $selectFields['PRODUCT_ID'] = 'PROPERTIES.PROPERTY_'.$this->getPropIdByCode('PRODUCT');

        $propertiesEntity = Base::compileEntity(
            'PROPERTIES',
            $propertyFields,
            ['table_name' => 'b_iblock_element_prop_s'.$this->comparingIblockId]
        );

        $rsProducts = ElementTable::getList([
            'order' => [
                'SORT' => 'ASC',
                'NAME' => 'ASC',
            ],
            'filter' => [
                'IBLOCK_ID' => $this->comparingIblockId,
                'IBLOCK_SECTION_ID' => $this->arParams['SECTION_ID'],
                'ACTIVE' => 'Y',
            ],
            'select' => $selectFields,
            'runtime' => [
                'PROPERTIES' => [
                    'data_type' => $propertiesEntity->getDataClass(),
                    'reference' => ['=this.ID' => 'ref.IBLOCK_ELEMENT_ID'],
                    'join_type' => 'left',
                ],
                'PROPERTY_IMAGE' => [
                    'data_type' => $imageEntity->getDataClass(),
                    'reference' => ['=this.PROPERTIES.PROPERTY_'.$this->getPropIdByCode(IblockProperty::COMPARING_LINK) => 'ref.IBLOCK_ELEMENT_ID'],
                    'join_type' => 'inner',
                ],
                'CATALOG_PRODUCT' => [
                    'data_type' => 'Bitrix\Catalog\ProductTable',
                    'reference' => ['=this.PROPERTIES.PROPERTY_'.$this->getPropIdByCode(IblockProperty::COMPARING_LINK) => 'ref.ID'],
                    'join_type' => 'inner',
                ],
            ],
        ]);

        $arProducts = [];
        while ($arProduct = $rsProducts->fetch()) {

            TaggedCacheHelper::addManagedCacheTags([
                'catalog:offer:' . $arProduct['PRODUCT_ID'],
            ]);

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

            $properties = [];
            foreach($this->properties as $code => $property){

                $value = $arProduct['PROPERTY_'.$code.'_VALUE'];
                if(!empty($value) && in_array($code, array_keys($this->propertyMeasure))){
                    $value .= $this->propertyMeasure[$code];
                }

                $properties[$code] = [
                    'NAME' => $property['NAME'],
                    'VALUE' => $value,
                ];
            }

            $this->offerIds[] = $arProduct['PROPERTY_PRODUCT_VALUE'];

            $arProducts[$arProduct['PRODUCT_ID']] = [
                'ID' => $arProduct['ID'],
                'PRODUCT_ID' => $arProduct['PRODUCT_ID'],
                'NAME' => $arProduct['NAME'],
                'SORT' => $arProduct['SORT'],
                'CODE' => $arProduct['CODE'],
                'IBLOCK_SECTION_ID' => $arProduct['IBLOCK_SECTION_ID'],
                'IMAGE' => $imageId,
                'WEIGHT' => $weight,
                'PROPERTIES' => $properties,
            ];
        }

        $this->arResult['PRODUCTS'] = $arProducts;
        $this->arResult['PROPERTIES'] = $this->properties;
    }

    private function fetchPrices()
    {
        if(empty($this->offerIds)){
            return false;
        }

        $rsProperties = PropertyTable::getList([
            'filter' => [
                'IBLOCK_ID' => $this->offersIblockId,
                'CODE' => [IblockProperty::OFFERS_COND_FOR_ACTION, IblockProperty::OFFERS_COND_VALUE, IblockProperty::OFFERS_PRICE_ACTION],
            ],
            'select' => ['ID', 'CODE'],
        ]);

        $selectedFields = [
            'ID',
            'WEIGHT',
            'PRICE_VALUE' => 'PRICE.PRICE',
        ];
        $propertyFields = [
            'IBLOCK_ELEMENT_ID' => ['data_type' => 'integer']
        ];
        while($arProperty = $rsProperties->fetch()){
            $selectedFields['PROPERTY_'.$arProperty['CODE'].'_VALUE'] = 'PROPERTIES.PROPERTY_'.$arProperty['ID'];

            if($arProperty['CODE'] == IblockProperty::OFFERS_COND_FOR_ACTION){
                $propertyFields['PROPERTY_'.$arProperty['ID']] = ['data_type' => 'string'];
            }
            else{
                $propertyFields['PROPERTY_'.$arProperty['ID']] = ['data_type' => 'integer'];
            }
        }

        $propertiesEntity = Base::compileEntity(
            'OFFER_PROPERTIES',
            $propertyFields,
            ['table_name' => 'b_iblock_element_prop_s'.$this->offersIblockId]
        );

        $rsOffers = ProductTable::getList([
            'filter' => [
                'ID' => $this->offerIds,
            ],
            'select' => $selectedFields,
            'runtime' => [
                'PRICE' => [
                    'data_type' => '\Bitrix\Catalog\PriceTable',
                    'reference' => ['=this.ID' => 'ref.PRODUCT_ID'],
                    'join_type' => ['left'],
                ],
                'PROPERTIES' => [
                    'data_type' => $propertiesEntity->getDataClass(),
                    'reference' => ['=this.ID' => 'ref.IBLOCK_ELEMENT_ID'],
                    'join_type' => ['left'],
                ]
            ]
        ]);

        $arPrices = [];
        while($arOffer = $rsOffers->fetch()){
            $price = $this->getOptimalPrice($arOffer);

            $weightPortion = (float)$this->arResult['PRODUCTS'][$arOffer['ID']]['PROPERTIES']['PORTION_WEIGHT']['VALUE'];
            $weightTotal = (float)$arOffer['WEIGHT'];
            $pricePortion = (float)($weightPortion * $price) / $weightTotal;

            $arPrices[$arOffer['ID']] = round($pricePortion);
        }

        $this->arResult['PRICES'] = $arPrices;
    }

    private function getOptimalPrice($arOffer): float
    {
        $price = $arOffer['PRICE_VALUE'];
        $discountType = $arOffer['PROPERTY_'.IblockProperty::OFFERS_COND_FOR_ACTION.'_VALUE'];

        if(!is_null($discountType)){
            if($discountType == $this::SIMPLE_SHARE_SALE_CODE){
                $price = (float)$arOffer['PROPERTY_'.IblockProperty::OFFERS_PRICE_ACTION.'_VALUE'];
            }
            elseif($discountType == $this::SIMPLE_SHARE_DISCOUNT_CODE) {
                $price *= (100 - $arOffer['PROPERTY_'.IblockProperty::OFFERS_COND_VALUE.'_VALUE']) / 100;
            }
        }

        return $price;
    }

    private function getPropIdByCode($code){
        return $this->properties[$code]['ID'];
    }

    public function getPropertyName($arProperty){
        return $this->propertyNames[$arProperty['CODE']] ? $this->propertyNames[$arProperty['CODE']] : $arProperty['NAME'];
    }

    public function getPropertyValue($arProperty){
        return ($arProperty['VALUE']) ? $arProperty['VALUE'] : 'нет информации';
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
}
