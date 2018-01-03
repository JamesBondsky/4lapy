<?php

namespace Sprint\Migration;

use Adv\Bitrixtools\Migration\SprintMigrationBase;
use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;
use CIBlockSection;

class Catalog_category_display_name_field20180103130312 extends SprintMigrationBase
{
    protected $description = 'Добавление поля "Отображаемое имя" разделам каталога';

    protected $names = [
        'koshki'                         => 'Товары для кошек',
        'reptilii'                       => 'Товары для рептилий',
        'veterinarnaya-apteka'           => 'Ветеринарная аптека',
        'zashchita-ot-blokh-i-kleshchey' => 'Защита от блох и клещей',
        'gryzuny-i-khorki'               => 'Товары для грызунов и хорьков',
        'sobaki'                         => 'Товары для собак',
        'ptitsy'                         => 'Товары для птиц',
        'ryby'                           => 'Товары для рыб',
    ];

    const FIELD_NAME = 'UF_DISPLAY_NAME';

    public function up()
    {
        /** @var \Sprint\Migration\Helpers\UserTypeEntityHelper $userTypeEntityHelper */
        $userTypeEntityHelper = $this->getHelper()->UserTypeEntity();
        $iblockId = IblockUtils::getIblockId(IblockType::CATALOG, IblockCode::PRODUCTS);
        $entityId = 'IBLOCK_' . $iblockId . '_SECTION';

        if ($userTypeEntityHelper->addUserTypeEntityIfNotExists(
            $entityId,
            static::FIELD_NAME,
            [
                'XML_ID'            => static::FIELD_NAME,
                'USER_TYPE_ID'      => 'string',
                'SORT'              => 200,
                'MULTIPLE'          => 'N',
                'MANDATORY'         => 'N',
                'SHOW_FILTER'       => 'N',
                'SHOW_IN_LIST'      => 'Y',
                'EDIT_IN_LIST'      => 'Y',
                'IS_SEARCHABLE'     => 'N',
                'EDIT_FORM_LABEL'   => [
                    'ru' => 'Отображаемое имя',
                    'en' => 'Display name',
                ],
                'LIST_COLUMN_LABEL' => [
                    'ru' => 'Отображаемое имя',
                    'en' => 'Display name',
                ],
                'LIST_FILTER_LABEL' => [
                    'ru' => 'Отображаемое имя',
                    'en' => 'Display name',
                ],
            ]
        )) {
            $this->log()->info('Пользовательское свойство ' . static::FIELD_NAME . ' добавлено');
        } else {
            $this->log()->error('Ошибка при добавлении пользовательского свойства ' . static::FIELD_NAME);

            return false;
        }

        $sectionCodeToId = [];
        $sections = CIBlockSection::GetList([], ['CODE' => array_keys($this->names)]);
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

    public function down()
    {
        /** @var \Sprint\Migration\Helpers\UserTypeEntityHelper $userTypeEntityHelper */
        $userTypeEntityHelper = $this->getHelper()->UserTypeEntity();
        $iblockId = IblockUtils::getIblockId(IblockType::CATALOG, IblockCode::PRODUCTS);
        $entityId = 'IBLOCK_' . $iblockId . '_SECTION';

        if ($userTypeEntityHelper->deleteUserTypeEntityIfExists($entityId, static::FIELD_NAME)) {
            $this->log()->info('Пользовательское свойство ' . static::FIELD_NAME . ' удалено');
        } else {
            $this->log()->warning('Ошибка при удалении пользовательского свойства ' . static::FIELD_NAME);
        }
    }
}
