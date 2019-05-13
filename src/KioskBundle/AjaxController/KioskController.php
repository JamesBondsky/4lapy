<?php
/**
 * Created by PhpStorm.
 * User: mmasterkov
 * Date: 08.05.2019
 * Time: 15:09
 */

namespace FourPaws\KioskBundle\AjaxController;

use CUser;
use FourPaws\App\Response\JsonErrorResponse;
use FourPaws\App\Response\JsonSuccessResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class KioskController
 *
 * @package FourPaws\KioskBundle\AjaxController
 *
 * @Route("/controller")
 */
class KioskController extends Controller
{
    protected $userSearchInterface;

    /**
     * @param $card
     * @Route("/auth/", methods={"GET", "POST"})
     * @throws \Exception
     */
    public function authByCard(Request $request)
    {
        global $USER;

        try {
            $card = $request->get('card');
            if(empty($card)){
                throw new \Exception('не передан номер карты');
            }

            $dbres = CUser::GetList($by, $order, ['UF_DISCOUNT_CARD' => $card]);
            if($dbres->SelectedRowsCount() > 1){
                throw new \Exception('найдено больше одного пользователя');
            }
            if($dbres->SelectedRowsCount() == 0){
                throw new \Exception('не найдено ни одного пользователя');
            }
            $user = $dbres->Fetch();

            if($USER->IsAuthorized()){
                $USER->Logout();
            }
            $USER->Authorize($user['ID']);

            $responce = JsonSuccessResponse::create(
                "Авторизация прошла успешно",
                200,
                [],
                [
                    'reload' => true,
                    'redirect' => '',
                ]
            );
        } catch (\Exception $e) {
            $responce = JsonSuccessResponse::create(
                sprintf("Не удалось авторизоваться: %s", $e->getMessage()),
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