<?php
/*
 * @copyright Copyright (c) ADV/web-engineering co.
 */

namespace Sprint\Migration;


use Adv\Bitrixtools\Migration\SprintMigrationBase;
use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use Bitrix\Iblock\ElementTable;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;

class CatalogDefaultBrand20180603150004 extends SprintMigrationBase
{
    protected $description = 'Задание XML_ID бренду без названия';

    protected const ELEMENT_CODE = 'default';

    /**
     * @return bool
     * @throws \Adv\Bitrixtools\Exception\IblockNotFoundException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public function up()
    {
        $brand = ElementTable::query()
            ->setFilter([
                '=NAME'     => '',
                'IBLOCK_ID' => IblockUtils::getIblockId(IblockType::CATALOG, IblockCode::BRANDS),
            ])
            ->setSelect(['*'])
            ->exec()
            ->fetch();

        $e = new \CIBlockElement();
        if ($brand) {
            if (!$e->Update($brand['ID'], [
                'XML_ID' => self::ELEMENT_CODE,
                'CODE'   => self::ELEMENT_CODE,
            ])) {
                $this->log()->error('Ошибка при обновлении элемента');
                return false;
            }
        } elseif (!$e->Add([
            'NAME'      => '',
            'IBLOCK_ID' => IblockUtils::getIblockId(IblockType::CATALOG, IblockCode::BRANDS),
            'ACTIVE'    => 'N',
            'XML_ID'    => self::ELEMENT_CODE,
            'CODE'      => self::ELEMENT_CODE,
        ])) {
            $this->log()->error('Ошибка при создании элемента');
            return false;
        }

        return true;
    }

    public function down()
    {

    }
}