<?php

namespace Sprint\Migration;

use Bitrix\Sale\Internals\OrderPropsTable;
use CUserFieldEnum;
use FourPaws\App\Application;
use FourPaws\Enum\HlblockCode;
use FourPaws\StoreBundle\Entity\DeliverySchedule;
use Adv\Bitrixtools\Migration\SprintMigrationBase;
use FourPaws\StoreBundle\Entity\ScheduleResult;
use FourPaws\StoreBundle\Exception\ValidationException;
use FourPaws\StoreBundle\Service\DeliveryScheduleService;
use FourPaws\StoreBundle\Exception\BitrixRuntimeException;
use FourPaws\StoreBundle\Exception\InvalidIdentifierException;
use FourPaws\StoreBundle\Exception\ConstraintDefinitionException;
use FourPaws\StoreBundle\Service\ScheduleResultService;

class DeliveryScheduleRegularity20190711185748 extends SprintMigrationBase
{

    protected $description = 'Добавляет поля \'Регулярность\' для расписаний поставок';

    public function up()
    {
        $helper = new HelperManager();

        $hlblockId = $helper->Hlblock()->getHlblockId(HlblockCode::DELIVERY_SCHEDULE_RESULT);
        $field = [
            'ENTITY_ID'         => 'HLBLOCK_' . $hlblockId,
            'FIELD_NAME'        => 'UF_REGULARITY',
            'USER_TYPE_ID'      => 'enumeration',
            'XML_ID'            => '',
            'SORT'              => '100',
            'MULTIPLE'          => 'N',
            'MANDATORY'         => 'N',
            'SHOW_FILTER'       => 'N',
            'SHOW_IN_LIST'      => 'Y',
            'EDIT_IN_LIST'      => 'Y',
            'IS_SEARCHABLE'     => 'N',
            'SETTINGS'          => [
                'DISPLAY'          => 'LIST',
                'LIST_HEIGHT'      => 5,
                'CAPTION_NO_VALUE' => '',
                'SHOW_NO_VALUE'    => 'Y',
            ],
            'EDIT_FORM_LABEL'   => [
                'ru' => 'Регулярность'
            ],
            'LIST_COLUMN_LABEL' => [
                'ru' => 'Регулярность'
            ],
            'LIST_FILTER_LABEL' => [
                'ru' => 'Регулярность'
            ],
            'ERROR_MESSAGE'     => [
                'ru' => ''
            ],
            'HELP_MESSAGE'      => [
                'ru' => ''
            ],
            'ENUMS'             => [
                'n1' => [
                    'XML_ID' => 'Z1',
                    'VALUE'  => 'Регулярное',
                    'SORT'   => '100',
                ],
                'n2' => [
                    'XML_ID' => 'Z2',
                    'VALUE'  => 'Нерегулярное',
                    'SORT'   => '200',
                ],
                'n3' => [
                    'XML_ID' => 'Z3',
                    'VALUE'  => 'ТПЗ',
                    'SORT'   => '300',
                ],
                'n4' => [
                    'XML_ID' => 'Z9',
                    'VALUE'  => 'Исключения',
                    'SORT'   => '500',
                ],
            ],
        ];

        $fieldId = $helper->UserTypeEntity()->addUserTypeEntityIfNotExists(
            $field['ENTITY_ID'],
            $field['FIELD_NAME'],
            $field
        );

        if (isset($field['ENUMS'])) {
            $enum = new CUserFieldEnum();
            if ($enum->SetEnumValues($fieldId, $field['ENUMS'])) {
                $this->log()->info(sprintf('Добавлены значения для поля %s', $field['FIELD_NAME']));
            } else {
                $this->log()->error(sprintf('Не удалось добавить значения для поля %s', $field['FIELD_NAME']));
            }
        }

        $this->setRegular($fieldId, HlblockCode::DELIVERY_SCHEDULE_RESULT);

        // ----
        $hlblockId = $helper->Hlblock()->getHlblockId(HlblockCode::DELIVERY_SCHEDULE);
        $field['ENTITY_ID'] = 'HLBLOCK_' . $hlblockId;

        $fieldId = $helper->UserTypeEntity()->addUserTypeEntityIfNotExists(
            $field['ENTITY_ID'],
            $field['FIELD_NAME'],
            $field
        );

        if (isset($field['ENUMS'])) {
            $enum = new CUserFieldEnum();
            if ($enum->SetEnumValues($fieldId, $field['ENUMS'])) {
                $this->log()->info(sprintf('Добавлены значения для поля %s', $field['FIELD_NAME']));
            } else {
                $this->log()->error(sprintf('Не удалось добавить значения для поля %s', $field['FIELD_NAME']));
            }
        }

        $this->setRegular($fieldId, HlblockCode::DELIVERY_SCHEDULE);

        $prop = [
            'PERSON_TYPE_ID'       => '1',
            'NAME'                 => 'Тип расписания',
            'TYPE'                 => 'STRING',
            'REQUIRED'             => 'N',
            'DEFAULT_VALUE'        => '',
            'SORT'                 => '100',
            'USER_PROPS'           => 'N',
            'IS_LOCATION'          => 'N',
            'PROPS_GROUP_ID'       => '3',
            'DESCRIPTION'          => '',
            'IS_EMAIL'             => 'N',
            'IS_PROFILE_NAME'      => 'N',
            'IS_PAYER'             => 'N',
            'IS_LOCATION4TAX'      => 'N',
            'IS_FILTERED'          => 'N',
            'CODE'                 => 'SCHEDULE_REGULARITY',
            'IS_ZIP'               => 'N',
            'IS_PHONE'             => 'N',
            'IS_ADDRESS'           => 'N',
            'ACTIVE'               => 'Y',
            'UTIL'                 => 'N',
            'INPUT_FIELD_LOCATION' => '0',
            'MULTIPLE'             => 'N',
            'SETTINGS'             => [
                'MINLENGTH' => '',
                'MAXLENGTH' => '',
                'PATTERN'   => '',
                'MULTILINE' => 'N',
                'SIZE'      => '',
            ],
            'ENTITY_REGISTRY_TYPE' => 'ORDER'
        ];

        $addResult = OrderPropsTable::add($prop);
        if (!$addResult->isSuccess()) {
            $this->log()->error('Ошибка при добавлении свойства заказа');

            return false;
        }

        return true;
    }

    /**
     * @param int    $fieldID
     * @param string $entity
     *
     * @return bool
     */
    protected function setRegular(int $fieldID, string $entity): bool
    {
        $enum = new CUserFieldEnum();
        $rsEnum = $enum->GetList([], ['USER_FIELD_ID' => $fieldID]);

        $enumID = null;
        while ($arEnum = $rsEnum->Fetch()) {
            if ($arEnum['XML_ID'] == 'Z1') {
                $enumID = $arEnum["ID"];
                break;
            }
        }

        if (!$enumID) {
            $this->log()->error('не найден айди нового свойства');
            return false;
        }

        if ($entity == HlblockCode::DELIVERY_SCHEDULE_RESULT) {
            /** @var ScheduleResultService $service */
            $service = Application::getInstance()->getContainer()->get(ScheduleResultService::class);
            $schedules = $service->findAllResults();
            /** @var ScheduleResult $schedule */
            foreach ($schedules as $schedule) {
                $schedule->setRegularity($enumID);
                $service->updateResult($schedule);
            }
        } else {
            /** @var DeliveryScheduleService $service */
            $service = Application::getInstance()->getContainer()->get(DeliveryScheduleService::class);
            $schedules = $service->findAll();
            /** @var DeliverySchedule $schedule */
            foreach ($schedules as $schedule) {
                $schedule->setRegular($enumID);
                try {
                    $service->updateResult($schedule);
                } catch (BitrixRuntimeException|ConstraintDefinitionException|InvalidIdentifierException|ValidationException $e) {
                    return false;
                }
            }
        }

        return true;
    }

    public function down()
    {
        $helper = new HelperManager();
        return true;
    }

}
