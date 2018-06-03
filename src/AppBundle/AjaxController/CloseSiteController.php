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
use Bitrix\Main\ObjectException;
use Bitrix\Main\SystemException;
use Bitrix\Main\Type\DateTime;
use Bitrix\Sale\Fuser;
use Bitrix\Sale\Internals\FuserTable;
use FourPaws\App\Application;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\App\Response\JsonResponse;
use FourPaws\App\Response\JsonSuccessResponse;
use FourPaws\BitrixOrm\Utils\EntityConstructor;
use FourPaws\SaleBundle\Service\BasketService;
use FourPaws\UserBundle\Service\UserAvatarAuthorizationInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class CloseSiteController extends Controller
{
    /**
     * @return JsonResponse
     */
    public function closeAction(): JsonResponse
    {
        $fuserId = Fuser::getId();
        if ($fuserId > 0) {
            /** @var BasketService $basketService */
            $logger = LoggerFactory::create('forgot_basket_close_site');
            try {
                $userId = FuserTable::getUserById($fuserId);
                /** @var UserAvatarAuthorizationInterface $userAvatarService */
                $userAvatarService = Application::getInstance()->getContainer()->get(UserAvatarAuthorizationInterface::class);
                /** скипаем если это аватар */
                if ($userId > 0 && !$userAvatarService->isAvatarAuthorized()) {
                    $container = Application::getInstance()->getContainer();
                    $basketService = $container->get(BasketService::class);
                    $basket = $basketService->getBasket(false, $fuserId);
                    if ($basket->count() > 0) {
                        $time = time();
                        $date = DateTime::createFromTimestamp($time);
                        $date->add('+ 3 hours'); //+3 часа
                        $formattedDate = $date->format('d.m.Y H:i:s');
                        $agentName = '\FourPaws\SaleBundle\AgentController\ForgotBasketController::sendEmailByOldBasketAfter3Hours(' . $fuserId . ');';

                        $agentDataManager = EntityConstructor::compileEntityDataClass('AgentTable', 'b_agent');
                        $agentItem = null;
                        $dateToFilter = clone $date;
                        $dateToFilter->add('- 5 minutes'); // отнимаем 5 минут, чтобы чекать агенты не при закрытии вкладок последовательно

                        $res = $agentDataManager::query()
                            ->where('ACTIVE', 'Y')
                            ->where('IS_PERIOD', 'N')
                            ->where('USER_ID', $userId)
                            ->where('NAME', $agentName)
                            ->where('NEXT_EXEC', '<', $dateToFilter)
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
                            try {
                                $dateAgent = new DateTime($agentItem['NEXT_EXEC'], 'd.m.Y H:i:s');
                            } catch (ObjectException $e) {
                                $dateAgent = null;
                            }
                            if ($dateAgent === null || (($date->getTimestamp() - $dateAgent->getTimestamp()) > 5 * 60)) {
                                \CAgent::Update($agentItem['ID'], ['NEXT_EXEC' => $formattedDate]);
                            }
                        } else {
                            /** в случае если такой агент создан уже, то вернется false и он создан не будет */
                            \CAgent::AddAgent($agentName,
                                '', 'N', 0, $formattedDate, 'Y', $formattedDate, 10, $userId, true);
                        }
                    }
                }
            } catch (ApplicationCreateException $e) {
                $logger->error('ошибка загрузки сервисов ' . $e->getMessage(), $e);
            } catch (ArgumentException $e) {
                $logger->error('ошибка получения id юзера ' . $e->getMessage(), $e);
            } catch (SystemException $e) {
                $logger->error('ошибка получения данных из таблицы агентов ' . $e->getMessage(), $e);
            }
        }
        return JsonSuccessResponse::create('аякс успешен');
    }
}