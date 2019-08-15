<?php


namespace FourPaws\External\Manzana\Consumer;


use Bitrix\Main\Type\DateTime;
use FourPaws\App\Application as App;
use FourPaws\External\Manzana\Model\Client;
use FourPaws\External\ManzanaService;
use FourPaws\Helpers\PhoneHelper;
use FourPaws\MobileApiBundle\Tables\UserApiLastUsingTable;
use FourPaws\UserBundle\EventController\Event;
use PhpAmqpLib\Message\AMQPMessage;

class ManzanaContactMobileUpdateConsumer extends ManzanaConsumerBase
{
    public function execute(AMQPMessage $message)
    {
        Event::disableEvents();
        $userData = $this->serializer->deserialize($message->getBody(), 'array', 'json');

        $userId = $userData['userId'];
        $personalPhone = $userData['personalPhone'];

        $personalPhone = PhoneHelper::getManzanaPhone($personalPhone);

        $currentDate = new DateTime();
        $fields = [
            'USER_ID' => $userId
        ];
        $getLastUsing = UserApiLastUsingTable::query()->setSelect(['ID', 'DATE_INSERT'])->addFilter('=USER_ID', $fields['USER_ID'])->setOrder(['ID' => 'DESC'])->exec()->fetch();
        if (!$getLastUsing || (isset($getLastUsing['DATE_INSERT']) && $getLastUsing['DATE_INSERT']->format('d.m.Y') != $currentDate->format('d.m.Y'))) {
            if ($getLastUsing) {
                $fields['DATE_INSERT'] = clone $currentDate;
                UserApiLastUsingTable::update($getLastUsing['ID'], $fields);
            } else {
                UserApiLastUsingTable::add($fields);
            }

            $client = new Client();
            $client->phone = $personalPhone;
            $client->haveMobileApp = true;
            $client->lastDateUseMobileApp = $currentDate->format(\DateTime::ATOM);

            if ($client->phone) {
                $container = App::getInstance()->getContainer();
                /** @var ManzanaService $manzanaService */
                $manzanaService = $container->get('manzana.service');

                try {
                    $manzanaClient = $manzanaService->getContactByPhone($personalPhone);

                    $client->contactId = $manzanaClient->contactId;
                } catch (\Exception $e) {}

                $manzanaService->updateContact($client);
            }
        }

        Event::enableEvents();

        return true;
    }
}
