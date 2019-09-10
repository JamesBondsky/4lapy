<?php

namespace Sprint\Migration;


use Adv\Bitrixtools\Tools\HLBlock\HLBlockFactory;
use Bitrix\Highloadblock\DataManager;
use FourPaws\PersonalBundle\Entity\Pet;

class AddFieldExpertSenderIdPetTypeHl20190906150556 extends \Adv\Bitrixtools\Migration\SprintMigrationBase
{
    const HL_NAME = 'ForWho';

    const FIELD_NAME = 'UF_EXPERT_SENDER_ID';

    protected $description = 'Добавление поля "ID питомца в ExpertSender" в highload тип питомца';

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
                    'FIELD_NAME' => static::FIELD_NAME,
                    'USER_TYPE_ID' => 'integer',
                    'XML_ID' => static::FIELD_NAME,
                    'SORT' => 20,
                    'MULTIPLE' => 'N',
                    'MANDATORY' => 'N',
                    'SHOW_FILTER' => 'N',
                    'SHOW_IN_LIST' => 'Y',
                    'EDIT_IN_LIST' => 'Y',
                    'IS_SEARCHABLE' => 'N',
                    'SETTINGS' => [
                        'DEFAULT_VALUE' => '',
                        'MAX_VALUE' => 0,
                        'MIN_VALUE' => 0,
                        'SIZE' => 20,
                    ],
                    'EDIT_FORM_LABEL' => [
                        'ru' => 'ID питомца в ExpertSender',
                    ],
                    'LIST_COLUMN_LABEL' => [
                        'ru' => 'ID питомца в ExpertSender',
                    ],
                    'LIST_FILTER_LABEL' => [
                        'ru' => 'ID питомца в ExpertSender',
                    ],
                    'ERROR_MESSAGE' => [
                        'ru' => '',
                    ],
                    'HELP_MESSAGE' => [
                        'ru' => 'ID питомца в ExpertSender',
                    ],
                ]
            );


            /** @var DataManager $dataManager */
            try {
                $dataManager = HLBlockFactory::createTableObject(static::HL_NAME);

                $res = $dataManager::query()
                    ->setFilter(
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
                    )
                    ->setSelect(
                        [
                            'ID',
                            'UF_XML_ID',
                        ]
                    )->exec();

                while ($item = $res->Fetch()) {
                    $value = '';
                    switch ($item['UF_XML_ID']) {
                        case '3':
                            $value = 54;
                            break;
                        case '11':
                            $value = 55;
                            break;
                        case '7':
                            $value = 56;
                            break;
                        case '2':
                            $value = 58;
                            break;
                        case '10':
                            $value = 57;
                            break;
                        case '00000120':
                            $value = 59;
                            break;
                    }

                    $dataManager::update(
                        $item['ID'],
                        [
                            static::FIELD_NAME => $value,
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
