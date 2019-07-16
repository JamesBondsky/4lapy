<?php


namespace FourPaws\MobileApiBundle\Controller;


use Bitrix\Main\Type\DateTime;
use FOS\RestBundle\Controller\FOSRestController;
use FourPaws\App\Application as App;
use FourPaws\External\Manzana\Model\Client;
use FourPaws\External\ManzanaService;
use FourPaws\UserBundle\Exception\NotAuthorizedException;
use FourPaws\UserBundle\Service\CurrentUserProviderInterface;
use FourPaws\UserBundle\Service\UserService;

class BaseController extends FOSRestController
{
    public function __destruct()
    {
        $currentDate = new DateTime();

        $container = App::getInstance()->getContainer();

        /** @var ManzanaService $manzanaService */
        $manzanaService = $container->get('manzana.service');
        $client = new Client();
        try {
            /** @var UserService $userCurrentUserService*/
            $userCurrentUserService = App::getInstance()->getContainer()->get(CurrentUserProviderInterface::class);
            $currentUser = $userCurrentUserService->getCurrentUser();

            $client->phone = $currentUser->getPersonalPhone();
            $client->haveMobileApp = true;
            $client->lastDateUseMobileApp = $currentDate->format(\DateTime::ATOM);

            if ($client instanceof Client) {
                $manzanaService->updateContactAsync($client);
            }
        } catch(NotAuthorizedException $e){
        }
    }
}
