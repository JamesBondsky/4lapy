<?php


namespace FourPaws\MobileApiBundle\Controller;


use Bitrix\Main\Type\DateTime;
use FOS\RestBundle\Controller\FOSRestController;
use FourPaws\App\Application as App;
use FourPaws\External\Manzana\Model\Client;
use FourPaws\External\ManzanaService;

class BaseController extends FOSRestController
{
    public function __destruct()
    {
        $currentDate = new DateTime();

        $container = App::getInstance()->getContainer();

        /** @var ManzanaService $manzanaService */
        $manzanaService = $container->get('manzana.service');
        $client = new Client();
        $client->contactId = $manzanaService->getContactIdByUser();
        $client->haveMobileApp = true;
        $client->lastDateUseMobileApp = $currentDate->format(\DateTime::ATOM);

        if ($client instanceof Client) {
            $manzanaService->updateContactAsync($client);
        }
    }
}
