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

class ComparingImportComponent extends \CBitrixComponent
{
    private $comparingIblockId;
    private $offersIblockId;

    private $elementNames;
    private $elements;

    private $offerXmlIds;
    private $offers;

    private $properties;

    private $filename;
    private $row;
    private $fields = [
        'NAME' => 0,
        'SECTION_NAME' => 1,
    ];

    private $propertyIds = [
        'ARTICLE'    => 2,
        'FRESH_MEAT' => 3,
        'PROTEIN'    => 4,
        'MINERALS'   => 5,
        'CEREALS'    => 6,
        'COMPOSITION'    => 7,
        'PORTION_WEIGHT' => 8,
    ];

    public function onPrepareComponentParams($params): array
    {
        return parent::onPrepareComponentParams($params);
    }

    public function executeComponent()
    {
        $this->filename = $_SERVER['DOCUMENT_ROOT'].'/compare.imoport.csv';

        $this->comparingIblockId = IblockUtils::getIblockId(IblockType::PUBLICATION, IblockCode::COMPARING);
        $this->offersIblockId = IblockUtils::getIblockId(IblockType::CATALOG, IblockCode::OFFERS);

        $handle = fopen($this->filename, "r");
        $headersFlag = false;
        $arItems = [];
        $arItemProperties = [];

        try{
            if(empty($handle) === false) {

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
                while($arProperty = $rsProperties->fetch()){
                    $this->properties[$arProperty['CODE']] = $arProperty['ID'];
                }

                while(($this->row = fgetcsv($handle, 1000, ",")) !== FALSE){
                    if(!$headersFlag){
                        $headersFlag = true;
                        continue;
                    }

                    $name = $this->getFieldValueByCode('NAME');
                    $this->elementNames[] = $name;

                    $sectionName = $this->getFieldValueByCode('SECTION_NAME');

                    $arSection = \Bitrix\Iblock\SectionTable::getList([
                        'select' => ['ID'],
                        'filter' => ['NAME' => $sectionName],
                    ])->fetch();


                    if(empty($arSection)){

                        /*$result = \Bitrix\Iblock\SectionTable::add([
                            'IBLOCK_ID' => $this->comparingIblockId,
                            'IBLOCK_SECTION_ID' => false,
                            'NAME' => $sectionName,
                            'TIMESTAMP_X' => new \DateTime(),
                        ]);*/

                        $obSection = new \CIBlockSection;

                        $sectionId = $obSection->Add([
                            'IBLOCK_ID' => $this->comparingIblockId,
                            'IBLOCK_SECTION_ID' => false,
                            'NAME' => $sectionName,
                        ]);

                        if (!$sectionId){
                            throw new Exception("Не удалось создать раздел");
                        }
                    }
                    else{
                        $sectionId = $arSection['ID'];
                    }


                    $arProperties = [];
                    foreach($this->propertyIds as $code => $id){
                        $arProperties[$this->properties[$code]] = $this->row[$id];

                        if($code == 'ARTICLE'){
                            $this->offerXmlIds[] = $this->row[$id];
                        }

                    }

                    $arItems[] = [
                        'NAME' => $name,
                        'IBLOCK_SECTION_ID' => (int)$sectionId,
                    ];
                    $arItemProperties[$name] = $arProperties;
                }

                if(empty($arItems)){
                    throw new Exception("Nothing to add/update");
                }

                $rsElements = ElementTable::getList([
                    'select' => ['ID', 'NAME'],
                    'filter' => [
                        'IBLOCK_ID' => $this->comparingIblockId,
                        'NAME' => $this->elementNames,
                    ]
                ]);

                while($arElement = $rsElements->fetch()){
                    $this->elements[$arElement['ID']] = $arElement['NAME'];
                }

                $rsOffers = ElementTable::getList([
                    'select' => ['ID', 'XML_ID'],
                    'filter' => [
                        'IBLOCK_ID' => $this->offersIblockId,
                        'XML_ID' => $this->offerXmlIds,
                    ]
                ]);

                while($arOffer = $rsOffers->fetch()){
                    $this->offers[$arOffer['ID']] = $arOffer['XML_ID'];
                }

                /*dump($this->properties);
                dump($arItems);
                dump($arItemProperties);
                dump($this->offers);
                dump($this->elements);*/

                $obElement = new \CIBlockElement;
                foreach($arItems as $arItem){
                    $index = array_search($arItem['NAME'], $this->elements);


                    if(is_null($index) || $index === false){
                        $arProperties = $arItemProperties[$arItem['NAME']];

                        $propProductId = $this->getPropertyIdByCode('PRODUCT');
                        $offerId = array_search($arProperties[$arItem['NAME']]['ARTICLE'], $this->offerXmlIds);

                        dump($propProductId);
                        dump($arProperties);
                        dump($this->offerXmlIds);

                        //$arItemProperties[$this->properties['PRODUCT']] = $this->offers[$arElement['ID']];


                        //$arItem['PROPERTY_VALUES']
                        //$result = $obElement->Add($arItem);
                    }
                }


                fclose($handle);
            }
        }
        catch (Exception $e){

        }


        $this->includeComponentTemplate();
    }

    private function getFieldValueByCode($code){
        try{
            if(empty($this->row)){
                throw new Exception("data-row not found");
            }

            if(isset($this->fields[$code])){
                $fieldId = $this->fields[$code];
            }
            elseif(isset($this->propertyIds[$code])){
                $fieldId = $this->propertyIds[$code];
            }
            else{
                throw new Exception("Field '".$code."' not found");
            }

            return $this->row[$fieldId];
        }
        catch (Exception $e){
            die($e->getMessage());
        }
    }

    private function getPropertyIdByCode($code){
        return array_search($code, $this->properties);
    }


}
