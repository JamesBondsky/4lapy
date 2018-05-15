<?php

namespace Sprint\Migration;


use Adv\Bitrixtools\Migration\SprintMigrationBase;
use Bitrix\Iblock\PropertyIndex\Manager;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Entity\Query;
use Bitrix\Main\SystemException;
use Bitrix\Sale\Location\LocationTable;
use FourPaws\Enum\IblockCode;

/**
 * Class DeleteStreetFromLocation20180515143738
 *
 * @package Sprint\Migration
 */
class DeleteStreetFromLocation20180515143738 extends SprintMigrationBase
{
    protected /** @noinspection ClassOverridesFieldOfSuperClassInspection */
        $description = 'Удаление улиц из местоположений и удаление фасетного индекса';

    /** @noinspection PhpMissingParentCallCommonInspection
     *
     * @throws SystemException
     * @throws ArgumentException
     */
    public function up(): void
    {
        $this->log()->info('Запуск миграции');
        $this->deleteStreets();
        $this->deleteIndex();
        $this->log()->info('Готово');
    }

    /** @noinspection PhpMissingParentCallCommonInspection */
    public function down()
    {
        /**
         * Улиц быть не должно.
         */
    }

    /**
     * @throws SystemException
     * @throws ArgumentException
     */
    public function deleteStreets(): void
    {
        $this->log()->info('Удаление улиц');

        $locationList = (new Query(LocationTable::getEntity()))
            ->setFilter(['TYPE.CODE' => 'STREET'])
            ->setSelect(['ID'])
            ->exec()
            ->fetchAll();

        foreach ($locationList as $location) {
            if (!($result = LocationTable::deleteExtended($location['ID'], [
                'DELETE_SUBTREE' => false,
                'RESET_LEGACY' => false,
                'REBALANCE' => false,
            ]))->isSuccess()) {
                $this->log()->error(
                    \sprintf(
                        'Location remove error: %s',
                        \implode(', ', $result->getErrorMessages())
                    )
                );
            }
        }

        $this->log()->info('Удаление улиц завершено');
        $this->log()->info('Ребалансировка дерева');

        LocationTable::resetLegacyPath();
        LocationTable::resort();

        $this->log()->info('Ребалансировка дерева завершена');
    }

    /**
     *
     */
    public function deleteIndex(): void
    {
        $this->log()->info('Удаление фасетного индекса');

        $iblockId = $this->getHelper()->Iblock()->getIblockId(IblockCode::PRODUCTS);
        Manager::deleteIndex($iblockId);
        Manager::markAsInvalid($iblockId);

        $this->log()->info('Удаление фасетного индекса завершено');
    }
}
