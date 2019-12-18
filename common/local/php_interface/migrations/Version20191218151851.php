<?php

namespace Sprint\Migration;

use Adv\Bitrixtools\Migration\SprintMigrationBase;
use Adv\Bitrixtools\Tools\HLBlock\HLBlockFactory;
use Exception;

class Version20191218151851 extends SprintMigrationBase
{
    protected $description = 'Обновляет промокоды для квеста';

    protected const PRIZE_HL_TYPE = 'QuestPrize';
    protected const PROMOCODE_HL_TYPE = 'QuestPromocode';

    protected $catPrizeData = [
        ['UF_PRODUCT_XML_ID' => '1018143'],
        ['UF_PRODUCT_XML_ID' => '1006981'],
    ];

    protected $dogPrizeData = [
        ['UF_PRODUCT_XML_ID' => '1009108'],
        ['UF_PRODUCT_XML_ID' => '1009939'],
    ];

    /**
     * @return bool|void
     * @throws Exception
     */
    public function up()
    {
        $this->fillPrizes();
    }

    /**
     * @throws Exception
     */
    protected function fillPrizes(): void
    {
        $dataManager = HLBlockFactory::createTableObject(self::PRIZE_HL_TYPE);

        foreach ($this->catPrizeData as $data) {
            $res = $dataManager::query()
                ->setFilter(['UF_PRODUCT_XML_ID' => $data['UF_PRODUCT_XML_ID']])
                ->setSelect(['ID'])
                ->exec();

            if ($result = $res->fetch()) {
                $this->fillPromocode($result['ID'], $data['UF_PRODUCT_XML_ID']);
            }
        }

        foreach ($this->dogPrizeData as $data) {
            $res = $dataManager::query()
                ->setFilter(['UF_PRODUCT_XML_ID' => $data['UF_PRODUCT_XML_ID']])
                ->setSelect(['ID'])
                ->exec();

            if ($result = $res->fetch()) {
                $this->fillPromocode($result['ID'], $data['UF_PRODUCT_XML_ID']);
            }
        }
    }

    /**
     * @param $prizeId
     * @param $productXmlId
     * @throws Exception
     */
    protected function fillPromocode($prizeId, $productXmlId): void
    {
        $dataManager = HLBlockFactory::createTableObject(self::PROMOCODE_HL_TYPE);

        $fileDir = __DIR__ . "/../migration_sources/quest_promocode/$productXmlId.csv";

        $row = 0;
        $promocodeData = [];

        if (($handle = fopen($fileDir, 'rb')) !== false) {
            while ((($data = fgetcsv($handle, 1000, ';')) !== false)) {
                $row++;
                if ($row === 1) {
                    continue;
                }

                $promocodeData[] = $data[0];
            }
            fclose($handle);
        }

        foreach ($promocodeData as $data) {
            $dataManager::add([
                'UF_PRIZE' => $prizeId,
                'UF_PROMOCODE' => $data,
                'UF_ACTIVE' => 1
            ]);
        }
    }
}
