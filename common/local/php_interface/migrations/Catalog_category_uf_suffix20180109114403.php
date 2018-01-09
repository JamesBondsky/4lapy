<?php

namespace Sprint\Migration;

use Adv\Bitrixtools\Migration\SprintMigrationBase;
use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use FourPaws\Enum\IblockType;
use FourPaws\Enum\IblockCode;
use CIBlockSection;

class Catalog_category_uf_suffix20180109114403 extends SprintMigrationBase
{
    protected $description = 'Добавление пользовательского свойства UF_SUFFIX для разделов 1-го уровня каталога';

    protected $suffixes = [
        'koshki'                         => 'для кошек',
        'reptilii'                       => 'для рептилий',
        'gryzuny-i-khorki'               => 'для грызунов и хорьков',
        'sobaki'                         => 'для собак',
        'ptitsy'                         => 'для птиц',
        'ryby'                           => 'для рыб',
    ];

    const FIELD_NAME = 'UF_SUFFIX';


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
                    'ru' => 'Суффикс для подразделов',
                    'en' => 'Display name',
                ],
                'LIST_COLUMN_LABEL' => [
                    'ru' => 'Суффикс для подразделов',
                    'en' => 'Display name',
                ],
                'LIST_FILTER_LABEL' => [
                    'ru' => 'Суффикс для подразделов',
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
        $sections = CIBlockSection::GetList([], ['CODE' => array_keys($this->suffixes)]);
        while ($section = $sections->fetch()) {
            $sectionCodeToId[$section['CODE']] = $section['ID'];
        }

        foreach ($this->suffixes as $code => $name) {
            if (!isset($sectionCodeToId[$code])) {
                $this->log()->warning('Не найден раздел ' . $code);
                continue;
            }

            $section = new CIBlockSection();
            if ($section->Update($sectionCodeToId[$code], [static::FIELD_NAME => $name])) {
                $this->log()->info('Задан суффикс для раздела ' . $code);
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
