<?php

namespace Sprint\Migration;

use Adv\Bitrixtools\Migration\SprintMigrationBase;
use Adv\Bitrixtools\Tools\HLBlock\HLBlockFactory;
use Bitrix\Main\Entity\DataManager;

class AddFieldUseInMyPetsByPetTypeHl20180117094506 extends SprintMigrationBase
{
    
    const HL_NAME    = 'ForWho';
    
    const FIELD_NAME = 'UF_USE_BY_PET';
    
    protected $description = 'Добавление поля "использовать в моих питомцах" в highload тип питомца';
    
    public function up()
    {
        $helper = new HelperManager();
        
        $hlblockId = (int)$helper->Hlblock()->getHlblockId(static::HL_NAME);
        if ($hlblockId > 0) {
            $entityId = 'HLBLOCK_' . $hlblockId;
            $helper->UserTypeEntity()->addUserTypeEntityIfNotExists(
                $entityId,
                static::FIELD_NAME,
                [
                    'FIELD_NAME'        => 'UF_USE_BY_PET',
                    'USER_TYPE_ID'      => 'boolean',
                    'XML_ID'            => '',
                    'SORT'              => '1000',
                    'MULTIPLE'          => 'N',
                    'MANDATORY'         => 'N',
                    'SHOW_FILTER'       => 'N',
                    'SHOW_IN_LIST'      => 'Y',
                    'EDIT_IN_LIST'      => 'Y',
                    'IS_SEARCHABLE'     => 'N',
                    'SETTINGS'          => [
                        'DEFAULT_VALUE'  => '1',
                        'DISPLAY'        => 'CHECKBOX',
                        'LABEL'          => [
                            0 => '',
                            1 => '',
                        ],
                        'LABEL_CHECKBOX' => '',
                    ],
                    'EDIT_FORM_LABEL'   => [
                        'ru' => 'Использовать для моих питомцев',
                    ],
                    'LIST_COLUMN_LABEL' => [
                        'ru' => 'Использовать для моих питомцев',
                    ],
                    'LIST_FILTER_LABEL' => [
                        'ru' => 'Использовать для моих питомцев',
                    ],
                    'ERROR_MESSAGE'     => [
                        'ru' => '',
                    ],
                    'HELP_MESSAGE'      => [
                        'ru' => 'Использовать для моих питомцев',
                    ],
                ]
            );
            
            /** @var DataManager $dataManager */
            try {
                $dataManager = HLBlockFactory::createTableObject(static::HL_NAME);
                $res         =
                    $dataManager::query()->setSelect(
                        [
                            'ID',
                            'XML_ID',
                        ]
                    )->setFilter(
                        [
                            'UF_XML_ID' => [
                                '3',
                                '11',
                                '7',
                                '2',
                                '10',
                                '00000120',
                            ],
                        ]
                    )->exec();
                while ($item = $res->fetch()) {
                    $code = '';
                    switch ($item['XML_ID']) {
                        case '3':
                            $code = 'koshki';
                            break;
                        case '11':
                            $code = 'sobaki';
                            break;
                        case '7':
                            $code = 'ptitsy';
                            break;
                        case '2':
                            $code = 'gryzuny';
                            break;
                        case '10':
                            $code = 'ryby';
                            break;
                        case '00000120':
                            $code = 'prochee';
                            break;
                    }
                    $dataManager::update(
                        $item['ID'],
                        [
                            'UF_USE_BY_PET' => 'Y',
                            'UF_CODE'       => $code,
                        ]
                    );
                }
            } catch (\Exception $e) {
            }
        }
    }
    
    public function down()
    {
        $helper = new HelperManager();
        
        $hlblockId = (int)$helper->Hlblock()->getHlblockId(static::HL_NAME);
        if ($hlblockId > 0) {
            $entityId = 'HLBLOCK_' . $hlblockId;
            
            $helper->UserTypeEntity()->deleteUserTypeEntityIfExists($entityId, static::FIELD_NAME);
        }
    }
    
}
