<?php

namespace Sprint\Migration;


use FourPaws\App\Application;
use Bitrix\Highloadblock as HL;
use Bitrix\Main\Entity;
use FourPaws\PersonalBundle\Service\OrderSubscribeHistoryService;
use FourPaws\PersonalBundle\Service\OrderSubscribeService;

class HLBlockOrderSubscribeUpdateDateCheck20190611113445 extends \Adv\Bitrixtools\Migration\SprintMigrationBase {

    protected $description = "Обновляет свойство \"Дата оформления следующего заказа\" для старых подписок.";

    public function up(){
        $helper = new HelperManager();

        /** @var OrderSubscribeService $orderSubscriveService */
        $orderSubscriveService = Application::getInstance()->getContainer()->get('order_subscribe.service');
        /** @var OrderSubscribeHistoryService $orderSubscriveService */
        $orderSubscribeHistoryService = Application::getInstance()->getContainer()->get('order_subscribe_history.service');

        $hlblockId = $helper->Hlblock()->getHlblock('OrderSubscribe');
        $entity = HL\HighloadBlockTable::compileEntity($hlblockId);
        $entity_data_class = $entity->getDataClass();

        $rs = $entity_data_class::getList([
            'select' => ['ID'],
            'filter' => [
                '!=UF_NEXT_DEL' => false,
                '!=UF_CHECK_DAYS' => false,
                '>UF_ORDER_ID' => 0,
                'UF_DATE_CHECK' => null,
            ],
            'limit' => 5
        ]);

        while($row = $rs->fetch()){
            try {
                $subscribeId = $row['ID'];
                $orderSubscribe = $orderSubscriveService->getById($subscribeId);

                $isOrderCreated = $orderSubscribeHistoryService->wasOrderCreated(
                    $orderSubscribe->getOrderId(),
                    new \DateTime($orderSubscribe->getNextDate()->toString())
                );

                if($isOrderCreated || $orderSubscribe->getNextDate()->getTimestamp() < time()){
                    $orderSubscriveService->countNextDate($orderSubscribe);
                    while($orderSubscribe->getNextDate()->getTimestamp() < time()){
                        $orderSubscriveService->countNextDate($orderSubscribe);
                    }
                }

                $orderSubscribe->countDateCheck();
                $orderSubscriveService->update($orderSubscribe);
            } catch (\Exception $e) {
                print_r(sprintf('<br><span style="color: red">Не удалось обновить: %s. Ошибка: %s</span>', $orderSubscribe->getId(), $e->getMessage()));
            }

            print(sprintf('<br>Обновлено: %s. Дата: %s', $orderSubscribe->getId(), $orderSubscribe->getDateCheck()));
        }

        return true;
    }

    public function down(){
        $helper = new HelperManager();
        return true;
    }

}
