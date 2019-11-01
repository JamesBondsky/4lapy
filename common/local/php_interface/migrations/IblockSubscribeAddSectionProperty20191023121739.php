<?php

namespace Sprint\Migration;


use Adv\Bitrixtools\Exception\IblockNotFoundException;
use Adv\Bitrixtools\Migration\SprintMigrationBase;
use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use FourPaws\AppBundle\Entity\BaseEntity;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;

class IblockSubscribeAddSectionProperty20191023121739 extends SprintMigrationBase
{
    protected $description = 'Добавляет свойство "разделы" в инфоблок "Скидка по подписке на доставку"';

    protected $subscribeIblockId;

    protected $productIblockId;

    protected const PROPERTY_CODE = 'SECTION';

    protected $sectionCodes = [
        'dieticheskiy',
        'konservy',
        'sukhoy',
        'sukhoy-korm-sobaki',
        'dieticheskiy-korm-sobaki',
        'lakomstva-vitaminy-dobavki',
        'lakomstva-i-vitaminy-sobaki',
        'lakomstva-gryzuni',
        'lakomstva-i-vitaminy',
        'napolniteli-koshki',
        'napolniteli-gryzuni',
        'napolniteli',
        'pelenki-podguzniki-shtanishki',
    ];

    /**
     * @return bool
     * @throws IblockNotFoundException
     */
    public function up(): bool
    {
        $helper = new HelperManager();

        $prop = [
            'IBLOCK_ID' => $this->getSubscribeIblockId(),
            'NAME' => 'Привязка к разделам',
            'ACTIVE' => BaseEntity::BITRIX_TRUE,
            'SORT' => '500',
            'CODE' => self::PROPERTY_CODE,
            'DEFAULT_VALUE' => '',
            'PROPERTY_TYPE' => 'G',
            'ROW_COUNT' => '1',
            'COL_COUNT' => '30',
            'LIST_TYPE' => 'L',
            'MULTIPLE' => BaseEntity::BITRIX_TRUE,
            'XML_ID' => '',
            'FILE_TYPE' => '',
            'MULTIPLE_CNT' => '5',
            'TMP_ID' => null,
            'LINK_IBLOCK_ID' => $this->getProductIblockId(),
            'WITH_DESCRIPTION' => BaseEntity::BITRIX_FALSE,
            'SEARCHABLE' => BaseEntity::BITRIX_FALSE,
            'FILTRABLE' => BaseEntity::BITRIX_FALSE,
            'IS_REQUIRED' => BaseEntity::BITRIX_FALSE,
            'VERSION' => '1',
            'USER_TYPE' => null,
            'USER_TYPE_SETTINGS' => null,
            'HINT' => '',
        ];

        $helper->Iblock()->addPropertyIfNotExists($this->getSubscribeIblockId(), $prop);

        $rsSection = \CIBlockSection::GetList(false, [
            'IBLOCK_ID' => $this->getProductIblockId(),
            '=CODE' => $this->sectionCodes
        ], false, ['IBLOCK_ID', 'ID', 'CODE']);

        $sectionIds = [];
        while ($arSection = $rsSection->Fetch()) {
            $sectionIds[] = $arSection['ID'];
        }

        if (empty($sectionIds)) {
            return true;
        }

        $rsElement = \CIBlockElement::GetList(false, ['IBLOCK_ID' => $this->getSubscribeIblockId()], false, false, ['ID', 'IBLOCK_ID']);

        while ($arElement = $rsElement->Fetch()) {
            \CIBlockElement::SetPropertyValuesEx($arElement['ID'], $this->getSubscribeIblockId(), [self::PROPERTY_CODE => $sectionIds]);
        }

        return true;
    }

    /**
     * @return bool
     * @throws IblockNotFoundException
     */
    public function down(): bool
    {
        $helper = new HelperManager();

        $helper->Iblock()->deletePropertyIfExists($this->getSubscribeIblockId(), self::PROPERTY_CODE);

        return true;
    }

    /**
     * @return int
     * @throws IblockNotFoundException
     */
    protected function getSubscribeIblockId(): int
    {
        if ($this->subscribeIblockId === null) {
            $this->subscribeIblockId = IblockUtils::getIblockId(IblockType::CATALOG, IblockCode::SUBSCRIBE_PRICES);
        }

        return $this->subscribeIblockId;
    }

    /**
     * @return int
     * @throws IblockNotFoundException
     */
    protected function getProductIblockId(): int
    {
        if ($this->productIblockId === null) {
            $this->productIblockId = IblockUtils::getIblockId(IblockType::CATALOG, IblockCode::PRODUCTS);
        }

        return $this->productIblockId;
    }
}
