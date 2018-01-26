<?php

namespace FourPaws\Migrator\Provider;

use Bitrix\Catalog\ProductTable;
use FourPaws\Migrator\Converter\CodeBuilder;
use FourPaws\Migrator\Converter\ColorToReference;
use FourPaws\Migrator\Converter\CountryToReference;
use FourPaws\Migrator\Converter\File;
use FourPaws\Migrator\Converter\Stm;
use FourPaws\Migrator\Converter\StringToIblock;
use FourPaws\Migrator\Converter\StringToInt;
use FourPaws\Migrator\Converter\StringToMultipleString;
use FourPaws\Migrator\Converter\StringToReference;
use FourPaws\Migrator\Converter\StringToYesNo;
use FourPaws\Migrator\Converter\Trim;
use FourPaws\Migrator\Utils;

/**
 * Class Catalog
 *
 * @package FourPaws\Migrator\Provider
 */
class Catalog extends IBlockElement
{
    /**
     * @inheritdoc
     */
    public function getMap() : array
    {
        $map = [
            'ID'                 => 'ID',
            'NAME'               => 'NAME',
            'ACTIVE'             => 'ACTIVE',
            'SORT'               => 'SORT',
            'DATE_CREATE'        => 'DATE_CREATE',
            'user.CREATED_BY'    => 'CREATED_BY',
            'TIMESTAMP_X'        => 'TIMESTAMP_X',
            'user.MODIFIED_BY'   => 'MODIFIED_BY',
            'CODE'               => 'CODE',
            'XML_ID'             => 'XML_ID',
            'TAGS'               => 'TAGS',
            'SHOW_COUNTER'       => 'SHOW_COUNTER',
            'SHOW_COUNTER_START' => 'SHOW_COUNTER_START',
            
            'PROPERTY_GROUP'       => 'PROPERTY_GROUP',
            'PROPERTY_GROUP_NAME'  => 'PROPERTY_GROUP_NAME',
            'PROPERTY_TTX'         => 'PROPERTY_TTX',
            'PROPERTY_YML_NAME'    => 'PROPERTY_YML_NAME',
            'PROPERTY_sales_notes' => 'PROPERTY_SALES_NOTES',
            
            'PROPERTY_ANIMALS_AGE'           => 'PROPERTY_PET_AGE',
            'PROPERTY_ANIMALS_AGE_BLOSHINKI' => 'PROPERTY_PET_AGE_ADDITIONAL',
            
            'PROPERTY_BRAND'      => 'PROPERTY_BRAND',
            'PROPERTY_BRAND_NAME' => 'PROPERTY_BRAND_NAME',
            
            'PROPERTY_CODE_COLOUR' => 'PROPERTY_CODE_COLOUR',
            'PROPERTY_COLOUR'      => 'PROPERTY_COLOUR',
            
            'PROPERTY_ALPHA_PRODUCT'   => 'PROPERTY_ALPHA_PRODUCT',
            'PROPERTY_GOODS_AND_SIZES' => 'PROPERTY_GOODS_AND_SIZES',
            
            'PROPERTY_TYPE_OF_BONUS'           => 'PROPERTY_REWARD_TYPE',
            'PROPERTY_STM'                     => 'PROPERTY_STM',
            'PROPERTY_STM_OTHER'               => 'PROPERTY_STM_OTHER',
            'PROPERTY_STM_S_KORM'              => 'PROPERTY_STM_S_KORM',
            'PROPERTY_PROIZV_TEXT'             => 'PROPERTY_PRODUCED_BY_HOLDER',
            'PROPERTY_FUNCTION'                => 'PROPERTY_PURPOSE',
            'PROPERTY_WEIGHT_CAPACITY_PACKING' => 'PROPERTY_WEIGHT_CAPACITY_PACKING',
            'PROPERTY_TYPE_OF_PARASITE'        => 'PROPERTY_TYPE_OF_PARASITE',
            'PROPERTY_KIND_OF_PACKING'         => 'PROPERTY_KIND_OF_PACKING',
            'PROPERTY_FOR_WHO'                 => 'PROPERTY_FOR_WHO',
            'PROPERTY_IMG'                     => 'PROPERTY_IMG',
            'PROPERTY_MANAGER_OF_CATEGORY'     => 'PROPERTY_MANAGER_OF_CATEGORY',
            'PROPERTY_CATEGORY'                => 'PROPERTY_CATEGORY',
            'PROPERTY_MULTIPLICITY'            => 'PROPERTY_MULTIPLICITY',
            'PROPERTY_MANUFACTURE_MATERIAL'    => 'PROPERTY_MANUFACTURE_MATERIAL',
            'PROPERTY_COMMON_NAME'             => 'PROPERTY_COMMON_NAME',
            'PROPERTY_PACKING_COMBINATION'     => 'PROPERTY_PACKING_COMBINATION',
            'PROPERTY_VOLUME'                  => 'PROPERTY_VOLUME',
            'PROPERTY_DESCRIPTION_CARD'        => 'DETAIL_TEXT',
            'PROPERTY_GENDER_OF_ANIMAL'        => 'PROPERTY_PET_GENDER',
            'PROPERTY_MAKER'                   => 'PROPERTY_MAKER',
            'PROPERTY_SIZE_OF_THE_ANIMAL_BIRD' => 'PROPERTY_PET_SIZE',
            'PROPERTY_SIZE_CLOTHES'            => 'PROPERTY_CLOTHING_SIZE',
            'PROPERTY_SEASON_CLOTHES'          => 'PROPERTY_SEASON_CLOTHES',
            'PROPERTY_TRADE_NAME'              => 'PROPERTY_TRADE_NAME',
            'PROPERTY_LICENSE'                 => 'PROPERTY_LICENSE',
            'PROPERTY_PRODUCT_FORM'            => 'PROPERTY_PRODUCT_FORM',
            'PROPERTY_LOW_TEMPERATURE'         => 'PROPERTY_LOW_TEMPERATURE',
            'PROPERTY_BARCODE'                 => 'PROPERTY_BARCODE',
            'DETAIL_PAGE_URL'                  => 'PROPERTY_OLD_URL',
            'PROPERTY_COUNTRY_NAME'            => 'PROPERTY_COUNTRY_NAME',
            'PROPERTY_COUNTRY'                 => 'PROPERTY_COUNTRY',
            
            'CATALOG'        => 'CATALOG',
            'DETAIL_PICTURE' => 'DETAIL_PICTURE',
        ];
        
        return $map;
    }
    
    /**
     * @param array $data
     *
     * @return array
     *
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\LoaderException
     * @throws \RuntimeException
     */
    public function prepareData(array $data) : array
    {
        if ($data['CATALOG']['TYPE']) {
            /**
             * Торговый каталог для элемента инфоблока у нас всегда - предложение
             */
            $data['CATALOG']['TYPE'] = ProductTable::TYPE_OFFER;
        }
        
        return parent::prepareData($data);
    }
    
    /**
     * Конвертеры для статей:
     *
     * - тип - загоняем в справочник
     * - артикулы (XML_ID, на самом деле) продуктов извлекаем из детального описания и добавляем в отдельное свойство
     *
     * @throws \Bitrix\Main\LoaderException
     * @return array
     */
    public function getConverters() : array
    {
        $codeConverter           = new CodeBuilder('CODE');
        $stmConverter            = new Stm('PROPERTY_STM_S_KORM');
        $producedConverter       = new StringToYesNo('PROPERTY_PRODUCED_BY_HOLDER');
        $skuTrimConverter        = new Trim('PROPERTY_GOODS_AND_SIZES');
        $skuIntConverter         = new StringToInt('PROPERTY_GOODS_AND_SIZES');
        $barcodeExplodeConverter = new StringToMultipleString('PROPERTY_BARCODE');
        $barcodeTrimConverter    = new Trim('PROPERTY_BARCODE');
        
        $pictureConverter = new File('PROPERTY_IMG');
        $pictureConverter->setToProperty();
        
        $licenseConverter = new StringToYesNo('PROPERTY_LICENSE');
        $licenseConverter->setYes($licenseConverter::YES_TYPE_RU);
        
        $lowTemperatureConverter = new StringToYesNo('PROPERTY_LOW_TEMPERATURE');
        $lowTemperatureConverter->setYes($lowTemperatureConverter::YES_TYPE_RU);
        
        $kindOfPackingConverter = new StringToReference('PROPERTY_KIND_OF_PACKING');
        $kindOfPackingConverter->setReferenceCode('PackageType');
        
        $clothingSizeConverter = new StringToReference('PROPERTY_CLOTHING_SIZE');
        $clothingSizeConverter->setReferenceCode('ClothingSize');
        
        $seasonClothesConverter = new StringToReference('SEASON_CLOTHES');
        $seasonClothesConverter->setReferenceCode('Season');
        
        $forWhoConverter = new StringToReference('PROPERTY_FOR_WHO');
        $forWhoConverter->setReferenceCode('ForWho');
        
        $petSizeConverter = new StringToReference('PROPERTY_PET_SIZE');
        $petSizeConverter->setReferenceCode('PetSize');
        
        $petGenderConverter = new StringToReference('PROPERTY_PET_GENDER');
        $petGenderConverter->setReferenceCode('PetGender');
        
        $categoryConverter = new StringToReference('PROPERTY_CATEGORY');
        $categoryConverter->setReferenceCode('ProductCategory');
        
        $purposeConverter = new StringToReference('PROPERTY_PURPOSE');
        $purposeConverter->setReferenceCode('Purpose');
        
        $makerConverter = new StringToReference('PROPERTY_MAKER');
        $makerConverter->setReferenceCode('Maker');
        
        $tradeNameConverter = new StringToReference('PROPERTY_TRADE_NAME');
        $tradeNameConverter->setReferenceCode('TradeName');
        
        $managerConverter = new StringToReference('PROPERTY_MANAGER_OF_CATEGORY');
        $managerConverter->setReferenceCode('CategoryManager');
        
        $materialConverter = new StringToReference('PROPERTY_MANUFACTURE_MATERIAL');
        $materialConverter->setReferenceCode('Material');
        
        $productFormConverter = new StringToReference('PROPERTY_PRODUCT_FORM');
        $productFormConverter->setReferenceCode('ProductForm');
        
        $parasiteTypeConverter = new StringToReference('PROPERTY_TYPE_OF_PARASITE');
        $parasiteTypeConverter->setReferenceCode('ParasiteType');
        
        $petAgeConverter = new StringToReference('PROPERTY_PET_AGE');
        $petAgeConverter->setReferenceCode('PetAge');
        
        $rewardTypeConverter = new StringToReference('PROPERTY_REWARD_TYPE');
        $rewardTypeConverter->setReferenceCode('RewardType');
        
        $petAgeAdditionalConverter = new StringToReference('PROPERTY_PET_AGE_ADDITIONAL');
        $petAgeAdditionalConverter->setReferenceCode('PetAgeAdditional');
        
        $converters = [
            $barcodeExplodeConverter,
            $barcodeTrimConverter,
            $codeConverter,
            $pictureConverter,
            $skuTrimConverter,
            $skuIntConverter,
            $stmConverter,
            $producedConverter,
            $licenseConverter,
            $lowTemperatureConverter,
            $clothingSizeConverter,
            $kindOfPackingConverter,
            $seasonClothesConverter,
            $forWhoConverter,
            $petSizeConverter,
            $petGenderConverter,
            $categoryConverter,
            $purposeConverter,
            $makerConverter,
            $tradeNameConverter,
            $managerConverter,
            $materialConverter,
            $productFormConverter,
            $parasiteTypeConverter,
            $petAgeConverter,
            $petAgeAdditionalConverter,
            $rewardTypeConverter,
        ];
    
        try {
            $countryConverter = new CountryToReference('PROPERTY_COUNTRY');
            $countryConverter->setReferenceCode('Country');
        
            $converters[] = $countryConverter;
        } catch (\Exception $e) {
            $this->getLogger()->error($e->getMessage());
        }
    
        try {
            $colorConverter = new ColorToReference('PROPERTY_COLOUR');
            $colorConverter->setReferenceCode('Colour');
        
            $converters[] = $colorConverter;
        } catch (\Exception $e) {
            $this->getLogger()->error($e->getMessage());
        }
        
        try {
            $brandConverter = new StringToIblock('PROPERTY_BRAND_NAME');
            $brandConverter->setIblockId(Utils::getIblockId('catalog', 'brands'));
            $brandConverter->setXmlId('PROPERTY_BRAND');
            
            $converters[] = $brandConverter;
        } catch (\Exception $e) {
            $this->getLogger()->error(sprintf('Brand convert error: %s', $e->getMessage()));
        }
        
        return $converters;
    }
}
