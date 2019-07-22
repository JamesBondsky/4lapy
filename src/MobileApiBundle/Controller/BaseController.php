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
            $client = new Client();
            try {
                /** @var UserService $userCurrentUserService */
                $userCurrentUserService = App::getInstance()->getContainer()->get(CurrentUserProviderInterface::class);
                $currentUser = $userCurrentUserService->getCurrentUser();
                $currentDate = new DateTime();
                $fields = [
                    'USER_ID' => $currentUser->getId()
                ];
                $getLastUsing = UserApiLastUsingTable::query()->setSelect(['ID', 'DATE_INSERT'])->addFilter('=USER_ID', $fields['USER_ID'])->setOrder(['ID' => 'DESC'])->exec()->fetch();
                if (!$getLastUsing || (isset($getLastUsing['DATE_INSERT']) && $getLastUsing['DATE_INSERT']->format('d.m.Y') != $currentDate->format('d.m.Y'))) {
                    if ($getLastUsing) {
                        $fields['DATE_INSERT'] = $currentDate;
                        UserApiLastUsingTable::update($getLastUsing['ID'], $fields);
                    } else {
                        UserApiLastUsingTable::add($fields);
                    }


                    $client->phone = $currentUser->getPersonalPhone();
                    $client->haveMobileApp = true;
                    $client->lastDateUseMobileApp = $currentDate->format(\DateTime::ATOM);

                    if ($client instanceof Client) {
                        $manzanaService->updateContactAsync($client);
                    }
                }
            } catch (NotAuthorizedException $e) {
            }
        }
    }
}
