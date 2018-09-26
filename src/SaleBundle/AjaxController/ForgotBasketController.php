<?php

namespace FourPaws\SaleBundle\AjaxController;

use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\App\Response\JsonResponse;
use FourPaws\App\Response\JsonSuccessResponse;
use FourPaws\SaleBundle\Entity\ForgotBasket;
use FourPaws\SaleBundle\Enum\ForgotBasketEnum;
use FourPaws\SaleBundle\Exception\ForgotBasket\ForgotBasketExceptionInterface;
use FourPaws\SaleBundle\Service\ForgotBasketService;
use FourPaws\UserBundle\Exception\NotAuthorizedException;
use FourPaws\UserBundle\Service\CurrentUserProviderInterface;
use FourPaws\UserBundle\Service\UserAvatarAuthorizationInterface;
use Psr\Log\LoggerAwareInterface;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

/**
 * Class ForgotBasketController
 *
 * @package FourPaws\SaleBundle\Controller
 * @Route("/forgot-basket")
 */
class ForgotBasketController extends Controller implements LoggerAwareInterface
{
    use LazyLoggerAwareTrait;

    /**
     * @var UserAvatarAuthorizationInterface
     */
    private $userAvatarAuthorization;

    /**
     * @var CurrentUserProviderInterface
     */
    private $currentUserProvider;

    /**
     * @var ForgotBasketService
     */
    private $forgotBasketService;

    /**
     * ForgotBasketController constructor.
     * @param UserAvatarAuthorizationInterface $userAvatarAuthorization
     * @param CurrentUserProviderInterface     $currentUserProvider
     * @param ForgotBasketService              $forgotBasketService
     */
    public function __construct(
        UserAvatarAuthorizationInterface $userAvatarAuthorization,
        CurrentUserProviderInterface $currentUserProvider,
        ForgotBasketService $forgotBasketService
    )
    {
        $this->userAvatarAuthorization = $userAvatarAuthorization;
        $this->currentUserProvider = $currentUserProvider;
        $this->forgotBasketService = $forgotBasketService;
    }

    /**
     * @Route("/close-page/", methods={"GET", "POST"})
     *
     * @return JsonResponse
     * @throws ApplicationCreateException
     * @throws RuntimeException
     */
    public function closePageAction(): JsonResponse
    {
        try {
            $user = $this->currentUserProvider->getCurrentUser();
            if ($user->getEmail() && !$this->userAvatarAuthorization->isAvatarAuthorized()) {
                try {
                    $task = (new ForgotBasket())->setType(ForgotBasketEnum::TYPE_NOTIFICATION)
                                                ->setUserId($user->getId())
                                                ->setActive(true);
                    $this->forgotBasketService->saveTask($task);
                } catch (ForgotBasketExceptionInterface $e) {
                    $this->log()->error($e->getMessage(), ['user' => $user->getId()]);
                }
            }
        } catch (NotAuthorizedException $e) {
        } catch (\Exception $e) {
            $this->log()->error(
                \sprintf(
                    '%s: %s',
                    \get_class($e),
                    $e->getMessage()
                ),
                [
                    'trace' => $e->getTrace()
                ]
            );
        }

        return JsonSuccessResponse::create('');
    }
}
