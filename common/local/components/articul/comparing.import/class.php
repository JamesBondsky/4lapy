<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;
use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use Bitrix\Main\Entity\Base;
use Bitrix\Iblock\ElementTable;
use Bitrix\Iblock\PropertyTable;

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

    public $log;
    public $errors;

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
        $this->comparingIblockId = IblockUtils::getIblockId(IblockType::PUBLICATION, IblockCode::COMPARING);
        $this->offersIblockId = IblockUtils::getIblockId(IblockType::CATALOG, IblockCode::OFFERS);

        if($this->arParams['TYPE'] == 'import'){
            if($_FILES['file']['error'] == 4 || $_FILES['file']['size'] <= 0){
                $this->endWithErrors("Выберите файл для импорта");
                return false;
            }

            $file = $_FILES['file'];
            $tmpPath = $file['tmp_name'];

            if(substr(strtolower($file['name']), -4, 4) !== '.csv'){
                $this->endWithErrors("Неверный тип файла");
                return false;
            }

            $handle = fopen($tmpPath, "r");
            $r = fgetcsv($handle, 1000, ",");

            if(is_null($r)){
                $this->endWithErrors("Не удалось считать файл");
                return false;
            }

            $this->filename = $_SERVER['DOCUMENT_ROOT'].'/upload/compare.import.csv';
            if(!move_uploaded_file($tmpPath, $this->filename)){
                $this->endWithErrors("Не удалось загрузить файл на сервер");
                return false;
            }

            $result = $this->import();
            if(!$result){
                return false;
            }

            $this->includeComponentTemplate();
        }
        elseif($this->arParams['TYPE'] == 'export'){
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="compare.export.csv"');

            global $APPLICATION;
            $APPLICATION->RestartBuffer();

            $this->export();
            die();
        }
        else{
            $this->includeComponentTemplate();
        }
    }

    private function import(){
        if(!file_exists($this->filename)){
            $this->endWithErrors("Файл для импорта не найден: ".$this->filename);
            return false;
        }

        $headersFlag = false;
        $localId = 0;
        $arItems = [];
        $arItemsProperties = [];
        $arAddItems = [];
        $arUpdateItems = [];

        $handle = fopen($this->filename, "r");
        if(empty($handle) === false) {
            $this->getProperties();

            while(($this->row = fgetcsv($handle, 1000, ";")) !== FALSE){
                if(!$headersFlag){

                    foreach($this->row as $i => $header){
                        $fieldId = $this->getFieldIdByName($this->fromExcel($header));

                        if($fieldId === false){
                            $this->endWithErrors('Неизвестное поле: "'.$header.'"');
                            return false;
                        }

                        if($fieldId != $i){
                            $this->endWithErrors('Неверный порядок полей. Поле "'.$header.'" должно быть '.($fieldId+1).' по порядку');
                            return false;
                        }
                    }

                    $headersFlag = true;
                    continue;
                }

                $name = $this->fromExcel($this->getFieldValueByCode('NAME'));
                if(empty($name)){
                    $this->endWithErrors('Не имя для элемента с номером '.$localId);
                    return false;
                }
                $this->elementNames[] = $name;

                $sectionName = $this->fromExcel($this->getFieldValueByCode('SECTION_NAME'));
                if(empty($sectionName)){
                    $this->endWithErrors('Не указано название раздела для товара "'.$name.'"');
                    return false;
                }

                $arSection = \Bitrix\Iblock\SectionTable::getList([
                    'select' => ['ID'],
                    'filter' => ['NAME' => $sectionName],
                ])->fetch();


                if(empty($arSection)){

                    $obSection = new \CIBlockSection;
                    $sectionId = $obSection->Add([
                        'IBLOCK_ID' => $this->comparingIblockId,
                        'IBLOCK_SECTION_ID' => false,
                        'NAME' => $sectionName,
                    ]);

                    if (!$sectionId){
                        $this->errors[] = "Не удалось создать раздел: ".$sectionName;
                        break;
                    }
                    else{
                        $this->log('Создан раздел: <a target="_blank" href="'.$this->getSectionViewUrl($sectionId).'">'.$sectionName.' ['.$sectionId.']</a>');
                    }
                }
                else{
                    $sectionId = $arSection['ID'];
                }

                $arProperties = [];
                foreach($this->propertyIds as $code => $id){
                    $arProperties[$this->properties[$code]['ID']] = $this->row[$id];

                    if($code == 'ARTICLE'){
                        $this->offerXmlIds[] = $this->row[$id];
                    }
                }

                $arItems[$localId] = [
                    'NAME' => $name,
                    'IBLOCK_ID' => $this->comparingIblockId,
                    'IBLOCK_SECTION_ID' => (int)$sectionId,
                ];
                $arItemsProperties[$localId] = $arProperties;
                $localId++;
            }

            if(empty($arItems)){
                $this->errors[] = "Не найдено подходящих элементов: ".$sectionName;
            }

            if($this->endWithErrors()){
                return false;
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

            /*dump($arItems);
            dump($arItemsProperties);
            dump($this->properties);
            dump($this->offers);
            dump($this->elements);*/

            foreach($arItems as $localId => $arItem){
                $arProperties = $arItemsProperties[$localId];

                $propIdProduct = $this->getPropertyIdByCode('PRODUCT');
                $propIdArticle = $this->getPropertyIdByCode('ARTICLE');

                $offerId = array_search($arProperties[$propIdArticle], $this->offers);
                if(empty($offerId)){
                    $this->errors[] = "Товар не найден: ".$arItem['NAME'];
                }
                $arProperties[$propIdProduct] = $offerId;

                $elementId = array_search($arItem['NAME'], $this->elements);

                if(is_null($elementId) || $elementId === false){
                    $arAddItems[] = array_merge($arItem, ['PROPERTY_VALUES' => $arProperties]);
                }
                else{
                    $arItem['ID'] = $elementId;
                    $arUpdateItems[] = array_merge($arItem, ['PROPERTY_VALUES' => $arProperties]);
                }
            }
            fclose($handle);
        }

        if($this->endWithErrors()){
            return false;
        }

        $obElement = new \CIBlockElement;
        foreach($arAddItems as $arItem){
            $id = $obElement->Add($arItem);
            if(!$id){
                $this->errors[] = 'Не удалось добавить элемент: '.$obElement->LAST_ERROR.' ['.$arItem['NAME'].']';
            }
            else{
                $this->arResult['ADDED'][] = $id;
                $this->log('Создан новый элемент: <a target="_blank" href="'.$this->getElementEditUrl($id).'">'.$arItem['NAME'].' ['.$id.']</a>');
            }
        }

        foreach($arUpdateItems as $arItem){
            $itemId = $arItem['ID'];
            unset($arItem['ID']);

            $result = $obElement->Update($itemId, $arItem);
            if(!$result){
                $this->errors[] = 'Не удалось обновить элемент: '.$obElement->LAST_ERROR.' ['.$itemId.']';
            }
            else{
                $this->arResult['UPDATED'][] = $itemId;
                $this->log('Обновлён элемент: <a target="_blank" href="'.$this->getElementEditUrl($itemId).'">'.$arItem['NAME'].' ['.$itemId.']</a>');
            }
        }

        $this->arResult['SUCCESS'] = true;
        $this->arResult['EVENTS'] = $this->log;
        $this->arResult['ERRORS'] = $this->errors;

        return true;
    }

    private function export()
    {
        $arCsv = [];
        $arItems = [];
        $arHeaders = [
            0 => 'Название товара',
            1 => 'Название категории',
        ];

        $this->getProperties();

        $selectedFields = [
            'ID',
            'NAME',
            'SECTION_ID' => 'SECTION.ID',
            'SECTION_NAME' => 'SECTION.NAME',
        ];

        $rsProperties = PropertyTable::getList([
            'order' => [
                'SORT' => 'ASC'
            ],
            'filter' => [
                'IBLOCK_ID' => $this->comparingIblockId,
            ],
            'select' => ['ID', 'CODE'],
        ]);
        $propertyFields = [
            'IBLOCK_ELEMENT_ID' => ['data_type' => 'integer']
        ];
        while($arProperty = $rsProperties->fetch()){
            $selectedFields['PROPERTY_'.$arProperty['CODE'].'_VALUE'] = 'PROPERTIES.PROPERTY_'.$arProperty['ID'];
            $propertyFields['PROPERTY_'.$arProperty['ID']] = ['data_type' => 'string'];
        }

        $propertiesEntity = Base::compileEntity(
            'PROPERTIES',
            $propertyFields,
            ['table_name' => 'b_iblock_element_prop_s'.$this->comparingIblockId]
        );

        $rsElements = ElementTable::getList([
            'select' => $selectedFields,
            'filter' => [
                'IBLOCK_ID' => $this->comparingIblockId,
            ],
            'runtime' => [
                'PROPERTIES' => [
                    'data_type' => $propertiesEntity->getDataClass(),
                    'reference' => ['=this.ID' => 'ref.IBLOCK_ELEMENT_ID'],
                    'join_type' => 'left',
                ],
                'SECTION' => [
                    'data_type' => '\Bitrix\Iblock\SectionTable',
                    'reference' => ['=this.IBLOCK_SECTION_ID' => 'ref.ID'],
                ]
            ],
        ]);

        while($arItem = $rsElements->fetch()){
            $arFormatItem = [
                0 => $arItem['NAME'],
                1 => $arItem['SECTION_NAME'],
            ];

            foreach($this->properties as $code => $arProperty){
                if($code == 'PRODUCT'){
                    continue;
                }

                $fieldId = $this->propertyIds[$code];
                $arFormatItem[$fieldId] = $arItem['PROPERTY_'.$code.'_VALUE'];

                if(empty($arHeaders[$fieldId])){
                    $arHeaders[$fieldId] = $arProperty['NAME'];
                }
            }

            ksort($arFormatItem);
            $arItems[] = $arFormatItem;
        }
        ksort($arHeaders);

        $fp = fopen('php://output', 'wb');
        //fputcsv($fp, $arHeaders);
        fputcsv($fp, array_map([$this, 'forExcel'], $arHeaders), ';');
        foreach($arItems as $arItem){
            //fputcsv($fp, $arItem);
            fputcsv($fp, array_map([$this, 'forExcel'], $arItem), ';');
        }
        fclose($fp);

        return true;
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

    private function endWithErrors($mess = false){
        if(!empty($mess)){
            $this->errors[] = $mess;
        }

        if(!empty($this->errors)){
            $this->arResult['SUCCESS'] = false;
            $this->arResult['ERRORS'] = $this->errors;
            $this->includeComponentTemplate();
            return true;
        }

        return false;
    }

    private function log($mess)
    {
        $this->log[] = $mess;
    }

    private function getElementEditUrl($id)
    {
        $url = '/bitrix/admin/iblock_element_edit.php?IBLOCK_ID='.$this->comparingIblockId.'&type=publications&ID='.$id;
        return $url;
    }
    private function getSectionViewUrl($id)
    {
        $url = '/bitrix/admin/iblock_list_admin.php?IBLOCK_ID='.$this->comparingIblockId.'&type=publications&lang=ru&find_section_section='.$id;
        return $url;
    }

    private function getPropertyIdByCode($code)
    {
        return $this->properties[$code]['ID'];
    }
    private function getFieldIdByName($name)
    {
        if($name == "Название товара"){
            return $this->fields['NAME'];
        }
        if($name == "Название категории"){
            return $this->fields['SECTION_NAME'];
        }

        foreach($this->properties as $property){
            if($property['NAME'] == $name){
                return $this->propertyIds[$property['CODE']];
            }
        }

        return false;
    }

    private function getProperties()
    {
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
            $this->properties[$arProperty['CODE']] = $arProperty;
        }
    }

    private function forExcel($string) :string
    {
        return mb_convert_encoding($string, 'cp-1251');
    }

    private function fromExcel($string) :string
    {
        return mb_convert_encoding($string, 'utf-8', 'cp-1251');
    }

}
