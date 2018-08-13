<?php

namespace Sprint\Migration;

use Adv\Bitrixtools\Migration\SprintMigrationBase;
use Adv\Bitrixtools\Tools\HLBlock\HLBlockFactory;
use Bitrix\Main\SystemException;
use Bitrix\Main\Type\Date;
use FourPaws\App\Application;
use FourPaws\Migrator\Entity\MapTable;

class PetsImport20180809200000 extends SprintMigrationBase
{
    protected $description = 'Импорт данных о питомцах со старого сайта';

    /** @var bool Нужно ли обновлять ранее импортированные записи */
    protected $isExistsPetsUpdateEnabled = false;
    protected $scvPetsFilePath = '/local/php_interface/migration_sources/pets.csv';
    protected $scvBreedsMapFilePath = '/local/php_interface/migration_sources/breeds_map.csv';
    protected $scvLogFilePath = '/upload/pets_import_log_%datetime%.csv';
    protected $oldSitePhotoUrl = 'https://api.4lapy.ru';
    protected $petsHighloadBlockName = 'Pet';
    protected $categoriesHighloadBlockName = 'ForWho';
    protected $breedsHighloadBlockName = 'PetBreed';
    /** @var string $defaultPetsGenderEnumXmlId */
    protected $defaultPetsGenderEnumXmlId = 'U';
    /** @var array $genderExternalId2InternalXmlId */
    protected $genderExternalId2InternalXmlId = [
        // id на старом сайте => XML_ID значения списка на новом сайте
        '10890412' => 'M', // Мальчик
        '10890418' => 'F', // Девочка
        '10890421' => 'U', // Другое
    ];
    /** @var array $genderUfEnums */
    protected $genderUfEnums = [
        [
            'XML_ID' => 'M',
            'DEF' => 'N',
            'SORT' => '500',
            'VALUE' => 'Мальчик',
        ],
        [
            'XML_ID' => 'F',
            'DEF' => 'N',
            'SORT' => '500',
            'VALUE' => 'Девочка',
        ],
        [
            'XML_ID' => 'U',
            'DEF' => 'N',
            'SORT' => '500',
            'VALUE' => 'Другое',
        ],
    ];
    /** @var string $defaultCategoryInternalXmlId */
    protected $defaultCategoryInternalXmlId = '00000120';
    /** @var array $categoryExternalId2InternalXmlId */
    protected $categoryExternalId2InternalXmlId = [
        '10702' => [
            'HAS_BREEDS' => 'N',
            'EXTERNAL_NAME' => 'Рыбка',
            'INTERNAL_XML_ID' => '10',
        ],
        '10666' => [
            'HAS_BREEDS' => 'N',
            'EXTERNAL_NAME' => 'Грызун',
            'INTERNAL_XML_ID' => '2',
        ],
        '10672' => [
            'HAS_BREEDS' => 'Y',
            'EXTERNAL_NAME' => 'Кошка',
            'INTERNAL_XML_ID' => '3',
        ],
        '10681' => [
            'HAS_BREEDS' => 'N',
            'EXTERNAL_NAME' => 'Другое',
            'INTERNAL_XML_ID' => '00000120',
        ],
        '10687' => [
            'HAS_BREEDS' => 'Y',
            'EXTERNAL_NAME' => 'Собака',
            'INTERNAL_XML_ID' => '11',
        ],
        '10693' => [
            'HAS_BREEDS' => 'N',
            'EXTERNAL_NAME' => 'Птичка',
            'INTERNAL_XML_ID' => '7',
        ],
    ];

    /** @var \Bitrix\Highloadblock\DataManager $petsTableDataManager */
    private $petsTableDataManager = false;
    /** @var \Bitrix\Highloadblock\DataManager $breedsTableDataManager */
    private $breedsTableDataManager = false;
    /** @var array $mappingData */
    private $mappingData = [];
    /** @var resource $logFileRs */
    private $csvLogFileRs = false;

    public function __destruct()
    {
        $this->closeCsvLog();
    }

    /**
     * @return bool
     * @throws SystemException
     * @throws \Adv\Bitrixtools\Exception\IblockNotFoundException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Exception
     */
    public function up()
    {
        $this->updatePetsFields();
        $this->createPetsFields();
        $this->importBreeds();
        $this->importPets();

        return true;
    }
    
    public function down()
    {

    }

    /**
     * @param string $entityName
     * @param string $key
     * @param array $value
     */
    protected function addMappingData(string $entityName, string $key, array $value)
    {
        $this->mappingData[$entityName][$key] = $value;
    }

    /**
     * @param string $entityName
     * @param string $fieldName
     * @param $fieldValue
     * @return array
     */
    protected function getMappingDataByFieldValue(string $entityName, string $fieldName, $fieldValue)
    {
        $data = [];
        if ($this->mappingData[$entityName]) {
            foreach ($this->mappingData[$entityName] as $key => $value) {
                if (isset($value[$fieldName]) && $value[$fieldName] == $fieldValue) {
                    $data[$key] = $value;
                }
            }
        }

        return $data;
    }

    /**
     * @param string $entityName
     * @return array
     */
    protected function getMappingData(string $entityName)
    {
        return $this->mappingData[$entityName] ?? [];
    }

    /**
     * @param string $entityName
     * @return bool
     */
    protected function isMappingData(string $entityName)
    {
        return isset($this->mappingData[$entityName]);
    }

    /**
     * @param string $entityName
     */
    protected function initMappingData(string $entityName)
    {
        if (isset($this->mappingData[$entityName])) {
            $this->mappingData[$entityName] = [];
        }
    }

    /**
     * @param string $entityName
     */
    protected function flushMappingEntity(string $entityName)
    {
        if (isset($this->mappingData[$entityName])) {
            unset($this->mappingData[$entityName]);
        }
    }

    /**
     * @throws SystemException
     * @throws \Exception
     */
    protected function updatePetsFields()
    {
        // Проверка наличия необходимых значений в вариантах списка "Пол питомца"
        $addEnumValues = [];
        foreach ($this->genderUfEnums as $fields) {
            $addEnumValues[$fields['XML_ID']] = $fields;
        }

        $curEnumValues = $this->getPetsGenderEnumValues();
        foreach ($curEnumValues as $item) {
            if (isset($addEnumValues[$item['XML_ID']])) {
                unset($addEnumValues[$item['XML_ID']]);
            }
        }

        if ($addEnumValues) {
            $genderField = $this->getPetsField('UF_GENDER');

            $i = 0;
            foreach ($addEnumValues as $fields) {
                $curEnumValues['n' . $i++] = $fields;
            }
            $res = (new \CUserFieldEnum())->SetEnumValues($genderField['ID'], $curEnumValues);
            $this->flushPetsGenderEnumCache();
            if (!$res) {
                throw new SystemException(
                    $GLOBALS['APPLICATION']->GetException()->GetString()
                );
            }
        }
    }

    /**
     * @throws SystemException
     * @throws \Exception
     */
    protected function createPetsFields()
    {
        $helper = $this->getHelper();

        $entityId = $this->getPetsHlblockEntityId();

        $fieldCode = 'UF_XML_ID';
        $tmp = $helper->UserTypeEntity()->getUserTypeEntity($entityId, $fieldCode);
        if ($tmp) {
            return;
        }

        $helper->UserTypeEntity()->addUserTypeEntityIfNotExists(
            $entityId,
            $fieldCode,
            [
                'FIELD_NAME' => $fieldCode,
                'USER_TYPE_ID' => 'string',
                'XML_ID' => $fieldCode,
                'SORT' => '1000',
                'MULTIPLE' => 'N',
                'MANDATORY' => 'N',
                'SHOW_FILTER' => 'I',
                'SHOW_IN_LIST' => 'Y',
                'EDIT_IN_LIST' => 'Y',
                'IS_SEARCHABLE' => 'N',
                'SETTINGS' => [
                    'SIZE' => 15,
                    'ROWS' => 0,
                    'REGEXP' => '',
                    'MIN_LENGTH' => 0,
                    'MAX_LENGTH' => 0,
                    'DEFAULT_VALUE' => '',
                ],
                'EDIT_FORM_LABEL' => [
                    'ru' => 'XML_ID',
                ],
                'LIST_COLUMN_LABEL' => [
                    'ru' => 'XML_ID',
                ],
                'LIST_FILTER_LABEL' => [
                    'ru' => 'XML_ID',
                ],
                'ERROR_MESSAGE' => [
                    'ru' => '',
                ],
                'HELP_MESSAGE' => [
                    'ru' => 'XML_ID',
                ],
            ]
        );

        $tableName = $this->getPetsHlblockTableName();
        $connection = \Bitrix\Main\Application::getConnection();
        $connection->queryExecute('ALTER TABLE '.$tableName.' MODIFY '.$fieldCode.' VARCHAR(255)');
        $connection->queryExecute('CREATE INDEX ADV_IX_'.$fieldCode.' ON '.$tableName.' ('.$fieldCode.')');
    }

    /**
     * @throws SystemException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Exception
     */
    protected function importBreeds()
    {
        $breedsData = $this->getOldSiteBreeds();
        foreach ($breedsData as $item) {
            if ($item['OLD_ID'] && $item['NEW_XML_ID'] === '' && $item['NEW_NAME'] !== '') {
                if (!$this->findBreedsByName($item['OLD_NAME']) && !$this->findBreedsByName($item['NEW_NAME'])) {
                    $newId = $this->addBreed(
                        [
                            'UF_NAME' => $item['OLD_NAME'],
                            'UF_CODE' => $item['OLD_ID'],
                            'UF_XML_ID' => $item['OLD_ID'],
                        ]
                    );
                    if (!$newId) {
                        throw new SystemException('Breed not created');
                    }
                }
            }
        }
    }

    /**
     * @throws \Adv\Bitrixtools\Exception\IblockNotFoundException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     * @throws \Exception
     */
    protected function importPets()
    {
        $filePathAbs = Application::getAbsolutePath($this->scvPetsFilePath);

        $rs = fopen($filePathAbs, 'rb');
        if (!$rs) {
            throw new \RuntimeException(
                sprintf(
                    'Can not open file %s',
                    $filePathAbs
                )
            );
        }

        // добавление в лог первой строки с названием колонок
        $this->addCsvLogData(
            [
                'OLD_ID',
                'NEW_ID',
                'SUCCESS_SAVED',
                'NOTE',
            ]
        );

        $rowsMap = [];
        $firstRow = true;
        while ($row = fgetcsv($rs, null, ';', '"')) {
            if ($firstRow) {
                $firstRow = false;
                foreach ($row as $key => $fieldName) {
                    $rowsMap[trim($fieldName)] = $key;
                }
                continue;
            }

            $tmpKey = $rowsMap['ID'];
            $id = (int)$row[$tmpKey];

            $existsPetId = $this->isPetExists($id);
            if ($existsPetId && !$this->isExistsPetsUpdateEnabled) {
                continue;
            }

            $tmpKey = $rowsMap['NAME'];
            $name = trim($row[$tmpKey]);

            $tmpKey = $rowsMap['PROP_PET_BIRTHDAY'];
            $birthdayValue = trim($row[$tmpKey]);

            $tmpKey = $rowsMap['PROP_PET_CATEGORY'];
            $externalPetCategoryId = trim($row[$tmpKey]);

            $tmpKey = $rowsMap['PROP_PET_SEX'];
            $externalGenderId = trim($row[$tmpKey]);

            $tmpKey = $rowsMap['PROP_USER_ID'];
            $externalUserId = trim($row[$tmpKey]);

            $tmpKey = $rowsMap['PROP_PET_BREED'];
            $externalBreedId = trim($row[$tmpKey]);

            $tmpKey = $rowsMap['PROP_PET_BREED_OTHER'];
            $externalBreedOther = trim($row[$tmpKey]);

            $tmpKey = $rowsMap['PROP_PET_PHOTO'];
            $externalPetPhotoUrl = trim($row[$tmpKey]);

            $internalUserId = $this->getInternalUserId($externalUserId);

            $logErr = [];
            if ($name === '') {
                $name = 'undefined';
                $logErr[] = 'Не задана кличка питомца, установлено значение по умолчанию: '.$name;
            }

            $birthday = '';
            if ($birthdayValue !== '') {
                try {
                    $birthday = new Date($birthdayValue);
                } catch (\Exception $exception) {
                    $logErr[] = 'Ошибка при разборе даты рождения питомца, oldSiteBirthday: '.$birthdayValue;
                }
            }

            if ($internalUserId <= 0) {
                $logErr[] = 'Не удалось определить пользователя, oldSiteUserId: '.$externalUserId;
            }

            $genderInternalEnumId = $this->getPetsGenderEnumId($externalGenderId);
            if ($genderInternalEnumId <= 0) {
                $logErr[] = 'Не удалось сопоставить пол питомца, oldSiteGenderId: '.$externalGenderId;
                // если не удалось сопоставить, то устанавлвиаем пол по умолчанию: "Другое"
                $genderInternalEnumId = $this->getDefaultPetsGenderEnumId();
            }

            $internalPetCategory = $this->getCategoryByExternalId($externalPetCategoryId);
            if (!$internalPetCategory) {
                $logErr[] = 'Не удалось сопоставить тип питомца, oldSiteCategoryId: '.$externalPetCategoryId;
                // если не удалось сопоставить, то устанавливаем категорию по умолчанию: "Прочее"
                $internalPetCategory = $this->getDefaultCategory();
            }
            $internalPetCategoryId = $internalPetCategory['ID'] ?? 0;

            $breedName = '';
            if ($externalBreedId !== '') {
                $breed = $this->getOldSiteBreedByExternalId($externalBreedId);
                if ($breed) {
                    $breedName = $breed['NEW_NAME'] === '' ? $breed['OLD_NAME'] : $breed['NEW_NAME'];
                }
                if ($breedName === '') {
                    $logErr[] = 'Не удалось сопоставить породу питомца по внешнему коду, oldSiteBreedId: '.$externalBreedId;
                }
            }
            if ($breedName === '' && $externalBreedOther !== '') {
                $tmpList = $this->findBreedsByName($externalBreedOther);
                if (!$tmpList) {
                    $logErr[] = 'Не удалось сопоставить породу питомца по полю Порода (другое): '.$externalBreedOther;
                } else {
                    $tmp = reset($tmpList);
                    $breedName = $tmp['UF_NAME'];
                    $logErr[] = 'Сопоставлена порода питомца по полю Порода (другое): '.$externalBreedOther.' - '.$breedName;
                }
            }

            $photo = false;
            if ($externalPetPhotoUrl !== '') {
                $photo = \CFile::MakeFileArray($this->oldSitePhotoUrl.$externalPetPhotoUrl);
            }

            $resId = 0;
            if ($existsPetId) {
                try {
                    $resId = $this->updatePet(
                        $existsPetId,
                        [
                            'UF_XML_ID' => $id,
                            'UF_BIRTHDAY' => $birthday,
                            'UF_GENDER' => $genderInternalEnumId,
                            'UF_BREED' => $breedName,
                            'UF_TYPE' => $internalPetCategoryId,
                            'UF_NAME' => $name,
                            'UF_PHOTO' =>  $photo,
                            'UF_USER_ID' => $internalUserId,
                        ]
                    );
                } catch (\Exception $exception) {
                    $logErr[] = 'Ошибки в процессе обновления записи питомца: '.$exception->getMessage();
                }
            } else {
                try {
                    $resId = $this->addPet(
                        [
                            'UF_XML_ID' => $id,
                            'UF_BIRTHDAY' => $birthday,
                            'UF_GENDER' => $genderInternalEnumId,
                            'UF_BREED' => $breedName,
                            'UF_TYPE' => $internalPetCategoryId,
                            'UF_NAME' => $name,
                            'UF_PHOTO' =>  $photo,
                            'UF_USER_ID' => $internalUserId,
                        ]
                    );
                } catch (\Exception $exception) {
                    $logErr[] = 'Ошибки в процессе создания записи питомца: '.$exception->getMessage();
                }
            }

            $this->addCsvlogData(
                [
                    $id,
                    $existsPetId ? $existsPetId : $resId,
                    $resId ? 'Y' : 'N',
                    $logErr ? implode(' || ', $logErr) : '',
                ]
            );
        }

        $this->closeCsvLog();

        fclose($rs);
    }

    protected function initOldSiteBreeds()
    {
        if (!$this->isMappingData('oldSiteBreeds')) {
            $this->initMappingData('oldSiteBreeds');
            $filePathAbs = Application::getAbsolutePath($this->scvBreedsMapFilePath);

            $rs = fopen($filePathAbs, 'rb');
            if (!$rs) {
                throw new \RuntimeException(
                    sprintf(
                        'Can not open file %s',
                        $filePathAbs
                    )
                );
            }
            $rowsMap = [];
            $firstRow = true;
            while ($row = fgetcsv($rs, null, ';', '"')) {
                if ($firstRow) {
                    $firstRow = false;
                    foreach ($row as $key => $fieldName) {
                        $rowsMap[trim($fieldName)] = $key;
                    }
                    continue;
                }

                $tmpKey = $rowsMap['OLD_ID'];
                $oldId = (int)$row[$tmpKey];
                if ($oldId <= 0) {
                    continue;
                }

                $tmpKey = $rowsMap['OLD_NAME'];
                $oldName = trim($row[$tmpKey]);

                $tmpKey = $rowsMap['NEW_NAME'];
                $newName = trim($row[$tmpKey]);

                $tmpKey = $rowsMap['NEW_XML_ID'];
                $newXmlId = trim($row[$tmpKey]);

                $this->addMappingData(
                    'oldSiteBreeds',
                    $oldId,
                    [
                        'OLD_ID' => $oldId,
                        'OLD_NAME' => $oldName,
                        'NEW_NAME' => $newName,
                        'NEW_XML_ID' => $newXmlId,
                    ]
                );
            }

            fclose($rs);
        }
    }

    /**
     * @return array
     * @throws SystemException
     * @throws \Exception
     */
    protected function getOldSiteBreeds()
    {
        $this->initOldSiteBreeds();

        return $this->getMappingData('oldSiteBreeds');
    }

    /**
     * @param string $fieldName
     * @param $fieldValue
     * @return array
     * @throws SystemException
     * @throws \Exception
     */
    protected function getOldSiteBreedsMappingDataByFieldValue(string $fieldName, $fieldValue)
    {
        $this->initOldSiteBreeds();

        return $this->getMappingDataByFieldValue('oldSiteBreeds', $fieldName, $fieldValue);
    }

    /**
     * @param string $externalValue
     * @return array
     * @throws SystemException
     * @throws \Exception
     */
    protected function getOldSiteBreedByExternalId(string $externalValue)
    {
        $data = [];
        if ($externalValue !== '') {
            $data = reset($this->getOldSiteBreedsMappingDataByFieldValue('OLD_ID', $externalValue));
        }

        return $data;
    }

    /**
     * @throws SystemException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Exception
     */
    protected function initCategories()
    {
        if (!$this->isMappingData('categories')) {
            $this->initMappingData('categories');
            $hlblockTableDataManager = HLBlockFactory::createTableObject(
                $this->categoriesHighloadBlockName
            );
            $items = $hlblockTableDataManager::getList(
                [
                    'select' => [
                        'ID', 'UF_XML_ID',
                    ],
                ]
            );
            while ($item = $items->fetch()) {
                $item['ID'] = (int)$item['ID'];
                $this->addMappingData(
                    'categories',
                    $item['ID'],
                    [
                        'ID' => $item['ID'],
                        'UF_XML_ID' => trim($item['UF_XML_ID']),
                    ]
                );
            }
        }
    }

    /**
     * @return array
     * @throws SystemException
     * @throws \Exception
     */
    protected function getCategories()
    {
        $this->initCategories();

        return $this->getMappingData('categories');
    }

    /**
     * @param string $fieldName
     * @param $fieldValue
     * @return array
     * @throws SystemException
     * @throws \Exception
     */
    protected function getCategoriesMappingDataByFieldValue(string $fieldName, $fieldValue)
    {
        $this->initCategories();

        return $this->getMappingDataByFieldValue('categories', $fieldName, $fieldValue);
    }

    /**
     * @param string $externalValue
     * @return array
     * @throws SystemException
     * @throws \Exception
     */
    protected function getCategoryByExternalId(string $externalValue)
    {
        $data = [];
        if ($externalValue !== '') {
            $rels = $this->categoryExternalId2InternalXmlId[$externalValue] ?? [];
            if ($rels) {
                $data = reset($this->getCategoriesMappingDataByFieldValue('UF_XML_ID', $rels['INTERNAL_XML_ID']));
                /*
                if ($data) {
                    $data['__HAS_BREEDS'] = $rels['HAS_BREEDS'];
                    $data['__EXTERNAL_NAME'] = $rels['EXTERNAL_NAME'];
                }
                */
            }
        }

        return $data;
    }

    /**
     * @return array
     * @throws SystemException
     * @throws \Exception
     */
    protected function getDefaultCategory()
    {
        $data = reset($this->getCategoriesMappingDataByFieldValue('UF_XML_ID', $this->defaultCategoryInternalXmlId));
        /*
        if ($data) {
            $data['__HAS_BREEDS'] = 'N';
            $data['__EXTERNAL_NAME'] = '';
        }
        */

        return $data;
    }

    /**
     * @throws SystemException
     * @throws \Exception
     */
    protected function initPetsGenderEnumValues()
    {
        if (!$this->isMappingData('genderEnum')) {
            $this->initMappingData('genderEnum');
            $genderField = $this->getPetsField('UF_GENDER');
            if ($genderField && $genderField['USER_TYPE_ID'] == 'enumeration') {
                $items = (new \CUserFieldEnum())->GetList(
                    [
                        'ID' => 'ASC'
                    ],
                    [
                        'USER_FIELD_ID' => $genderField['ID']
                    ]
                );
                while ($item = $items->Fetch()) {
                    $this->addMappingData(
                        'genderEnum',
                        $item['ID'],
                        [
                            'ID' => (int)$item['ID'],
                            'XML_ID' => $item['XML_ID'],
                            'VALUE' => $item['VALUE'],
                            'DEF' => $item['DEF'],
                            'SORT' => (int)$item['SORT'],
                        ]
                    );
                }
            }
        }
    }

    protected function flushPetsGenderEnumCache()
    {
        $this->flushMappingEntity('genderEnum');
    }

    /**
     * @return array
     * @throws SystemException
     * @throws \Exception
     */
    protected function getPetsGenderEnumValues()
    {
        $this->initPetsGenderEnumValues();

        return $this->getMappingData('genderEnum');
    }

    /**
     * @param string $fieldName
     * @param $fieldValue
     * @return array
     * @throws SystemException
     * @throws \Exception
     */
    protected function getPetsGenderMappingDataByFieldValue(string $fieldName, $fieldValue)
    {
        $this->initPetsGenderEnumValues();

        return $this->getMappingDataByFieldValue('genderEnum', $fieldName, $fieldValue);
    }

    /**
     * @param string $externalValue
     * @return array
     * @throws SystemException
     * @throws \Exception
     */
    protected function getPetsGenderEnumByExternalId(string $externalValue)
    {
        $data = [];
        if ($externalValue !== '') {
            $enumXmlId = $this->genderExternalId2InternalXmlId[$externalValue] ?? '';
            if ($enumXmlId !== '') {
                $this->initPetsGenderEnumValues();
                $data = reset($this->getPetsGenderMappingDataByFieldValue('XML_ID', $enumXmlId));
            }
        }

        return $data;
    }

    /**
     * @param string $externalValue
     * @return int
     * @throws SystemException
     * @throws \Exception
     */
    protected function getPetsGenderEnumId(string $externalValue)
    {
        $data = $this->getPetsGenderEnumByExternalId($externalValue);

        return $data ? $data['ID'] : 0;
    }

    /**
     * @return int
     * @throws SystemException
     * @throws \Exception
     */
    protected function getDefaultPetsGenderEnumId()
    {
        $data = reset($this->getPetsGenderMappingDataByFieldValue('XML_ID', $this->defaultPetsGenderEnumXmlId));

        return $data ? $data['ID'] : 0;
    }

    /**
     * @param string $externalValue
     * @return int
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\SystemException
     */
    protected function getInternalUserId(string $externalValue)
    {
        if ($externalValue !== '') {
            return (int)MapTable::getInternalIdByExternalId($externalValue, 'user');
        }

        return 0;
    }

    /**
     * @param string $hlblockName
     * @return \Bitrix\Highloadblock\DataManager
     * @throws SystemException
     * @throws \Exception
     */
    protected function createHlblockTableDataManager(string $hlblockName)
    {
        $hlblockTableDataManager = HLBlockFactory::createTableObject(
            $hlblockName
        );

        if (!$hlblockTableDataManager) {
            throw new SystemException(
                sprintf(
                    '%s highload block not found',
                    $hlblockName
                )
            );
        }

        if (!($hlblockTableDataManager instanceof \Bitrix\Highloadblock\DataManager)) {
            throw new SystemException('Wrong instance');
        }

        return $hlblockTableDataManager;
    }

    /**
     * @return \Bitrix\Highloadblock\DataManager
     * @throws SystemException
     * @throws \Exception
     */
    protected function getPetsTableDataManager()
    {
        if ($this->petsTableDataManager === false) {
            $this->petsTableDataManager = $this->createHlblockTableDataManager(
                $this->petsHighloadBlockName
            );
        }

        return $this->petsTableDataManager;
    }

    /**
     * @return int
     * @throws SystemException
     * @throws \Exception
     */
    protected function getPetsHlblockId()
    {
        $petsTableDataManager = $this->getPetsTableDataManager();
        $hlblockFields = $petsTableDataManager::getHighloadBlock();
        if (!$hlblockFields) {
            throw new SystemException('Highload block not inited');
        }

        return (int)$hlblockFields['ID'];
    }

    /**
     * @return string
     * @throws SystemException
     * @throws \Exception
     */
    protected function getPetsHlblockEntityId()
    {
        $hlblockId = $this->getPetsHlblockId();

        return 'HLBLOCK_' . $hlblockId;
    }

    /**
     * @return mixed
     * @throws SystemException
     * @throws \Exception
     */
    protected function getPetsHlblockTableName()
    {
        $petsTableDataManager = $this->getPetsTableDataManager();
        $hlblockFields = $petsTableDataManager::getHighloadBlock();
        if (!$hlblockFields) {
            throw new SystemException('Highload block not inited');
        }

        return $hlblockFields['TABLE_NAME'];
    }

    /**
     * @param string $fieldName
     * @return array|bool
     * @throws SystemException
     * @throws \Exception
     */
    protected function getPetsField(string $fieldName)
    {
        $userTypeEntityHelper = $this->getHelper()->UserTypeEntity();
        $entityId = $this->getPetsHlblockEntityId();
        $field = $userTypeEntityHelper->getUserTypeEntity($entityId, $fieldName);

        return $field;
    }

    /**
     * @return \Bitrix\Highloadblock\DataManager
     * @throws SystemException
     * @throws \Exception
     */
    protected function getBreedsTableDataManager()
    {
        if ($this->breedsTableDataManager === false) {
            $this->breedsTableDataManager = $this->createHlblockTableDataManager(
                $this->breedsHighloadBlockName
            );
        }

        return $this->breedsTableDataManager;
    }

    /**
     * @return int
     * @throws SystemException
     * @throws \Exception
     */
    protected function getBreedsHlblockId()
    {
        $tableDataManager = $this->getBreedsTableDataManager();
        $hlblockFields = $tableDataManager::getHighloadBlock();
        if (!$hlblockFields) {
            throw new SystemException('Highload block not inited');
        }

        return (int)$hlblockFields['ID'];
    }

    /**
     * @return string
     * @throws SystemException
     * @throws \Exception
     */
    protected function getBreedsHlblockEntityId()
    {
        $hlblockId = $this->getBreedsHlblockId();

        return 'HLBLOCK_' . $hlblockId;
    }

    /**
     * @return mixed
     * @throws SystemException
     * @throws \Exception
     */
    protected function getBreedsHlblockTableName()
    {
        $tableDataManager = $this->getBreedsTableDataManager();
        $hlblockFields = $tableDataManager::getHighloadBlock();
        if (!$hlblockFields) {
            throw new SystemException('Highload block not inited');
        }

        return $hlblockFields['TABLE_NAME'];
    }

    /**
     * @param string $fieldName
     * @return array|bool
     * @throws SystemException
     * @throws \Exception
     */
    protected function getBreedsField(string $fieldName)
    {
        $userTypeEntityHelper = $this->getHelper()->UserTypeEntity();
        $entityId = $this->getBreedsHlblockEntityId();
        $field = $userTypeEntityHelper->getUserTypeEntity($entityId, $fieldName);

        return $field;
    }

    /**
     * @param string $xmlId
     * @return int
     * @throws SystemException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Exception
     */
    protected function isPetExists(string $xmlId)
    {
        $xmlId = trim($xmlId);
        if ($xmlId === '') {
            return 0;
        }

        $dataManager = $this->getPetsTableDataManager();
        $item = $dataManager::getList(
            [
                'order' => [
                    'ID' => 'asc',
                ],
                'filter' => [
                    '=UF_XML_ID' => $xmlId,
                ],
                'select' => [
                    'ID'
                ]
            ]
        )->fetch();

        return $item ? (int)$item['ID'] : 0;
    }

    /**
     * @param int $id
     * @param array $fields
     * @return int
     * @throws SystemException
     * @throws \Exception
     */
    protected function updatePet(int $id, array $fields)
    {
        $dataManager = $this->getPetsTableDataManager();

        \FourPaws\PersonalBundle\EventController\Event::disableHandler('petUpdateManzana');
        $result = $dataManager::update($id, $fields);
        \FourPaws\PersonalBundle\EventController\Event::enableHandler('petUpdateManzana');
        if ($result->isSuccess()) {
            $newId = (int)$result->getId();
        } else {
            throw new SystemException(
                implode("\n", $result->getErrorMessages()),
                200
            );
        }

        return $newId;
    }

    /**
     * @param array $fields
     * @return int
     * @throws SystemException
     * @throws \Exception
     */
    protected function addPet(array $fields)
    {
        $dataManager = $this->getPetsTableDataManager();

        \FourPaws\PersonalBundle\EventController\Event::disableHandler('petUpdateManzana');
        $result = $dataManager::add($fields);
        \FourPaws\PersonalBundle\EventController\Event::enableHandler('petUpdateManzana');
        if ($result->isSuccess()) {
            $newId = (int)$result->getId();
        } else {
            throw new SystemException(
                implode("\n", $result->getErrorMessages()),
                100
            );
        }

        return $newId;
    }
    /**
     * @param string $breedName
     * @return array
     * @throws SystemException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Exception
     */
    protected function findBreedsByName(string $breedName)
    {
        $list = [];
        $breedName = trim($breedName);
        if ($breedName === '') {
            return $list;
        }

        $dataManager = $this->getBreedsTableDataManager();
        $items = $dataManager::getList(
            [
                'order' => [
                    'ID' => 'asc',
                ],
                'filter' => [
                    'UF_NAME' => $breedName,
                ],
                'select' => [
                    'ID', 'UF_NAME', 'UF_XML_ID', 'UF_CODE',
                ]
            ]
        );
        while ($item = $items->fetch()) {
            $list[] = $item;
        }

        return $list;
    }

    /**
     * @param array $fields
     * @return int
     * @throws SystemException
     * @throws \Exception
     */
    protected function addBreed(array $fields)
    {
        $dataManager = $this->getBreedsTableDataManager();

        $result = $dataManager::add($fields);
        if ($result->isSuccess()) {
            $newId = (int)$result->getId();
        } else {
            throw new SystemException(
                implode("\n", $result->getErrorMessages()),
                100
            );
        }

        return $newId;
    }

    /**
     * @return null|resource
     */
    protected function getCsvLogFileRs()
    {
        if ($this->csvLogFileRs === false) {
            $this->csvLogFileRs = null;
            if ($this->scvLogFilePath !== '') {
                $logPathAbs = Application::getAbsolutePath(
                    str_replace(
                        ['%datetime%'],
                        date('Ymd-His'),
                        $this->scvLogFilePath
                    )
                );
                $this->csvLogFileRs = fopen($logPathAbs, 'wb');
                if (!$this->csvLogFileRs) {
                    throw new \RuntimeException(
                        sprintf(
                            'Can not open file %s',
                            $logPathAbs
                        )
                    );
                }
            }
        }

        return $this->csvLogFileRs;
    }

    protected function closeCsvLog()
    {
        if ($this->csvLogFileRs) {
            fclose($this->csvLogFileRs);
            $this->csvLogFileRs = false;
        }
    }

    /**
     * @param array $logRow
     */
    protected function addCsvLogData(array $logRow)
    {
        $logFileRs = $this->getCsvLogFileRs();
        if ($logFileRs) {
            fputcsv($logFileRs, $logRow, ';', '"');
        }
    }
}
