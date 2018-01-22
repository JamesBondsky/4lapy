<?php

namespace Sprint\Migration;

use Adv\Bitrixtools\Migration\SprintMigrationBase;
use Bitrix\Highloadblock\DataManager;
use FourPaws\App\Application;
use FourPaws\Catalog\Model\Filter\BrandFilter;

class BrandFilterClass20171222173428 extends SprintMigrationBase
{
    protected $description = 'Сменить класс фильтра по бренду';

    public function up()
    {
        /**
         * @var DataManager $dataManager
         */
        $dataManager = Application::getInstance()->getContainer()->get('bx.hlblock.filter');
        $brandFilterFields = $dataManager::query()
            ->addSelect('ID')
            ->addFilter('UF_CODE', 'BRAND')
            ->exec()
            ->fetch();

        if (!$brandFilterFields) {
            throw new \RuntimeException('No such filter with code BRAND');
        }


        /**
         * @var array $brandFilterFields
         */

        $brandFilterFields['UF_CLASS_NAME'] = BrandFilter::class;
        /** @noinspection OffsetOperationsInspection */
        return $dataManager::update($brandFilterFields['ID'], $brandFilterFields)->isSuccess();
    }
}
