<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace Sprint\Migration;

use Adv\Bitrixtools\Migration\SprintMigrationBase;
use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use Bitrix\Iblock\SectionElementTable;
use Bitrix\Iblock\SectionTable;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;

class reorganizeFoodSelectionStructure20180126130150 extends SprintMigrationBase
{
    protected $description = 'Переформирование структуры заполнения корма и миграция';
    
    private   $iblockId;
    
    /** @var HelperManager $helper */
    private $helper;
    
    public function up()
    {
        $this->helper = new HelperManager();
        
        /** @noinspection PhpUnhandledExceptionInspection */
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $this->iblockId = IblockUtils::getIblockId(IblockType::CATALOG, IblockCode::FOOD_SELECTION);
        
        //get current sections
        $res            = SectionTable::query()->setFilter(
            [
                'IBLOCK_ID' => $this->iblockId,
            ]
        )->setSelect(
            [
                'ID',
                'DEPTH_LEVEL',
                'CODE',
                'XML_ID',
                'PARENT_CODE' => 'PARENT_SECTION.CODE',
            ]
        )->setOrder(['PARENT_SECTION.CODE' => 'asc'])->exec();
        $sections       = [];
        $petTypeSection = [];
        while ($item = $res->fetch()) {
            if ($item['DEPTH_LEVEL'] > 1) {
                $sections[$item['ID']] = $item;
            } else {
                $petTypeSection[] = $item['ID'];
            }
        }
        
        //add root sections
        $rootSectionsIds                     = [];
        $rootSectionsIds['pet_type']         = $this->addSection('Тип питомца', 'pet_type');
        $rootSectionsIds['pet_age']          = $this->addSection('Возраст питомца', 'pet_age');
        $rootSectionsIds['pet_size']         = $this->addSection('Размер питомца', 'pet_size');
        $rootSectionsIds['food_spec']        = $this->addSection('Специализация корма', 'food_spec');
        $rootSectionsIds['food_ingridient']  = $this->addSection('Особенности ингредиентов', 'food_ingridient');
        $rootSectionsIds['food_consistence'] = $this->addSection('Тип корма', 'food_consistence');
        $rootSectionsIds['food_flavour']     = $this->addSection('Вкус корма', 'food_flavour');
        
        //move all sections by dog
        $updateItemSections = [];
        $sectIdCode         = [];
        foreach ($sections as $sect) {
            if ($sect['PARENT_CODE'] === 'cat') {
                //установка связи разделов кошек и собак
                $updateItemSections[$sect['ID']]                   = 0;
                $sectIdCode[$sect['CODE'] . '_' . $sect['XML_ID']] = $sect['ID'];
            } else {
                //установка связи разделов кошек и собак
                $updateItemSections[$sectIdCode[$sect['CODE'] . '_' . $sect['XML_ID']]] = $sect['ID'];
                //привязка к новым корневым разделам разделов собак
                $this->helper->Iblock()->updateSection(
                    $sect['ID'],
                    [
                        'IBLOCK_SECTION_ID' => $rootSectionsIds[$sect['XML_ID']],
                        'XML_ID'            => $sect['ID'],
                    ]
                );
            }
        }
        
        //link cat sections by dog
        $res           = SectionElementTable::query()->setSelect(
            [
                'ITEM_ID'    => 'IBLOCK_ELEMENT_ID',
                'SECTION_ID' => 'IBLOCK_SECTION_ID',
            ]
        )->setFilter(
            ['IBLOCK_SECTION_ID' => array_keys($updateItemSections)]
        )->exec();
        $itemsSections = [];
        while ($item = $res->fetch()) {
            $itemsSections[$item['ITEM_ID']][] = $item['SECTION_ID'];
        }
        $obEl = new \CIBlockElement;
        foreach ($itemsSections as $itemId => $itemSections) {
            $newItemSections = [];
            if (\is_array($itemSections) && !empty($itemSections)) {
                foreach ($itemSections as $itemSection) {
                    $newItemSections[] = $updateItemSections[$itemSection];
                }
            }
            
            $obEl->SetElementSection($item['ITEM_ID'], $newItemSections);
        }
        
        //delete catType sections
        foreach ($updateItemSections as $oldSectId => $newSectID) {
            $this->helper->Iblock()->deleteSection($oldSectId);
        }
        
        //move petType sections
        foreach ($petTypeSection as $sectId) {
            $this->helper->Iblock()->updateSection(
                $sectId,
                [
                    'IBLOCK_SECTION_ID' => $rootSectionsIds['pet_type'],
                    'XML_ID'            => $sectId,
                ]
            );
        }
    }
    
    private function addSection(string $name, string $type = '', int $iblockSectionId = 0) : int
    {
        $data = [
            'ACTIVE'            => 'Y',
            'IBLOCK_SECTION_ID' => $iblockSectionId === 0 ? false : $iblockSectionId,
            'NAME'              => $name,
        ];
        if (!empty($type)) {
            $data['XML_ID'] = $type;
        }
        $data['CODE'] = \CUtil::translit($data['NAME'], 'ru');
        $id           = $this->helper->Iblock()->addSection(
            $this->iblockId,
            $data
        );
        
        return \is_bool($id) ? 0 : $id;
    }
    
    public function down()
    {
    }
}
