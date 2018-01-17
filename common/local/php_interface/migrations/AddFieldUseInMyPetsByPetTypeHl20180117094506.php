<?php

namespace Sprint\Migration;

use Adv\Bitrixtools\Tools\HLBlock\HLBlockFactory;

class AddFieldUseInMyPetsByPetTypeHl20180117094506 extends \Adv\Bitrixtools\Migration\SprintMigrationBase
{
    
    const HL_NAME = 'ForWho';
    
    protected $description = 'Добавление поля "использовать в моих питомцах" в highload тип питомца';
    
    public function up()
    {
        $helper = new HelperManager();
        
        $hlblockId = $helper->Hlblock()->getHlblockId(static::HL_NAME);
        $entityId  = 'HLBLOCK_' . $hlblockId;
        $helper->UserTypeEntity()->addUserTypeEntityIfNotExists(
            $entityId,
            'UF_USE_BY_PET',
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
        
        /** @var \Bitrix\Main\Entity\DataManager $dataManager */
        try {
            $dataManager = HLBlockFactory::createTableObject(static::HL_NAME);
            $res         =
                $dataManager::query()->setSelect(['ID'])->setFilter(
                    [
                        'UF_CODE' => [
                            'koshki',
                            'sobaki',
                            'ptitsy',
                            'gryzuny',
                            'ryby',
                            'prochee',
                        ],
                    ]
                )->exec();
            while ($item = $res->fetch()) {
                $dataManager::update($item['ID'], ['UF_USE_BY_PET' => 'Y']);
            }
        } catch (\Exception $e) {
        }
    }
    
    public function down()
    {
    }
    
}
