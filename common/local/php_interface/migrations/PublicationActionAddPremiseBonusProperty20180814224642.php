<?php

namespace Sprint\Migration;


use Adv\Bitrixtools\Migration\SprintMigrationBase;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;

/**
 * Class PublicationActionAddPremiseBonusProperty20180814224642
 * @package Sprint\Migration
 */
class PublicationActionAddPremiseBonusProperty20180814224642 extends SprintMigrationBase {

    protected $description = 'Добавляет свойство ИБ Акции "Начислять бонусы на предпосылки"';

    /**
     *
     *
     * @throws \RuntimeException
     *
     * @return void
     */
    public function up(): void
    {
        $arFields = [
            'NAME' => 'Начислять бонусы на предпосылки',
            'ACTIVE' => 'Y',
            'SORT' => '800',
            'CODE' => 'PREMISE_BONUS',
            'DEFAULT_VALUE' => 0,
            'PROPERTY_TYPE' => 'N',
            'ROW_COUNT' => '1',
            'COL_COUNT' => '30',
            'LIST_TYPE' => 'L',
            'MULTIPLE' => 'N',
            'XML_ID' => '',
            'FILE_TYPE' => '',
            'MULTIPLE_CNT' => '5',
            'TMP_ID' => NULL,
            'LINK_IBLOCK_ID' => '0',
            'WITH_DESCRIPTION' => 'N',
            'SEARCHABLE' => 'N',
            'FILTRABLE' => 'N',
            'IS_REQUIRED' => 'N',
            'VERSION' => '2',
            'USER_TYPE' => 'YesNoPropertyType',
            'USER_TYPE_SETTINGS' => NULL,
            'HINT' => '',
        ];

        $this->getHelper()->Iblock()->addPropertyIfNotExists($this->getIblockId(), $arFields);
    }

    /**
     *
     *
     * @return int
     * @throws \RuntimeException
     */
    protected function getIblockId(): int
    {
        $id = $this->getHelper()->Iblock()->getIblockId(IblockCode::SHARES, IblockType::PUBLICATION);
        if ($id) {
            return $id;
        }
        throw new \RuntimeException('No such iblock');
    }

    /**
     *
     *
     * @return bool|void
     */
    public function down()
    {
        // нет необходимости
    }
}
