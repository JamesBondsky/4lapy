<?php

namespace Sprint\Migration;


use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;

class LandingsFestival20190423171315 extends \Adv\Bitrixtools\Migration\SprintMigrationBase
{

    protected $description = 'Добавляет в инфоблок \"Заявки\" для лендингов раздел для регистраций на фестиваль';

    protected $requestSections = [
        'festival_requests' => [
            'NAME' => 'Регистрация на фестиваль',
            'CODE' => 'festival_requests'
        ],
    ];

    public function up()
    {
        $res = true;
        try {
            $helper = new HelperManager();

            $requestIblockId = $helper->Iblock()->getIblockId(IblockCode::GRANDIN_REQUEST, IblockType::GRANDIN);

            $iblockSectionIds = [];
            foreach ($this->requestSections as $requestSection) {
                $helper->Iblock()->addSectionIfNotExists($requestIblockId, $requestSection);
            }
        } catch (\Exception $e) {
            $res = false;
            dump($e->getCode(), $e->getMessage());
        }

        return $res;
    }

    public function down()
    {

    }

}
