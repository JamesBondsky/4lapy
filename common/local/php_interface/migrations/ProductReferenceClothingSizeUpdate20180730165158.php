<?php
/*
 * @copyright Copyright (c) ADV/web-engineering co.
 */

namespace Sprint\Migration;


use Adv\Bitrixtools\Migration\SprintMigrationBase;
use Bitrix\Main\Entity\DataManager;
use FourPaws\App\Application;

class ProductReferenceClothingSizeUpdate20180730165158 extends SprintMigrationBase
{
    protected $description = 'Задание значений сортировки в справочнике размеров одежды';

    protected const CLOTHING_SORT = [
        'xxs'          => 100,
        'xs'           => 200,
        's'            => 300,
        'm'            => 400,
        'l'            => 500,
        'xl'           => 600,
        '2xl'          => 700,
        '3xl'          => 800,
        '4xl'          => 900,
        '5xl'          => 1000,
        '6xl'          => 1100,
        '7xl'          => 1200,
        '8xl'          => 1300,
        'bezrazmernyy' => 9999,
    ];


    /**
     * @return bool
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     */
    public function up()
    {
        /** @var DataManager $dataManager */
        $dataManager = Application::getInstance()->getContainer()->get('bx.hlblock.clothingsize');

        $items = $dataManager::query()->setSelect(['*'])->exec();

        while ($item = $items->fetch()) {
            $sort = static::CLOTHING_SORT[$item['UF_CODE']];
            if (null === $sort) {
                $this->log()->warning('Не найдено значение сортировки для размера ' . $item['UF_CODE']);
                continue;
            }


            $result = $dataManager::update($item['ID'], ['UF_SORT' => $sort]);
            if (!$result->isSuccess()) {
                $this->log()->error(
                    sprintf('Ошибка при обновлении размера %s: %s', $item['UF_CODE'], implode(', ', $result->getErrorMessages()))
                );
                return false;
            }
        }

        return true;
    }

    public function down()
    {

    }
}