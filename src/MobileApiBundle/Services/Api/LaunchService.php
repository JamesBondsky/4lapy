<?php


namespace FourPaws\MobileApiBundle\Services\Api;

use Bitrix\Main\ObjectException;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\External\Exception\ManzanaServiceException;
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

            // обновление даты последнего входа в приложение
            try {
                $client = new ManzanaClient();
                try {
                    // если текущий пользак уже зареген в манзане - нужно просто обновить дату входа в приложение
                    // устанавливаем contactId
                    $contactId = $this->appManzanaService->getContactIdByUser();
                    if(!empty($contactId)) {
                        $client->contactId = $contactId;
                    }
                } catch (ManzanaServiceException $e) {
                    // если текущий пользак не зареген в манзане - регистриурем с данными из битрикс-профиля
                    /** @see Event::updateManzana */
                    $this->appUserService->setClientPersonalDataByCurUser($client);
                }
                // устанавливаем дату последнего входа в приложение
                $this->setClientMobileAppDate($client);
                // отправляем данные в манзану
                $this->appManzanaService->updateContactAsync($client);
            } catch (ApplicationCreateException $e) {
                // do nothing
            } catch (ObjectException $e) {
                // do nothing
            } catch (\Exception $e) {
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