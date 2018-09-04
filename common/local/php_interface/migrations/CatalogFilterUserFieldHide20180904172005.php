<?php
/*
 * @copyright Copyright (c) ADV/web-engineering co.
 */

namespace Sprint\Migration;


use Adv\Bitrixtools\Migration\SprintMigrationBase;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;

class CatalogFilterUserFieldHide20180904172005 extends SprintMigrationBase
{
    protected $description = 'Добавление поля "не показывать в фильтре" для справочников фильтра';

    protected const FIELD_NAME = 'UF_HIDE_IN_FILTER';
    protected const FIELD      = [
        'USER_TYPE_ID'      => 'boolean',
        'XML_ID'            => 'UF_HIDE_IN_FILTER',
        'SORT'              => 30,
        'MULTIPLE'          => 'N',
        'MANDATORY'         => 'N',
        'SHOW_FILTER'       => 'N',
        'SHOW_IN_LIST'      => 'Y',
        'EDIT_IN_LIST'      => 'Y',
        'IS_SEARCHABLE'     => 'Y',
        'EDIT_FORM_LABEL'   => [
            'ru' => 'Не показывать в фильтре',
        ],
        'LIST_COLUMN_LABEL' => [
            'ru' => 'Не показывать в фильтре',
        ],
        'LIST_FILTER_LABEL' => [
            'ru' => 'Не показывать в фильтре',
        ],
        'SETTINGS'          => [
            'DEFAULT_VALUE' => false,
        ],
    ];

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
     * @return bool|void
     * @throws Exceptions\HelperException
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public function up()
    {
        /** @var \Sprint\Migration\Helpers\UserTypeEntityHelper $userTypeEntityHelper */
        $userTypeEntityHelper = $this->getHelper()->UserTypeEntity();
        /** @var \Sprint\Migration\Helpers\HlblockHelper $hlBlockHelper */
        $hlBlockHelper = $this->getHelper()->Hlblock();

        foreach (static::HL_BLOCK_NAMES as $name) {
            if (!$hlBlockId = $hlBlockHelper->getHlblockId($name)) {
                $this->log()->warning(\sprintf('HL block %s not found', $hlBlockId));
                continue;
            }

            $entityId = 'HLBLOCK_' . $hlBlockId;
            if ($userTypeEntityHelper->getUserTypeEntity($entityId, static::FIELD_NAME)) {
                $this->log()->warning(\sprintf('Field %s already exists in %s', static::FIELD_NAME, $name));
                continue;
            }

            if (!$userTypeEntityHelper->addUserTypeEntityIfNotExists($entityId, static::FIELD_NAME, static::FIELD)) {
                $this->log()->warning(\sprintf('Failed to add %s field to %s', static::FIELD_NAME, $name));
            }
        }
    }

    public function down()
    {
    }
}
