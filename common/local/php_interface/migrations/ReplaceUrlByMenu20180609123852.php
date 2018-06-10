<?php

namespace Sprint\Migration;


use Adv\Bitrixtools\Migration\SprintMigrationBase;
use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use Bitrix\Iblock\ElementTable;
use Bitrix\Iblock\PropertyTable;
use Bitrix\Iblock\SectionTable;
use Bitrix\Main\Entity\Query\Join;
use Bitrix\Main\Entity\ReferenceField;
use FourPaws\BitrixOrm\Utils\EntityConstructor;
use FourPaws\BitrixOrm\Utils\IblockPropEntityConstructor;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;

class ReplaceUrlByMenu20180609123852 extends SprintMigrationBase
{

    protected $description = 'изменение пунктов меню';

    public function up()
    {
        $helper = new HelperManager();

        $iblockId = IblockUtils::getIblockId(IblockType::MENU, IblockCode::MAIN_MENU);
        $partialLinks = ['services/', 'company/', 'customer/'];
        $propertyHrefId = (int)PropertyTable::query()->where('CODE', 'HREF')->setSelect(['ID'])->exec()->fetch()['ID'];
        foreach ($partialLinks as $partialLink) {
            $sections = SectionTable::query()
                ->registerRuntimeField(new ReferenceField(
                    'USER_FIELDS',
                EntityConstructor::compileEntityDataClass('UtsIblock' . $iblockId . 'Section',
                    'b_uts_iblock_' . $iblockId . '_section'),
                    Join::on('this.ID', 'ref.VALUE_ID')))
                ->where('IBLOCK_ID', $iblockId)
                ->whereLike('USER_FIELDS.UF_HREF', '%' . $partialLink . '%')
                ->setSelect(['ID', 'HREF' => 'USER_FIELDS.UF_HREF'])
                ->exec()->fetchAll();
            foreach ($sections as $section) {
                $newLink = str_replace($partialLink, '', $section['HREF']);
                $helper->Iblock()->updateSection((int)$section['ID'], ['UF_HREF' => $newLink]);
            }

            $elements = ElementTable::query()
                ->registerRuntimeField(new ReferenceField(
                    'PROPERTIES',
                    IblockPropEntityConstructor::getDataClass($iblockId),
                    Join::on('this.ID', 'ref.IBLOCK_ELEMENT_ID')))
                ->where('IBLOCK_ID', $iblockId)
                ->whereLike('PROPERTIES.PROPERTY_' . $propertyHrefId, '%' . $partialLink . '%')
                ->setSelect(['ID', 'HREF' => 'PROPERTIES.PROPERTY_' . $propertyHrefId])
                ->exec()->fetchAll();
            foreach ($elements as $element) {
                $newLink = str_replace($partialLink, '', $element['HREF']);
                $helper->Iblock()->updateElement((int)$element['ID'], [], ['HREF' => $newLink]);
            }
        }
    }
}