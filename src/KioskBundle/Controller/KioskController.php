<?php
/**
 * Created by PhpStorm.
 * User: mmasterkov
 * Date: 08.05.2019
 * Time: 15:09
 */

namespace FourPaws\KioskBundle\Controller;

use CUser;
use FourPaws\App\Application;
use FourPaws\App\Response\JsonErrorResponse;
use FourPaws\App\Response\JsonSuccessResponse;
use FourPaws\App\Tools\RefererTrait;
use FourPaws\KioskBundle\Service\KioskService;
use FourPaws\UserBundle\Service\CurrentUserProviderInterface;
use FourPaws\UserBundle\Service\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class KioskController
 *
 * @package FourPaws\KioskBundle\Controller
 *
 * @Route("/kiosk")
 */
class KioskController extends Controller
{

    /**
     * Типы ошибок при сканировани ШК
     */
    const ERROR_BAD_CARD = 1; // не удалось распознать ШК
    const ERROR_MORE_THAN_ONE_USER = 2; // более 1 юзера

    /**
     * Авторизация по ШК
     *
     * @param $card
     * @Route("/auth/", methods={"GET", "POST"})
     * @throws \Exception
     */
    public function authByCard(Request $request)
    {
        global $USER;

        if(!KioskService::isKioskMode()){
            throw $this->createAccessDeniedException();
        }

        try {
            $card = $request->get('card');
            $card = $this->transformCard($card);
            if(empty($card)){
                throw new \Exception(self::ERROR_BAD_CARD);
            }

            $dbres = CUser::GetList($by, $order, ['UF_DISCOUNT_CARD' => $card]);
            if($dbres->SelectedRowsCount() > 1){
                throw new \Exception(self::ERROR_MORE_THAN_ONE_USER);
            }
            if($dbres->SelectedRowsCount() == 0){
                return $this->redirect('/personal/register/');
            }
            $user = $dbres->Fetch();

            if($USER->IsAuthorized()){
                $USER->Logout();
            }
            $USER->Authorize($user['ID']);
            $resultParams = ['auth' => 1];

        } catch (\Exception $e) {
            $resultParams = ['auth' => 0, 'error' => $e->getMessage()];
        }

        /** @var KioskService $kioskService */
        $kioskService = Application::getInstance()->getContainer()->get('kiosk.service');
        $lastUrl = $kioskService->addParamsToUrl($kioskService->getLastPageUrl(), $resultParams);

        return $this->redirect($lastUrl);
    }

    /**
     * @Route("/logout/", methods={"GET", "POST"})
     * @throws \Exception
     */
    public function logout(Request $request)
    {
        try {
            /** @var UserService $userService */
            $userService = Application::getInstance()->getContainer()->get(CurrentUserProviderInterface::class);
            if(!$userService->logout()){
                global $USER;
                $USER->Logout();
            }

            unset($_SESSION);

        } catch (\Exception $e) {
            // такого не должно быть
        }
        return $this->redirect("/");
    }

    /**
     * Привязка карты к заказу
     *
     * @param $card
     * @Route("/bindcard/", methods={"GET", "POST"})
     * @throws \Exception
     */
    public function bindCard(Request $request)
    {
        if(!KioskService::isKioskMode()){
            throw $this->createAccessDeniedException();
        }

        /** @var KioskService $kioskService */
        $kioskService = Application::getInstance()->getContainer()->get('kiosk.service');

        try {
            $card = $request->get('card');
            $card = $this->transformCard($card);
            if(empty($card)){
                throw new \Exception(self::ERROR_BAD_CARD);
            }
            $kioskService->setCardNumber($card);
        } catch (\Exception $e) {
            // TODO: обработать ошибки
        }

        return $this->redirect('/sale/order/payment/');
    }

    /**
     * Удаляет цифры из кода карты
     *
     * @param string $card
     * @return string|string[]|null
     */
    private function transformCard(string $card)
    {
        return preg_replace('/[^0-9]/', '', $card);
    }
}