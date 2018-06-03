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
use Bitrix\Main\Type\DateTime;
use Bitrix\Sale\Fuser;
use Bitrix\Sale\Internals\FuserTable;
use FourPaws\App\Application;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\App\Response\JsonResponse;
use FourPaws\App\Response\JsonSuccessResponse;
use FourPaws\SaleBundle\Service\BasketService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class CloseSiteController extends Controller
{
    /**
     * @return JsonResponse
     */
    public function addAction(): JsonResponse
    {
        $fuserId = Fuser::getId();
        if($fuserId > 0) {
            /** @var BasketService $basketService */
            $logger = LoggerFactory::create('forgot_basket_close_site');
            try {
                $userId = FuserTable::getUserById($fuserId);
                if($userId > 0) {
                    $container = Application::getInstance()->getContainer();
                    $basketService = $container->get(BasketService::class);
                    $basket = $basketService->getBasket(false, $fuserId);
                    if ($basket->count() > 0) {
                        $date = DateTime::createFromTimestamp(time());
                        $date->add('T3H'); //+3 часа
                        $formattedDate = $date->format('d.m.Y H:i:s');
                        \CAgent::AddAgent('\FourPaws\SaleBundle\AgentController\ForgotBasketController::sendEmailByOldBasketAfter3Hours(' . $fuserId . ');',
                            '', 'N', 0, $formattedDate, 'Y', $formattedDate);
                    }
                }
            } catch (ApplicationCreateException $e) {
                $logger->error('ошибка загрузки сервисов ' . $e->getMessage(), $e);
            } catch (ArgumentException $e) {
                $logger->error('ошибка получения id юзера ' . $e->getMessage(), $e);
            }
        }
        return JsonSuccessResponse::create('аякс успешен');
    }
}