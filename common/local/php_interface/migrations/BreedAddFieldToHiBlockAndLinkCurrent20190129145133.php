<?php

namespace Sprint\Migration;

use Sprint\Migration\Helpers\HlblockHelper;
use Sprint\Migration\Helpers\UserTypeEntityHelper;
use Adv\Bitrixtools\Tools\HLBlock\HLBlockFactory;
use CUserFieldEnum;


/**
 * Class BreedAddFieldToHiBlockAndLinkCurrent20190129145133
 * @package Sprint\Migration
 */
class BreedAddFieldToHiBlockAndLinkCurrent20190129145133 extends \Adv\Bitrixtools\Migration\SprintMigrationBase {

    /**
     * @var string
     */
    protected $description = "Создаёт поле \"Привязка к элементу\" для связи ИБ Пород и ИБ Типы питомца, а также проставляет эту связь для текущих ";

    /**
     * @var UserTypeEntityHelper
     */
    private $userTypeEntityHelper;
    /**
     * @var HlblockHelper
     */
    private $hlBlockHelper;

    /**
     * @var int
     */
    private $petTypeIblockId;
    /**
     * @var int
     */
    private $petBreedIblockId;
    /**
     * @var int
     */
    private $petsIblockId;

    /**
     *
     */
    protected const HL_PET_TYPE = 'ForWho';
    /**
     *
     */
    protected const HL_PET_BREED = 'PetBreed';

    protected const HL_PETS = 'Pet';

    /**
     * @var array
     */
    protected $petTypes = [
        'cats' => 13,
        'dogs' => 14,
        'birds' => 16,
        'rodents' => 17,
        'fish' => 18,
        'other' => 21,
    ];

    /*protected $petTypes = [
        'cats' => 'Кошки',
        'dogs' => 'Собаки',
        'birds' => 'Птицы',
        'rodents' => 'Грызуны',
        'fish' => 'Рыбы',
        'other' => 'Прочее',
    ];*/

    /**
     * ID пород, привязываемых к типу питомцев.
     * Если элемент является массивом, то он трактуется как предел от/до
     */
    protected $petBreeds = [
        'cats' => [
            [84, 87],
            89, 90, 91, 114,
            [120, 142],
            143,

        ],
        'dogs' => [
            [1, 83],
            88,
            [92, 113],
            [115, 119],
            144, 145,
        ],
        'birds' => [
            [157, 167]
        ],
        'rodents' => [
            [146, 156],
            181
        ],
        'fish' => [
            [168, 180],
        ],
        'other' => [
            [182, 195]
        ],
    ];

    /**
     * BreedAddFieldToHiBlockAndLinkCurrent20190129145133 constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->userTypeEntityHelper = $this->getHelper()->UserTypeEntity();
        $this->hlBlockHelper = $this->getHelper()->Hlblock();

        $this->petTypeIblockId = $this->hlBlockHelper->getHlblockId(static::HL_PET_TYPE);
        $this->petBreedIblockId = $this->hlBlockHelper->getHlblockId(static::HL_PET_BREED);
        $this->petsIblockId = $this->hlBlockHelper->getHlblockId(static::HL_PETS);
    }

    /**
     * @return bool|void
     * @throws \Exception
     */
    public function up()
    {
        $obEntity = new \CUserTypeEntity;
        $uLinkField = $obEntity->GetList([], ['ENTITY_ID' => 'HLBLOCK_'.$this->petTypeIblockId, 'FIELD_NAME' => 'UF_NAME'])->Fetch();

        $ufield = [
            'FIELD_NAME' => 'UF_PET_TYPE',
            'USER_TYPE_ID' => 'hlblock',
            'XML_ID' => 'UF_PET_TYPE',
            'SORT' => 500,
            'MULTIPLE' => 'N',
            'MANDATORY' => 'N',
            'SHOW_FILTER' => 'N',
            'SHOW_IN_LIST' => 'Y',
            'EDIT_IN_LIST' => 'Y',
            'IS_SEARCHABLE' => 'N',
            'SETTINGS' => [
                "DISPLAY" => "LIST",
                "LIST_HEIGHT" => 5,
                "HLBLOCK_ID" => $this->petTypeIblockId,
                "HLFIELD_ID" => $uLinkField['ID'],
                "DEFAULT_VALUE" => 0,
            ],
            "EDIT_FORM_LABEL" => [
                "ru" => "Тип питомца"
            ],
            "LIST_COLUMN_LABEL" => [
                "ru" => "Тип питомца"
            ],
            "LIST_FILTER_LABEL" => [
                "ru" => ""
            ],
            "ERROR_MESSAGE" => [
                "ru" => ""
            ],
            "HELP_MESSAGE" => [
                "ru" => ""
            ]
        ];
        $this->addField("HLBLOCK_".$this->petBreedIblockId, $ufield);

        $uLinkField = $obEntity->GetList([], ['ENTITY_ID' => 'HLBLOCK_'.$this->petBreedIblockId, 'FIELD_NAME' => 'UF_NAME'])->Fetch();
        $ufield = [
            'FIELD_NAME' => 'UF_BREED_ID',
            'USER_TYPE_ID' => 'hlblock',
            'XML_ID' => 'UF_BREED_ID',
            'SORT' => 500,
            'MULTIPLE' => 'N',
            'MANDATORY' => 'N',
            'SHOW_FILTER' => 'N',
            'SHOW_IN_LIST' => 'Y',
            'EDIT_IN_LIST' => 'Y',
            'IS_SEARCHABLE' => 'N',
            'SETTINGS' => [
                "DISPLAY" => "LIST",
                "LIST_HEIGHT" => 5,
                "HLBLOCK_ID" => $this->petBreedIblockId,
                "HLFIELD_ID" => $uLinkField['ID'],
                "DEFAULT_VALUE" => 0,
            ],
            "EDIT_FORM_LABEL" => [
                "ru" => "ID Породы"
            ],
            "LIST_COLUMN_LABEL" => [
                "ru" => "ID Породы"
            ],
            "LIST_FILTER_LABEL" => [
                "ru" => ""
            ],
            "ERROR_MESSAGE" => [
                "ru" => ""
            ],
            "HELP_MESSAGE" => [
                "ru" => ""
            ]
        ];
        $this->addField("HLBLOCK_".$this->petsIblockId, $ufield);

        $obPetBreed = HLBlockFactory::createTableObject(self::HL_PET_BREED);

        foreach($this->petBreeds as $type => $breedIds){
            foreach($breedIds as $breedId){
                $petTypeId = $this->petTypes[$type];
                if(is_array($breedId)){
                    for($id = $breedId[0]; $id <= $breedId[1]; $id++){
                        $r = $obPetBreed->update($id, ['UF_PET_TYPE' => $petTypeId]);
                        if(!$r->isSuccess()){
                            $this->log()->error(sprintf('Не удалось обновить элемент %s', $id));
                        }
                    }
                }
                else{
                    $r = $obPetBreed->update($breedId, ['UF_PET_TYPE' => $petTypeId]);
                    if(!$r->isSuccess()){
                        $this->log()->error(sprintf('Не удалось обновить элемент %s', $breedId));
                    }
                }
            }
        }
    }

    /**
     * @return bool
     */
    public function down()
    {
        $this->deleteField("HLBLOCK_".$this->petBreedIblockId, 'UF_PET_TYPE');
        $this->deleteField("HLBLOCK_".$this->petsIblockId, 'UF_BREED_ID');

        return true;
    }

    /**
     * @param $entityId
     * @param $field
     * @return bool
     */
    protected function addField($entityId, $field): bool
    {
        if ($fieldId = $this->userTypeEntityHelper->addUserTypeEntityIfNotExists(
            $entityId,
            $field['FIELD_NAME'],
            $field
        )) {
            $this->log()->info(sprintf(
                'Добавлено поле %s в HL-блок %s',
                $field['FIELD_NAME'],
                $entityId
            ));
        } else {
            $this->log()->error(sprintf(
                'Ошибка при добавлении поля %s в HL-блок %s',
                $field['FIELD_NAME'],
                $entityId
            ));
            return false;
        }

        if (isset($field['ENUMS'])) {
            $enum = new CUserFieldEnum();
            if ($enum->SetEnumValues($fieldId, $field['ENUMS'])) {
                $this->log()->info(sprintf('Добавлены значения для поля %s', $field['FIELD_NAME']));
            } else {
                $this->log()->error(sprintf('Не удалось добавить значения для поля %s', $field['FIELD_NAME']));
            }
        }

        return true;
    }

    /**
     * @param $entityId
     * @param $fieldName
     * @return bool
     */
    protected function deleteField($entityId, $fieldName): bool
    {
        if ($this->userTypeEntityHelper->deleteUserTypeEntityIfExists($entityId, $fieldName)) {
            $this->log()->info(sprintf(
                'Удалено поле %s из HL-блока %s',
                $fieldName,
                $entityId
            ));
        } else {
            $this->log()->error(sprintf(
                'Ошибка при удалении поля %s из HL-блока %s',
                $fieldName,
                $entityId
            ));
        }

        return true;
    }

}
