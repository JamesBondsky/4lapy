<?php

namespace Sprint\Migration;

use Adv\Bitrixtools\Migration\SprintMigrationBase;
use Adv\Bitrixtools\Tools\HLBlock\HLBlockFactory;
use Bitrix\Main\Entity\DataManager;

class AddDefaultValsByPetTypeHl20180118135206 extends SprintMigrationBase
{
    
    const HL_NAME    = 'ForWho';
    
    protected $description = 'установка дефолтных значений в highload тип питомца';
    
    public function up()
    {
        try {
            $dataManager = HLBlockFactory::createTableObject(static::HL_NAME);
            $res         =
                $dataManager::query()->setSelect(
                    [
                        'ID',
                        'UF_XML_ID',
                    ]
                )->setFilter(
                    [
                        'UF_XML_ID' => [
                            '3',
                            '11',
                            '7',
                            '2',
                            '10',
                            '00000120',
                        ],
                    ]
                )->exec();
            while ($item = $res->fetch()) {
                $code = '';
                switch ($item['UF_XML_ID']) {
                    case '3':
                        $code = 'koshki';
                        break;
                    case '11':
                        $code = 'sobaki';
                        break;
                    case '7':
                        $code = 'ptitsy';
                        break;
                    case '2':
                        $code = 'gryzuny';
                        break;
                    case '10':
                        $code = 'ryby';
                        break;
                    case '00000120':
                        $code = 'prochee';
                        break;
                }
                $dataManager::update(
                    $item['ID'],
                    [
                        'UF_USE_BY_PET' => 'Y',
                        'UF_CODE'       => $code,
                    ]
                );
            }
        } catch (\Exception $e) {
        }
    }
    
    public function down()
    {
    
    }
    
}
