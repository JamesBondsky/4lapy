<?php

namespace Sprint\Migration;

use Adv\Bitrixtools\Migration\SprintMigrationBase;
use Bitrix\Main\Type\Date;
use FourPaws\App\Application;
use FourPaws\StoreBundle\Entity\ScheduleResult;
use FourPaws\StoreBundle\Service\ScheduleResultService;

class TpzScheduleResultDateActive20190411124525 extends SprintMigrationBase
{
    protected $description = "Добавление нового поля для расписаний ТПЗ - дата, на которую действует расписание";
    protected $propertyCode = 'UF_DATE_ACTIVE';
    protected $name = 'Дата, на которую действует расписание';

    public function up()
    {
        $helper = new HelperManager();

        $hlblockId = $helper->Hlblock()->getHlblockId('DeliveryScheduleResult');
        $entityId = 'HLBLOCK_' . $hlblockId;

        $helper->UserTypeEntity()->addUserTypeEntityIfNotExists(
            $entityId,
            $this->propertyCode,
            [
                'FIELD_NAME' => $this->propertyCode,
                'USER_TYPE_ID' => 'date',
                'XML_ID' => $this->propertyCode,
                'SORT' => '1500',
                'MULTIPLE' => 'N',
                'MANDATORY' => 'Y',
                'SHOW_FILTER' => 'I',
                'SHOW_IN_LIST' => 'Y',
                'EDIT_IN_LIST' => 'Y',
                'IS_SEARCHABLE' => 'Y',
                'EDIT_FORM_LABEL' => [
                    'ru' => $this->name,
                ],
                'LIST_COLUMN_LABEL' => [
                    'ru' => $this->name,
                ],
                'LIST_FILTER_LABEL' => [
                    'ru' => $this->name,
                ],
                'ERROR_MESSAGE' => [
                    'ru' => $this->name,
                ],
                'HELP_MESSAGE' => [
                    'ru' => $this->name,
                ],
            ]
        );

        $scheduleResultService = Application::getInstance()->getContainer()->get(ScheduleResultService::class);
        $scheduleResults = $scheduleResultService->findAllResults();
        /** @var ScheduleResult $scheduleResult */
        foreach ($scheduleResults as $scheduleResult) {
            $scheduleResult->setDateActive((new Date())->format('d.m.Y'));
            $scheduleResultService->updateResult($scheduleResult);
        }

        return true;

    }

    public function down()
    {
        //empty
    }
}
