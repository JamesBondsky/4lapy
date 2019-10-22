<?php

namespace Sprint\Migration;


use Adv\Bitrixtools\Tools\HLBlock\HLBlockFactory;
use FourPaws\App\Application;
use FourPaws\AppBundle\Entity\BaseEntity;
use FourPaws\LocationBundle\LocationService;
use FourPaws\PersonalBundle\Repository\OldAddressRepository;

class UpdateMoscowAddress20191015182204 extends \Adv\Bitrixtools\Migration\SprintMigrationBase
{
    protected $description = 'Обновляет код локации у Московских адресов';

    protected const LIMIT = 500;

    protected const FIELD_NAME = 'UF_DADATA_CHECKED';

    protected const HL_BLOCK_NAME = 'Address';

    public function up(): bool
    {
        if (!$this->addNewFieldIfNotExist()) {
            $this->log()->error(sprintf('Не удалось добавить поле %s в HL блок %s', self::FIELD_NAME, self::HL_BLOCK_NAME));
            return false;
        }

        /** @var LocationService $locationService */
        $locationService = Application::getInstance()->getContainer()->get('location.service');

        try {
            $dataManager = HLBlockFactory::createTableObject(OldAddressRepository::HL_NAME);
            $res = $dataManager::query()
                ->setFilter([
                    '=UF_CITY_LOCATION' => LocationService::LOCATION_CODE_MOSCOW,
                    '=' . self::FIELD_NAME => BaseEntity::BITRIX_FALSE,
                ])
                ->setSelect(['ID', 'UF_CITY', 'UF_STREET', 'UF_HOUSE'])
                ->exec();

            $i = 0;

            while (($address = $res->Fetch()) && ($i < self::LIMIT)) {
                $writeLog = false;

                if (($this->validateField($address['UF_CITY'])) && ($this->validateField($address['UF_STREET'])) && ($this->validateField($address['UF_HOUSE']))) {
                    $strAddress = sprintf('%s, %s, %s', $address['UF_CITY'], $address['UF_STREET'], $address['UF_HOUSE']);
                    try {
                        $okato = $locationService->getDadataLocationOkato($strAddress);
                        $i++;
                        $locations = $locationService->findLocationByExtService(LocationService::OKATO_SERVICE_CODE, $okato);

                        $fieldsToUpdate = [
                            self::FIELD_NAME => BaseEntity::BITRIX_TRUE
                        ];

                        if (count($locations)) {
                            $location = current($locations);

                            if (($locationCode = $location['CODE']) && ($this->validateField($locationCode))) {
                                $fieldsToUpdate['UF_CITY_LOCATION'] = $locationCode;
                                $writeLog = true;
                            }
                        }

                        $updateResult = $dataManager::update($address['ID'], $fieldsToUpdate);

                        if ($updateResult->isSuccess() && $writeLog) {
                            $this->log()->info(
                                sprintf('Обновлен адрес: id - %s, новый UF_CITY_LOCATION - %s', $address['ID'], $locationCode)
                            );
                        }

                    } catch (\Exception $e) {
                        continue;
                    }
                }
            }
        } catch (\Exception $e) {
            $this->log()->error(sprintf('При обновлении адресов произошла ошибка - %s', $e->getMessage()));
            return false;
        }

        return ($i < self::LIMIT);
    }

    protected function addNewFieldIfNotExist(): bool
    {
        $helper = new HelperManager();

        $hlblockId = $helper->Hlblock()->getHlblockId(self::HL_BLOCK_NAME);

        $entityId = 'HLBLOCK_' . $hlblockId;

        $fieldId = $helper->UserTypeEntity()->addUserTypeEntityIfNotExists($entityId, self::FIELD_NAME, [
            'FIELD_NAME' => self::FIELD_NAME,
            'USER_TYPE_ID' => 'boolean',
            'XML_ID' => '',
            'SORT' => '100',
            'MULTIPLE' => 'N',
            'MANDATORY' => 'N',
            'SHOW_FILTER' => 'I',
            'SHOW_IN_LIST' => 'Y',
            'EDIT_IN_LIST' => 'Y',
            'IS_SEARCHABLE' => 'N',
            'SETTINGS' =>
                [
                    'DEFAULT_VALUE' => 0,
                    'DISPLAY' => 'CHECKBOX',
                    'LABEL' =>
                        [
                            0 => '',
                            1 => '',
                        ],
                    'LABEL_CHECKBOX' => '',
                ],
            'EDIT_FORM_LABEL' =>
                [
                    'ru' => 'Проверено дадатой',
                ],
            'LIST_COLUMN_LABEL' =>
                [
                    'ru' => 'Проверено дадатой',
                ],
            'LIST_FILTER_LABEL' =>
                [
                    'ru' => 'Проверено дадатой',
                ],
            'ERROR_MESSAGE' =>
                [
                    'ru' => '',
                ],
            'HELP_MESSAGE' =>
                [
                    'ru' => '',
                ],
        ]);

        return (bool)$fieldId;
    }

    /**
     * @param $value
     * @return bool
     */
    protected function validateField($value): bool
    {
        return $value && ($value !== null) && (!empty($value)) && ($value !== '');
    }

    public function down()
    {

    }
}
