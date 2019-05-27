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

            $responce = JsonSuccessResponse::create(
                sprintf("Пользователь успешно разавторизован"),
                200,
                [],
                [
                    'reload' => true,
                    'redirect' => '',
                ]
            );

        } catch (\Exception $e) {
            $responce = JsonSuccessResponse::create(
                sprintf("Не удалось разавторизоваться: %s", $e->getMessage()),
                200,
                [],
                [
                    'reload' => true,
                    'redirect' => '',
                ]
            );
        }


        return $responce;
    }
}