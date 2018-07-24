<?php

namespace Sprint\Migration;


use Adv\Bitrixtools\Migration\SprintMigrationBase;
use Bitrix\Main\Application;
use Bitrix\Main\DB\SqlQueryException;

/** @noinspection AutoloadingIssuesInspection
 *
 * Class AddCmlLinkIndex20180724124141
 *
 * @package Sprint\Migration
 */
class AddCmlLinkIndex20180724124141 extends SprintMigrationBase
{
    protected /** @noinspection ClassOverridesFieldOfSuperClassInspection */
        $description = 'Добавление индексов в таблицу местоположений DPD';

    /** @noinspection PhpMissingParentCallCommonInspection
     *
     * @throws SqlQueryException
     */
    public function up()
    {
        Application::getConnection()
            ->query('
                ALTER TABLE `b_iblock_element_prop_s3`
                    ADD INDEX `IX_CML2_LINK` (`PROPERTY_3`)
            ');
    }

    /** @noinspection PhpMissingParentCallCommonInspection
     *
     * do nothing
     */
    public function down()
    {

    }
}
