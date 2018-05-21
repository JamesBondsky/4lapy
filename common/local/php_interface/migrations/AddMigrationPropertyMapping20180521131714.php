<?php

namespace Sprint\Migration;


use Adv\Bitrixtools\Migration\SprintMigrationBase;
use FourPaws\Migrator\Client\OrderProperty;
use FourPaws\Migrator\Entity\OrderProperty as OrderPropertyEntity;

/**
 * Class AddMigrationPropertyMapping20180521131714
 *
 * Добавление свойство в маппинг миграции
 *
 * @package Sprint\Migration
 */
class AddMigrationPropertyMapping20180521131714 extends SprintMigrationBase
{
    protected $description = 'Добавление свойство в маппинг миграции';

    /** @noinspection PhpMissingParentCallCommonInspection
     *
     * @return bool
     */
    public function up(): bool
    {
        try {
            $entity = new OrderPropertyEntity(OrderProperty::ENTITY_NAME);
            $entity::$isSkipCheck = true;
            $entity->setDefaults();

            return true;
        } catch (\Exception $e) {
            $this->log()->error(\sprintf(
                'Error: %s',
                $e->getMessage()

            ));

            return false;
        }
    }
}
