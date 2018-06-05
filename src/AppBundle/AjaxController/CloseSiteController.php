<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 03.06.18
 * Time: 15:30
 */

namespace FourPaws\AppBundle\AjaxController;

use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\SystemException;
use Bitrix\Main\Type\DateTime;
use FourPaws\App\Application;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\App\Response\JsonResponse;
use FourPaws\App\Response\JsonSuccessResponse;
use FourPaws\BitrixOrm\Utils\EntityConstructor;
use FourPaws\SaleBundle\AgentController\ForgotBasketController;
use FourPaws\SaleBundle\Service\BasketService;
use FourPaws\UserBundle\Service\CurrentUserProviderInterface;
use FourPaws\UserBundle\Service\UserAuthorizationInterface;
use FourPaws\UserBundle\Service\UserAvatarAuthorizationInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class CloseSiteController extends Controller
{
    /**
     * @return JsonResponse
     */
    public function closeAction(): JsonResponse
    {
        $logger = LoggerFactory::create('forgot_basket_close_site');
        try {
            $container = Application::getInstance()->getContainer();
        } catch (ApplicationCreateException $e) {
            $logger->error('ошибка загрузки сервисов ' . $e->getMessage(), $e->getTrace());
            return JsonSuccessResponse::create('аякс успешен');
        }
        /** загружаем по интерфейсам - ибо может смениться - а сейчас доп. нагрузки нет ибо симлинки */
        /** @var UserAuthorizationInterface $userAuthService */
        $userAuthService = $container->get(UserAuthorizationInterface::class);
        /** только авторизованные пользователи */
        if ($userAuthService->isAuthorized()) {
            /** @var UserAvatarAuthorizationInterface $userAvatarService */
            $userAvatarService = $container->get(UserAvatarAuthorizationInterface::class);
            /** скипаем если это аватар */
            if (!$userAvatarService->isAvatarAuthorized()) {
                /** @var CurrentUserProviderInterface $currentUserService */
                $currentUserService = $container->get(CurrentUserProviderInterface::class);
                $curUser = $currentUserService->getCurrentUser();
                /** только с email */
                if ($curUser !== null && $curUser->hasEmail()) {
                    $fuserId = $currentUserService->getCurrentFUserId();
                    $userId = $curUser->getId();
                    if ($fuserId > 0 && $userId > 0) {
                        /** @var BasketService $basketService */
                        try {
                            $basketService = $container->get(BasketService::class);
                            $basket = $basketService->getBasket(false, $fuserId);
                            if ($basket->count() > 0) {
                                $time = time();
                                $date = DateTime::createFromTimestamp($time);
                                $date->add('+ 3 hours'); //+3 часа
                                $agentName = '\FourPaws\SaleBundle\AgentController\ForgotBasketController::sendEmailByOldBasketAfter3Hours(' . $fuserId . ');';

                                $agentDataManager = EntityConstructor::compileEntityDataClass('AgentTable', 'b_agent');
                                $agentItem = null;

                                $res = $agentDataManager::query()
                                    ->where('ACTIVE', 'Y')
                                    ->where('IS_PERIOD', 'N')
                                    ->where('USER_ID', $userId)
                                    ->where('NAME', $agentName)
                                    ->setSelect(['ID', 'NEXT_EXEC'])
                                    ->setLimit(1)
                                    ->exec();
                                if ($res->getSelectedRowsCount() > 0) {
                                    $agentItem = $res->fetch();
                                }

                                /** если в течении 3-х часов снова зашли на сайт и закрыли его, или сайт в нескольких вкладках
                                 * и мы позакрывали - то меняем точку отсчета
                                 * но смотрим рассинхро времени и изменяем если разница боьше 5 минут
                                 * иначе создадим эписную нагруку на обновлении при закрытии каскада вкладок
                                 */
                                if ($agentItem !== null) {
                                    /** @var DateTime $dateAgent */
                                    $dateAgent = $agentItem['NEXT_EXEC'];
                                    if ($dateAgent === null || (($date->getTimestamp() - $dateAgent->getTimestamp()) > 5 * 60)) {
                                        $agentDataManager::update((int)$agentItem['ID'], ['NEXT_EXEC' => $date]);
                                    }
                                } else {
                                    $agentDataManager::add([
                                        'ACTIVE'    => 'Y',
                                        'IS_PERIOD' => 'N',
                                        'SORT'      => 10,
                                        'NAME'      => $agentName,
                                        'USER_ID'   => $userId,
                                        'NEXT_EXEC' => $date,
                                    ]);
                                }
                            }
                        } catch (ArgumentException $e) {
                            $logger->error('ошибка получения id юзера ' . $e->getMessage(), $e->getTrace());
                        } catch (SystemException $e) {
                            $logger->error('ошибка получения данных из таблицы агентов ' . $e->getMessage(), $e->getTrace());
                        } catch (\Exception $e) {
                            $logger->error('ошибка при создании/обновлении агентов ' . $e->getMessage(), $e->getTrace());
                        }
                    }
                }
            }
        }
        return JsonSuccessResponse::create('аякс успешен');
    }
}