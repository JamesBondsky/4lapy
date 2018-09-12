<?php

 namespace FourPaws\UserBundle\AjaxController;

use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use Elastica\Exception\Connection\GuzzleException;
use Exception;
use FourPaws\App\Response\JsonResponse;
use FourPaws\App\Response\JsonSuccessResponse;
use FourPaws\AppBundle\Service\AjaxMess;
use FourPaws\External\ExpertsenderService;
use FourPaws\UserBundle\Entity\User;
use FourPaws\UserBundle\Exception\BitrixRuntimeException;
use FourPaws\UserBundle\Exception\ConstraintDefinitionException;
use FourPaws\UserBundle\Exception\InvalidIdentifierException;
use FourPaws\UserBundle\Exception\NotAuthorizedException;
use FourPaws\UserBundle\Exception\NotFoundException;
use FourPaws\UserBundle\Exception\ValidationException;
use FourPaws\UserBundle\Repository\UserRepository;
use FourPaws\UserBundle\Service\CurrentUserProviderInterface;
use FourPaws\UserBundle\Service\UserService;
use Psr\Log\LoggerAwareInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class SubscribeController
 *
 * @package FourPaws\UserBundle\AjaxController
 * @Route("/subscribe")
 */
class SubscribeController extends Controller implements LoggerAwareInterface
{
    use LazyLoggerAwareTrait;

    /** @var AjaxMess */
    private $ajaxMess;
    /**
     * @var UserService
     */
    private $userService;
    /**
     * @var ExpertsenderService
     */
    private $expertsenderService;
    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * SubscribeController constructor.
     *
     * @param AjaxMess                     $ajaxMess
     * @param CurrentUserProviderInterface $userService
     * @param ExpertsenderService          $expertsenderService
     * @param UserRepository               $userRepository
     */
    public function __construct(AjaxMess $ajaxMess, CurrentUserProviderInterface $userService, ExpertsenderService $expertsenderService, UserRepository $userRepository)
    {
        $this->ajaxMess = $ajaxMess;
        $this->userService = $userService;
        $this->expertsenderService = $expertsenderService;
        $this->userRepository = $userRepository;
    }

    /**
     * @Route("/subscribe/", methods={"POST"})
     * @param Request $request
     *
     * @return JsonResponse
     *
     * @throws \RuntimeException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function subscribeAction(Request $request): JsonResponse
    {
        $type = $request->get('type', '');
        $email = $request->get('email', '');

        if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            return $this->ajaxMess->getWrongEmailError();
        }

        try {
            $user = $this->getCurrentUser();

            $oldEmail = $user->getEmail();
            $user->setEmail($email);

            $subscriptionResult = ($type === 'all')
                ? $this->expertsenderService->sendEmailSubscribeNews($user)
                : $this->expertsenderService->sendEmailUnSubscribeNews($user);

            if ($subscriptionResult && $user->getId()) {
                $this->updateUserEmail($user, $oldEmail, $type === 'all');
            }

            if ($subscriptionResult) {
                $success = true;

            } else {
                $success = false;
            }
        } catch (Exception | GuzzleException $e) {
            $success = false;
            $this->log()
                ->error(\sprintf(
                    'Subscription error: %s',
                    $e->getMessage()
                ));
        }

        return $success ? JsonSuccessResponse::create('Ваша подписка успешно изменена') : $this->ajaxMess->getSystemError();
    }

    /**
     * @return User
     */
    protected function getCurrentUser(): User
    {
        try {
            $user = $this->userService->getCurrentUser();
        } catch (NotAuthorizedException $e) {
            $user = new User();
        }

        return $user;
    }

    /**
     * @param User   $user
     * @param string $email
     * @param bool   $isSubscribed
     *
     * @throws ValidationException
     * @throws InvalidIdentifierException
     * @throws ConstraintDefinitionException
     * @throws BitrixRuntimeException
     * @throws NotFoundException
     */
    protected function updateUserEmail(User $user, string $email, bool $isSubscribed): void
    {
        if (!$user->getId()) {
            $user = $this->userService->findOneByEmail($user->getEmail());
        }

        if ($user->getId()) {
            if ($email) {
                $user->setEmail($email);
            }

            $user->setEsSubscribed($isSubscribed);
            $this->userRepository->update($user);
        }
    }
}
