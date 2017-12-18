<?php

namespace Sprint\Migration;

use Adv\Bitrixtools\Migration\SprintMigrationBase;
use FourPaws\App\Application;
use FourPaws\Location\LocationService;

class HLBlock_cities_set_default20171218180853 extends SprintMigrationBase
{
    protected $description = 'Задание дефолтного города в hl-блоке городов';

    const HL_BLOCK_NAME = 'Cities';

    public function up()
    {
        $cityTable = Application::getInstance()->getContainer()->get('bx.hlblock.cities');

        if ($cityTable::getList(
            [
                'filter' => ['UF_DEFAULT' => true],
            ]
        )->fetch()) {
            $this->log()->warning('Дефолтный город уже задан');

            return true;
        }

        $moscow = $cityTable::getList(
            [
                'filter' => ['UF_LOCATION' => LocationService::LOCATION_CODE_MOSCOW],
            ]
        )->fetch();

        if (!$moscow) {
            $this->log()->error('Город "Москва" не найден');

            return false;
        }

        $updateResult = $cityTable::update($moscow['ID'], ['UF_DEFAULT' => true]);
        if (!$updateResult->isSuccess()) {
            $this->log()->error('Не удалось задать дефолтный город');

            return false;
        }

        $this->log()->info('Задан дефолтный город');

        return true;
    }

    public function down()
    {
    }
}
