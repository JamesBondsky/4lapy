<?php

namespace Sprint\Migration;

use Adv\Bitrixtools\Migration\SprintMigrationBase;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;


/**
 * Class PublicationActionJsonGroupSetProperty20180427150649
 * @package Sprint\Migration
 */
class PublicationActionJsonGroupSetProperty20180427150649 extends SprintMigrationBase {

    protected $description = 'Создание свойства JSON_GROUP_SET ИБ "Акции" для сниппета добавления в корзину набора товаров';

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
            'IBLOCK_ID' => $this->getIblockId(),
            'NAME' => 'Группы акции "Скидка за набор" JSON',
            'ACTIVE' => 'Y',
            'SORT' => '700',
            'CODE' => 'JSON_GROUP_SET',
            'DEFAULT_VALUE' => '',
            'PROPERTY_TYPE' => 'S',
            'ROW_COUNT' => '1',
            'COL_COUNT' => '30',
            'LIST_TYPE' => 'L',
            'MULTIPLE' => 'N',
            'XML_ID' => '',
            'FILE_TYPE' => '',
            'MULTIPLE_CNT' => '5',
            'TMP_ID' => null,
            'LINK_IBLOCK_ID' => '0',
            'WITH_DESCRIPTION' => 'N',
            'SEARCHABLE' => 'N',
            'FILTRABLE' => 'N',
            'IS_REQUIRED' => 'N',
            'VERSION' => '2',
            'USER_TYPE' => null,
            'USER_TYPE_SETTINGS' => null,
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
    public function down(){
        // CD
    }
}
