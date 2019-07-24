<?

namespace FourPaws\PersonalBundle\AjaxController;

use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\App\Response\JsonErrorResponse;
use FourPaws\App\Response\JsonResponse;
use FourPaws\App\Response\JsonSuccessResponse;
use FourPaws\PersonalBundle\Service\PersonalOffersService;
use FourPaws\UserBundle\Exception\NotAuthorizedException;
use FourPaws\UserBundle\Service\CurrentUserProviderInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class PersonalOffersController
 *
 * @package FourPaws\PersonalBundle\AjaxController
 * @Route("/personal_offers")
 */
class PersonalOffersController extends Controller
{
    /** @var PersonalOffersService $personalOffersService */
    private $personalOffersService;
    /** @var CurrentUserProviderInterface */
    private $currentUserProvider;

    public function __construct(PersonalOffersService $PersonalOffersService, CurrentUserProviderInterface $currentUserProvider)
    {
        $this->personalOffersService = $PersonalOffersService;
        $this->currentUserProvider = $currentUserProvider;
    }

    /**
     * @Route("/bind-unreserved-dobrolap-coupon/", methods={"POST"})
     * @param Request $request
     *
     * @return JsonResponse
     * @throws ApplicationCreateException
     * @throws \Adv\Bitrixtools\Exception\IblockNotFoundException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\LoaderException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public function bindUnreservedDobrolapCouponAction(Request $request): JsonResponse
    {
        $orderID = $request->get('order_id');
        $fuser = false;
        try {
            $curUser = $this->currentUserProvider->getCurrentUser();
            $userID = (string) $curUser->getId();
        } catch (NotAuthorizedException $e) {
            $fuser = true;
            $userID = (string) $_COOKIE['BX_USER_ID'];
        }

        $coupon = $this->personalOffersService->bindDobrolapRandomCoupon($userID, $orderID, $fuser, true);

        if ($coupon['success']) {
            return JsonSuccessResponse::createWithData(
                '',
                [
                    'html' => $coupon['data']
                ]
            );
        } else {
            return JsonErrorResponse::create($coupon['message']);
        }
    }

}
