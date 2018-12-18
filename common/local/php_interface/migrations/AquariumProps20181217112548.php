<?php

namespace Sprint\Migration;


use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;

class AquariumProps20181217112548 extends \Adv\Bitrixtools\Migration\SprintMigrationBase
{

    protected $description = "Новые свойства для комплектов аквариумов";

    private $arProps = [
        'AQUARIUM_COMBINATION' => [
            'IBLOCK_ID' => 0,
            'NAME' => 'Уникальный идентификатор связи аквариум-тумба',
            'ACTIVE' => 'Y',
            'SORT' => '1300',
            'CODE' => 'AQUARIUM_COMBINATION',
            'PROPERTY_TYPE' => 'S',
            'IS_REQUIRED' => 'N',
            'MULTIPLE' => 'N'
        ],
        'POWER_MIN' => [
            'IBLOCK_ID' => 0,
            'NAME' => 'Минимальная мощность фильтра',
            'ACTIVE' => 'Y',
            'SORT' => '1400',
            'CODE' => 'POWER_MIN',
            'PROPERTY_TYPE' => 'N',
            'IS_REQUIRED' => 'N',
            'MULTIPLE' => 'N'
        ],
        'POWER_MAX' => [
            'IBLOCK_ID' => 0,
            'NAME' => 'Максимальная мощность фильтра',
            'ACTIVE' => 'Y',
            'SORT' => '1500',
            'CODE' => 'POWER_MAX',
            'PROPERTY_TYPE' => 'N',
            'IS_REQUIRED' => 'N',
            'MULTIPLE' => 'N'
        ]
    ];

    /**
     *
     *
     * @return int
     * @throws \RuntimeException
     */
    protected function getIblockId(): int
    {
        $id = $this->getHelper()->Iblock()->getIblockId(IblockCode::PRODUCTS, IblockType::CATALOG);
        if ($id) {
            return $id;
        }
        throw new \RuntimeException('No such iblock');
    }

    public function up()
    {
        $helper = new HelperManager();

        foreach ($this->arProps as $code => &$prop) {
            $prop['IBLOCK_ID'] = $this->getIblockId();
            $helper->Iblock()->addPropertyIfNotExists($prop['IBLOCK_ID'], $prop);
        }
        return true;
    }

    public function down()
    {
        $helper = new HelperManager();

        foreach ($this->arProps as $code => $prop) {
            $helper->Iblock()->deletePropertyIfExists($this->getIblockId(), $code);
        }
        return true;
    }

}
