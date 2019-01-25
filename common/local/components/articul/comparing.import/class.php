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

    private $elementNames;

    private $properties;

    private $filename;
    private $row;
    private $fields = [
        'NAME' => 0,
        'SECTION_NAME' => 1,
    ];

    private $properties = [
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
                        $result = \Bitrix\Iblock\SectionTable::add([
                            'IBLOCK_ID' => $this->comparingIblockId,
                            'IBLOCK_SECTION_ID' => false,
                            'NAME' => $sectionName
                        ]);

                        if ($result->isSuccess()){
                            $sectionId = $result->getId();
                        }
                    }
                    else{
                        $sectionId = $arSection['ID'];
                    }

                    $arProperties = [];
                    foreach($this->properties as $code => $id){
                        $arProperties[$this->properties[$code]] = $this->row[$id];
                    }

                    $arItem = [
                        'NAME' => $name,
                        'IBLOCK_SECTION_ID' => $sectionId,
                        'PROPERTY_VALUES' => $arProperties
                    ];



                    $arItems[] = $arItem;

                }
                dump($arItems);

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

            if(!isset($this->fields[$code])){
                $fieldId = $this->fields[$code];
            }
            elseif(!isset($this->properties[$code])){
                $fieldId = $this->properties[$code];
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

}
