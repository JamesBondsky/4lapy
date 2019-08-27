<?php


namespace FourPaws\MobileApiBundle\Controller;


use Bitrix\Main\Type\DateTime;
use FOS\RestBundle\Controller\FOSRestController;
use FourPaws\App\Application as App;
use FourPaws\External\Manzana\Model\Client;
use FourPaws\External\ManzanaService;
use FourPaws\MobileApiBundle\Tables\UserApiLastUsingTable;
use FourPaws\UserBundle\Exception\NotAuthorizedException;
use FourPaws\UserBundle\Service\CurrentUserProviderInterface;
use FourPaws\UserBundle\Service\UserService;

class BaseController extends FOSRestController
{
    public function __destruct()
    {
        if (strripos($_SERVER['REQUEST_URI'], '/api/') === 0) {
            $container = App::getInstance()->getContainer();

            /** @var ManzanaService $manzanaService */
            $manzanaService = $container->get('manzana.service');
            try {
                /** @var UserService $userCurrentUserService */
                $userCurrentUserService = $container->get(CurrentUserProviderInterface::class);
                $currentUser = $userCurrentUserService->getCurrentUser();
                $userId = $currentUser->getId();
                $personalPhone = $currentUser->getPersonalPhone();

                $manzanaService->updateContactMobileAsync(['userId' => $userId, 'personalPhone' => $personalPhone]);
            } catch (NotAuthorizedException $e) {
            }
        }
    }
}
