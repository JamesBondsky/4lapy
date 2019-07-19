<?

namespace FourPaws\PersonalBundle\AjaxController;

use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\App\Response\JsonErrorResponse;
use FourPaws\App\Response\JsonResponse;
use FourPaws\App\Response\JsonSuccessResponse;
use FourPaws\PersonalBundle\Service\PersonalOffersService;
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
     * @Route("/bind-unreserved-dobrolap-coupon/", methods={"POST", "GET"})
     * @param Request $request
     *
     * @return JsonResponse
     * @throws ApplicationCreateException
     */
    public function bindUnreservedDobrolapCouponAction(Request $request): JsonResponse
    {
        $orderID = $request->get('order_id');
        $curUser = $this->currentUserProvider->getCurrentUser();
        $coupon = $this->personalOffersService->bindDobrolapRandomCoupon($curUser, $orderID);

        if ($coupon['success']) {
            return JsonSuccessResponse::createWithData(
                '',
                [
                    'html' => $coupon['html']
                ]
            );
        } else {
            return JsonErrorResponse::create($coupon['message']);
        }
    }

}
