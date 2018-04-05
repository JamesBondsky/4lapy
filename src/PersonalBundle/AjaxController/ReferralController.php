<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\PersonalBundle\AjaxController;

use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\App\Response\JsonResponse;
use FourPaws\App\Response\JsonSuccessResponse;
use FourPaws\AppBundle\Exception\EmptyEntityClass;
use FourPaws\AppBundle\Service\AjaxMess;
use FourPaws\External\Exception\ManzanaServiceException;
use FourPaws\External\Manzana\Exception\CardNotFoundException;
use FourPaws\External\Manzana\Exception\ContactUpdateException;
use FourPaws\External\Manzana\Model\Card;
use FourPaws\Helpers\TaggedCacheHelper;
use FourPaws\PersonalBundle\Service\ReferralService;
use FourPaws\UserBundle\Exception\BitrixRuntimeException;
use FourPaws\UserBundle\Exception\ConstraintDefinitionException;
use FourPaws\UserBundle\Exception\InvalidIdentifierException;
use FourPaws\UserBundle\Exception\NotAuthorizedException;
use FourPaws\UserBundle\Exception\ValidationException;
use FourPaws\UserBundle\Service\UserAuthorizationInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class ReferralController
 *
 * @package FourPaws\PersonalBundle\AjaxController
 * @Route("/referral")
 */
class ReferralController extends Controller
{
    /**
     * @var ReferralService
     */
    private $referralService;

    /** @var AjaxMess */
    private $ajaxMess;
    /** @var UserAuthorizationInterface */
    private $userAuthorization;

    public function __construct(
        ReferralService $referralService,
        UserAuthorizationInterface $userAuthorization,
        AjaxMess $ajaxMess
    ) {
        $this->referralService = $referralService;
        $this->userAuthorization = $userAuthorization;
        $this->ajaxMess = $ajaxMess;
    }

    /**
     * @Route("/add/", methods={"POST"})
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function addAction(Request $request): JsonResponse
    {
        if (!$this->userAuthorization->isAuthorized()) {
            return $this->ajaxMess->getNeedAuthError();
        }
        $data = $request->request->all();
        TrimArr($data);
        if (empty($data)) {
            return $this->ajaxMess->getEmptyDataError();
        }
        if (!empty($data['UF_CARD'])) {
            $data['UF_CARD'] = preg_replace("/\D/", '', $data['UF_CARD']);
        }
        try {
            /** если не нашли карту валидируем ее, иначе делаем вид что ок */
            try {
                $this->referralService->manzanaService->searchCardByNumber($data['UF_CARD']);
            } catch(CardNotFoundException $e){
                if (!$this->referralService->manzanaService->validateCardByNumber($data['UF_CARD'])) {
                    return $this->ajaxMess->getWrongCardNumber();
                }
            }

        }
        catch (ManzanaServiceException $e) {
            $logger = LoggerFactory::create('manzana');
            $logger->error('Ошибка манзаны - ' . $e->getMessage());
            return $this->ajaxMess->getSystemError();
        }
        $data['UF_MODERATED'] = 'Y';
        $data['UF_CANCEL_MODERATE'] = 'N';
        try {
            if ($this->referralService->add($data)) {
                TaggedCacheHelper::clearManagedCache(['personal:referral:'.$data['UF_USER_ID']]);
                return JsonSuccessResponse::create(
                    'Реферал добавлен, ожидайте модерации',
                    200,
                    [],
                    ['reload' => true]
                );
            }
        } catch (ManzanaServiceException|ContactUpdateException $e) {
            $logger = LoggerFactory::create('manzana');
            $logger->error('Ошибка манзаны - ' . $e->getMessage());
        } catch (BitrixRuntimeException $e) {
            return $this->ajaxMess->getAddError($e->getMessage());
        } catch (EmptyEntityClass $e) {
            return $this->ajaxMess->getAddError();
        } catch (NotAuthorizedException $e) {
            return $this->ajaxMess->getNeedAuthError();
        } catch (ValidationException|InvalidIdentifierException|ConstraintDefinitionException $e) {
            $logger = LoggerFactory::create('params');
            $logger->error('Ошибка параметров - ' . $e->getMessage());
        } catch (ApplicationCreateException|ServiceNotFoundException|ServiceCircularReferenceException|\RuntimeException|\Exception $e) {
            $logger = LoggerFactory::create('system');
            $logger->critical('Ошибка загрузки сервисов - ' . $e->getMessage());
        }

        return $this->ajaxMess->getSystemError();
    }

    /**
     * @Route("/get_user_info/", methods={"POST"})
     * @param Request $request
     *
     * @return JsonResponse
     * @throws \RuntimeException
     */
    public function getUserInfoAction(Request $request): JsonResponse
    {
        $card = $request->get('card');
        if (!empty($card)) {
            $card = preg_replace("/\D/", '', $card);
        }
        if (empty($card)) {
            return $this->ajaxMess->getEmptyCardNumber();
        }
        if(\mb_strlen($card) < 13){
            return $this->ajaxMess->getWrongCardNumber();
        }
        /** @var Card $currentCard */
        try {
            $currentCard = $this->referralService->manzanaService->searchCardByNumber($card);
            /** убираем проверку - ибо если карта есть будет возвращать ошибку
             * @todo удалить
             */
//            if(!$this->referralService->manzanaService->validateCardByNumber($card)){
//                return $this->ajaxMess->getWrongCardNumber();
//            }
            $cardInfo = [
                'last_name'   => $currentCard->lastName,
                'name'        => $currentCard->firstName,
                'second_name' => $currentCard->secondName,
                'phone'       => $currentCard->phone,
                'email'       => $currentCard->email,
            ];
            return JsonSuccessResponse::createWithData(
                'Информация о карте получена',
                ['card' => $cardInfo]
            );
        } catch(CardNotFoundException $e){
            return $this->ajaxMess->getCardNotFoundError();
        }
        catch (ManzanaServiceException $e) {
            $logger = LoggerFactory::create('manzana');
            $logger->error('Ошибка манзаны - ' . $e->getMessage());
        }
        return $this->ajaxMess->getSystemError();
    }
}
