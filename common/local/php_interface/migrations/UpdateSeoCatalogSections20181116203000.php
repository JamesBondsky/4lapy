<?php

namespace Sprint\Migration;


class UpdateSeoCatalogSections20181116203000 extends \Adv\Bitrixtools\Migration\SprintMigrationBase
{

    protected $description = 'Обновление SEO заголовоков разделов каталога';

    public function up()
    {
        $helper = new HelperManager();

        $iblockId = $helper->Iblock()->addIblockIfNotExists([
            'IBLOCK_TYPE_ID' => 'catalog',
            'CODE' => 'products'
        ]);

        $arFilter = [
            'IBLOCK_ID' => $iblockId,
            'ACTIVE' => 'Y',
            'GLOBAL_ACTIVE' => 'Y'
        ];

        $dbList = \CIBlockSection::GetList([], $arFilter);

        while ($arSection = $dbList->GetNext()) {
            $name = $arSection['NAME'];
            $sectionID = $arSection['ID'];
            $section = new \CIBlockSection();
            $section->Update(
                $sectionID,
                [
                    'IPROPERTY_TEMPLATES' => [
                        'SECTION_PAGE_TITLE' => $name
                    ]
                ]
            );
        }
    }

    public function down()
    {
        //empty
    }
}
