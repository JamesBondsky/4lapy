<?php
/*
 * @copyright Copyright (c) ADV/web-engineering co.
 */

namespace Sprint\Migration;


use Adv\Bitrixtools\Exception\IblockNotFoundException;
use Adv\Bitrixtools\Migration\SprintMigrationBase;
use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;

class IblockMainMenuAddAnchorProperty20180724140001 extends SprintMigrationBase
{

    protected const FIELD_CODE = 'UF_SECTION_ANCHOR';
    /**
     * @return bool
     * @throws Exceptions\HelperException
     * @throws IblockNotFoundException
     */
    public function up()
    {
        $entityId = $this->getEntityId();
        $ufHelper = $this->getHelper()->UserTypeEntity();
        if (!$ufHelper->addUserTypeEntityIfNotExists(
            $entityId,
            self::FIELD_CODE,
            [
                'FIELD_NAME'        => self::FIELD_CODE,
                'USER_TYPE_ID'      => 'iblock_section',
                'XML_ID'            => self::FIELD_CODE,
                'SORT'              => '210',
                'MULTIPLE'          => 'N',
                'MANDATORY'         => 'N',
                'SHOW_FILTER'       => 'E',
                'SHOW_IN_LIST'      => 'Y',
                'EDIT_IN_LIST'      => 'Y',
                'IS_SEARCHABLE'     => 'N',
                'SETTINGS'          => [
                    'DISPLAY'       => 'LIST',
                    'LIST_HEIGHT'   => 20,
                    'IBLOCK_ID'     => IblockUtils::getIblockId(IblockType::CATALOG, IblockCode::PRODUCTS),
                    'DEFAULT_VALUE' => '',
                    'ACTIVE_FILTER' => 'Y',
                ],
                'EDIT_FORM_LABEL'   => [
                    'ru' => 'Якорь на раздел',
                ],
                'LIST_COLUMN_LABEL' => [
                    'ru' => 'Якорь на раздел',
                ],
                'LIST_FILTER_LABEL' => [
                    'ru' => 'Якорь на раздел',
                ],
                'ERROR_MESSAGE'     => [
                    'ru' => '',
                ],
                'HELP_MESSAGE'      => [
                    'ru' => 'Добавляется к ссылке на раздел',
                ],
            ]
        )) {
            $this->log()->error('Ошибка при добавлении поля ' . self::FIELD_CODE);
            return false;
        }

        return true;
    }

    /**
     * @return bool
     * @throws Exceptions\HelperException
     * @throws IblockNotFoundException
     */
    public function down()
    {
        $entityId = $this->getEntityId();
        $ufHelper = $this->getHelper()->UserTypeEntity();

        if (!$ufHelper->getUserTypeEntity($entityId, self::FIELD_CODE)) {
            $this->log()->warning('Поле ' . self::FIELD_CODE . ' не существует');
        } elseif (!$ufHelper->deleteUserTypeEntityIfExists($entityId, self::FIELD_CODE)) {
            $this->log()->error('Ошибка при удалении поля ' . self::FIELD_CODE);
            return false;
        }

        return true;
    }

    /**
     * @return string
     * @throws IblockNotFoundException
     */
    protected function getEntityId()
    {
        $iblockId = IblockUtils::getIblockId(IblockType::MENU, IblockCode::MAIN_MENU);

        return 'IBLOCK_' . $iblockId . '_SECTION';
    }
}