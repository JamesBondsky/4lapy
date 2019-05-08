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
use FourPaws\UserBundle\Service\UserSearchInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class KioskController
 *
 * @package FourPaws\KioskBundle\AjaxController
 *
 * @Route("/kiosk")
 */
class KioskController extends Controller
{

    protected $userSearchInterface;


    public function __construct(
        UserSearchInterface $userSearchInterface
    ) {
        $this->userSearchInterface = $userSearchInterface;
    }

    /**
     * @param $card
     * @Route("/auth/", methods={"GET", "POST"})
     * @throws \Exception
     */
    public function authByCard(Request $request)
    {
        $card = $request->get('card');
        $dbres = CUser::GetList($by, $order, ['UF_DISCOUNT_CARD' => $card]);
        if($dbres->SelectedRowsCount() > 1){
            throw new \Exception('Найдено больше одного пользователя');
        }

        $user = $dbres->Fetch();

        $responce = JsonSuccessResponse::create(
            "Авторизация прошла успешно",
            200,
            [],
            [
                'reload' => true,
                'redirect' => '',
            ]
        );

        return $responce;
    }
}