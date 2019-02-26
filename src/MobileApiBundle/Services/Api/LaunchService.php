<?php


namespace FourPaws\MobileApiBundle\Services\Api;

use Bitrix\Main\ObjectException;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\External\ManzanaService as AppManzanaService;
use FourPaws\UserBundle\Exception\NotAuthorizedException;
use FourPaws\UserBundle\Service\UserService as AppUserService;
use FourPaws\MobileApiBundle\Services\Api\UserService as ApiUserService;
use FourPaws\External\Manzana\Model\Client as ManzanaClient;

class LaunchService
{

    /** @var AppManzanaService */
    private $appManzanaService;

    /** @var AppUserService */
    private $appUserService;

    /** @var ApiUserService */
    private $apiUserService;

    public function __construct(
        AppManzanaService $appManzanaService,
        AppUserService $appUserService,
        ApiUserService $apiUserService
    )
    {
        $this->appManzanaService = $appManzanaService;
        $this->appUserService = $appUserService;
        $this->apiUserService = $apiUserService;
    }

    /**
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\SystemException
     * @throws \FourPaws\PersonalBundle\Exception\InvalidArgumentException
     */
    public function onLaunchApp()
    {
        try {
            $user = $this->appUserService->getCurrentUser();

            $this->apiUserService->actualizeUserGroupsForApp();

            // обновление номера карты из манзаны в битриксовый профиль пользователя
            $this->appUserService->refreshUserCard($user);

            // отправка с сайта в манзану данных пользователя + параметров ffMobileApp и ffMobileAppDate
            try {
                $client = new ManzanaClient();
                // toDo дождаться ответа от разработчиков манзаны что ffMobileApp и ffMobileAppDate как-то используются и для передачи этих параметров через contract_update clientId - не нужен (достаточно того что выставляется в setClientPersonalDataByCurUser)
                $this->setClientMobileAppDate($client);
                $this->appUserService->setClientPersonalDataByCurUser($client);
                $this->appManzanaService->updateContactAsync($client);
            } catch (ApplicationCreateException $e) {
                // do nothing
            } catch (ObjectException $e) {
                // do nothing
            }
        } catch (NotAuthorizedException $e) {
            // it's okay if user is not authorized, do nothing
        }
    }

    /**
     * @param ManzanaClient $client
     * @throws ObjectException
     */
    protected function setClientMobileAppDate(ManzanaClient $client)
    {
        $client->ffMobileApp = 1;
        $client->ffMobileAppDate = (new \Bitrix\Main\Type\DateTime())->format('c');
    }
}