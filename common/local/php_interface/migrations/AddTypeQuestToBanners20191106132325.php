<?php

namespace Sprint\Migration;


use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use Bitrix\Iblock\PropertyEnumerationTable;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;

class AddTypeQuestToBanners20191106132325 extends \Adv\Bitrixtools\Migration\SprintMigrationBase
{
    protected $description = 'Добавляет баннерам тип "Квест"';

    protected const PROPERTY_CODE = 'TYPE';

    protected const PROPERTY_ENUM = [
        'VALUE' => 'Квест',
        'DEF' => 'N',
        'SORT' => '500',
        'XML_ID' => 'quest',
        'TMP_ID' => NULL,
    ];

    /**
     * @return bool
     */
    public function up(): bool
    {
        $helper = new HelperManager();

        try {
            $iblockId = IblockUtils::getIblockId(IblockType::PUBLICATION, IblockCode::BANNERS);
        } catch (\Exception $e) {
            return false;
        }

        $propertyId = $helper->Iblock()->getPropertyId($iblockId, self::PROPERTY_CODE);

        if (!$propertyId) {
            return false;
        }

        try {
            PropertyEnumerationTable::add(array_merge(['PROPERTY_ID' => $propertyId], self::PROPERTY_ENUM));
        } catch (\Exception $e) {
            $this->deleteProperty();
            return false;
        }

        return true;
    }

    /**
     * @return bool
     */
    public function down(): bool
    {
        return $this->deleteProperty();
    }

    /**
     * @return bool
     */
    protected function deleteProperty(): bool
    {
        return true;
    }
}
