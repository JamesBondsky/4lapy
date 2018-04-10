<?php

namespace Sprint\Migration;

use Adv\Bitrixtools\Migration\SprintMigrationBase;
use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;
use CIBlockSection;

class CatalogCategoryDisplayNames20180410114603 extends SprintMigrationBase
{
    protected $description = 'Здадание значений поля "Отображаемое имя" разделам каталога "Завели котенка" и "Завели щенка"';

    protected $names = [
        'zaveli-kotenka' => 'Завели котенка',
        'zaveli-shchenka' => 'Завели щенка',
    ];

    const FIELD_NAME = 'UF_DISPLAY_NAME';

    public function up()
    {
        $iblockId = IblockUtils::getIblockId(IblockType::CATALOG, IblockCode::PRODUCTS);

        $sectionCodeToId = [];
        $sections = CIBlockSection::GetList([], ['IBLOCK_ID' => $iblockId, 'CODE' => array_keys($this->names)]);
        while ($section = $sections->fetch()) {
            $sectionCodeToId[$section['CODE']] = $section['ID'];
        }

        foreach ($this->names as $code => $name) {
            if (!isset($sectionCodeToId[$code])) {
                $this->log()->warning('Не найден раздел ' . $code);
                continue;
            }

            $section = new CIBlockSection();
            if ($section->Update($sectionCodeToId[$code], [static::FIELD_NAME => $name])) {
                $this->log()->info('Задано отображаемое имя для раздела ' . $code);
            } else {
                $this->log()->warning('Не удалось задать отображаемое имя для раздела ' . $code);
            }
        }

        return true;
    }
}
