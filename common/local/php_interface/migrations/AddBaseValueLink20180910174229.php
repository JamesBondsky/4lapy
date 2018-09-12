<?php

namespace Sprint\Migration;


use Adv\Bitrixtools\Migration\SprintMigrationBase;

/**
 * Class AddBaseValueLink20180910174229
 * @package Sprint\Migration
 */
class AddBaseValueLink20180910174229 extends SprintMigrationBase
{

    protected $description = 'Добавление привязки к базовому значению в справочниках фильтра';

    /**
     * @var array
     */
    protected const HL_BLOCK_NAMES = [
        'ClothingSize',
        'ClothingSizeSelection',
        'Colour',
        'Consistence',
        'Country',
        'FeedSpec',
        'Flavour',
        'ForWho',
        'IngridientFeatures',
        'Maker',
        'Material',
        'PackageType',
        'ParasiteType',
        'Pet',
        'PetAge',
        'PetAgeAdditional',
        'PetBreed',
        'PetGender',
        'PetSize',
        'PetType',
        'PharmaGroup',
        'ProductCategory',
        'ProductForm',
        'Purpose',
        'Season',
        'TradeName',
        'Volume',
        'Year',
    ];

    /**
     *
     * @return bool|void
     */
    public function up()
    {
        $helper = new HelperManager();
        foreach (self::HL_BLOCK_NAMES as $HL_BLOCK_NAME) {
            $userField = false;
            $HLBlockId = $this->getHelper()->Hlblock()->getHlblockId($HL_BLOCK_NAME);
            if ($HLBlockId) {
                $entityId = 'HLBLOCK_' . $HLBlockId;
                $userField = $helper->UserTypeEntity()->getUserTypeEntity($entityId, 'UF_NAME');
            }
            if ($userField && $userField['ID']) {
                $helper->UserTypeEntity()->addUserTypeEntityIfNotExists($entityId, 'UF_BASE_VALUE', [
                    'FIELD_NAME' => 'UF_BASE_VALUE',
                    'USER_TYPE_ID' => 'hlblock',
                    'XML_ID' => 'UF_BASE_VALUE',
                    'SORT' => '1000',
                    'MULTIPLE' => 'N',
                    'MANDATORY' => 'N',
                    'SHOW_FILTER' => 'N',
                    'SHOW_IN_LIST' => 'Y',
                    'EDIT_IN_LIST' => 'Y',
                    'IS_SEARCHABLE' => 'N',
                    'SETTINGS' => [
                        'DISPLAY' => 'LIST',
                        'LIST_HEIGHT' => 5,
                        'HLBLOCK_ID' => $HLBlockId,
                        'HLFIELD_ID' => $userField['ID'],
                        'DEFAULT_VALUE' => 0,
                    ],
                    'EDIT_FORM_LABEL' => [
                        'ru' => 'Базовое значение',
                    ],
                    'LIST_COLUMN_LABEL' => [
                        'ru' => 'Базовое значение',
                    ],
                    'LIST_FILTER_LABEL' => [
                        'ru' => 'Базовое значение',
                    ],
                    'ERROR_MESSAGE' => [
                        'ru' => '',
                    ],
                    'HELP_MESSAGE' => [
                        'ru' => 'Базовое значение, использующиеся для объединения значений справочника для фильтра',
                    ],
                ]);
            } else {
                $this->log()->warning('У hl-блока ' . $HL_BLOCK_NAME . ' нет поля NAME');
            }
        }
    }

    /**
     * @return bool|void
     */
    public function down()
    {

    }

}
