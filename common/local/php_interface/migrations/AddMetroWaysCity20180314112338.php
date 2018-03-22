<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace Sprint\Migration;

use Adv\Bitrixtools\Migration\SprintMigrationBase;
use Adv\Bitrixtools\Tools\HLBlock\HLBlockFactory;
use Bitrix\Main\Application;
use Sprint\Migration\Exceptions\MigrationException;

class AddMetroWaysCity20180314112338 extends SprintMigrationBase
{
    private const HL_CODE = 'MetroWays';

    private const LOCATION_CODE = AddMetroWaysCity20180314112338::MOSCOW_LOCATION_CODE;
    private const MOSCOW_LOCATION_CODE = '0000073738';

    protected $description = 'Add UF CITY to MetroWays';

    /**
     * @throws \Bitrix\Main\Db\SqlQueryException
     * @return bool|void
     */
    public function up()
    {
        $hlBlockId = $this->getHelper()->Hlblock()->getHlblockId(static::HL_CODE);

        $connection = Application::getConnection();
        $connection->startTransaction();
        try {
            $this->addField('HLBLOCK_' . $hlBlockId);
            $this->setMoscow();
            $connection->commitTransaction();
        } catch (\Exception $exception) {
            $connection->rollbackTransaction();
        }
    }

    /**
     * @return bool
     */
    public function down(): bool
    {
        $helper = new HelperManager();
        $hlblockId = $helper->Hlblock()->getHlblockId(static::HL_CODE);
        return $helper->UserTypeEntity()->deleteUserTypeEntityIfExists('HLBLOCK_' . $hlblockId, 'UF_CITY_LOCATION');
    }

    /**
     * @param $entityId
     *
     * @return int
     */
    protected function addField($entityId): int
    {
        return $this
            ->getHelper()
            ->UserTypeEntity()
            ->addUserTypeEntityIfNotExists(
                $entityId,
                'UF_CITY_LOCATION',
                [
                    'FIELD_NAME'        => 'UF_CITY_LOCATION',
                    'USER_TYPE_ID'      => 'sale_location',
                    'XML_ID'            => '',
                    'SORT'              => '200',
                    'MULTIPLE'          => 'N',
                    'MANDATORY'         => 'Y',
                    'SHOW_FILTER'       => 'N',
                    'SHOW_IN_LIST'      => 'Y',
                    'EDIT_IN_LIST'      => 'Y',
                    'IS_SEARCHABLE'     => 'N',
                    'SETTINGS'          => [
                        'DEFAULT_VALUE' => '',
                    ],
                    'EDIT_FORM_LABEL'   => [
                        'ru' => 'Город(Местоположение)',
                    ],
                    'LIST_COLUMN_LABEL' => [
                        'ru' => 'Город(Местоположение)',
                    ],
                    'LIST_FILTER_LABEL' => [
                        'ru' => 'Город(Местоположение)',
                    ],
                    'ERROR_MESSAGE'     => [
                        'ru' => '',
                    ],
                    'HELP_MESSAGE'      => [
                        'ru' => 'Город(Местоположение)',
                    ],
                ]
            );
    }

    /**
     * @throws \Exception
     */
    protected function setMoscow(): void
    {
        $dataManager = HLBlockFactory::createTableObject(static::HL_CODE);
        $data = $dataManager::query()
            ->addSelect('ID')
            ->exec()
            ->fetchAll();

        $ids = array_map(function ($itemData) {
            return $itemData['ID'] ?? 0;
        }, $data);
        $ids = array_filter($ids);

        foreach ($ids as $id) {
            $result = $dataManager::update($id, [
                'UF_CITY_LOCATION' => static::LOCATION_CODE,
            ]);

            if (!$result->isSuccess()) {
                throw new MigrationException(
                    sprintf(
                        'Cant update %s: %s',
                        $id,
                        implode(', ', $result->getErrorMessages())
                    )
                );
            }
        }
    }
}
