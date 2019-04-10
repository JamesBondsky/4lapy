<?php
namespace FourPaws\AppBundle\AjaxController;

use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use FourPaws\App\Application as App;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\App\Response\JsonResponse;
use FourPaws\App\Response\JsonSuccessResponse;
use FourPaws\AppBundle\Exception\JsonResponseException;
use FourPaws\AppBundle\Service\AjaxMess;
use FourPaws\External\ExpertsenderService;
use FourPaws\Helpers\ProtectorHelper;
use FourPaws\PersonalBundle\Service\PersonalOffersService;
use Picqer\Barcode\BarcodeGenerator;
use Picqer\Barcode\BarcodeGeneratorPNG;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\HttpFoundation\Request;


/**
 * Class PersonalOffersController
 *
 * @package FourPaws\AppBundle\AjaxController
 */
class PersonalOffersController extends Controller
{

    /** @var AjaxMess */
    private $ajaxMess;

    public function __construct()
    {
        try {
            $container = App::getInstance()->getContainer();
            $this->ajaxMess = $container->get('ajax.mess');
        } catch (ApplicationCreateException|ServiceNotFoundException|ServiceCircularReferenceException $e) {
            $logger = LoggerFactory::create('system');
            $logger->critical('Ошибка загрузки сервисов - ' . $e->getMessage());
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws \Exception
     */
    public function sendEmailAction(Request $request): JsonResponse
    {
        global $USER;

        try {

            if (!$USER->IsAuthorized()) {
                throw new JsonResponseException($this->ajaxMess->getNotAuthorizedException());
            }

            if (!$request->get('email')) {
                throw new JsonResponseException($this->ajaxMess->getEmptyDataError());
            }

            if (!$request->get('ID_COUPON_PERSONAL_OFFERS') || !ProtectorHelper::checkToken($request->get(ProtectorHelper::getField(ProtectorHelper::TYPE_PERSONAL_OFFERS_EMAIL_SEND)), ProtectorHelper::TYPE_PERSONAL_OFFERS_EMAIL_SEND)) {
                throw new JsonResponseException($this->ajaxMess->getWrongParamsError());
            }


            try {
                /** @var ExpertsenderService $sender */
                $sender = App::getInstance()->getContainer()->get('expertsender.service');
                $barcodeGenerator = new BarcodeGeneratorPNG();
                $coupon = $request->get('ID_COUPON_PERSONAL_OFFERS');

                /** в случае если в email пользователя указана не корректная почта, то переписываем её почтой на которую отправляется купон */
                if (false
                    || !$USER->GetEmail()
                    || $USER->GetEmail() == 'no@mail.ru'
                    || mb_strpos($USER->GetEmail(), '@register.phone') !== false
                    || mb_strpos($USER->GetEmail(), '@fastorder.ru') !== false
                ) {
                    $user = new \CUser;
                    $user->Update($USER->GetID(), [
                        'EMAIL' => $request->get('email'),
                    ]);
                }

                /** @var PersonalOffersService $personalOffersService */
                $personalOffersService = App::getInstance()->getContainer()->get('personal_offers.service');
                $offerFields = $personalOffersService->getOfferFieldsByPromoCode('skidka025');
                $couponDescription = $offerFields->get('PREVIEW_TEXT');
                $couponDateActiveTo = $offerFields->get('DATE_ACTIVE_TO');
                $discountValue = $offerFields->get('PROPERTY_DISCOUNT_VALUE');

                $sender->sendPersonalOfferCouponEmail(
                    $USER->GetID(),
                    $USER->GetFirstName(),
                    $request->get('email'),
                    $coupon,
                    'data:image/png;base64,' . base64_encode($barcodeGenerator->getBarcode($coupon, BarcodeGenerator::TYPE_CODE_128, 2.132310384278889, 127)),
                    $couponDescription,
                    $couponDateActiveTo,
                    $discountValue
                );
            }
            catch (\Exception $exception)
            {
                $logger = LoggerFactory::create('expertSender');
                $logger->error(sprintf(
                    'Error while sending mail. %s exception: %s',
                    __METHOD__,
                    $exception->getMessage()
                ));
            }

            $token = ProtectorHelper::generateToken(ProtectorHelper::TYPE_PERSONAL_OFFERS_EMAIL_SEND);
            return JsonSuccessResponse::createWithData('Купон отправлен на почту', [
                'field' => $token['field'],
                'value' => $token['token'],
            ]);

        } catch (JsonResponseException $e) {

            $token = ProtectorHelper::generateToken(ProtectorHelper::TYPE_PERSONAL_OFFERS_EMAIL_SEND);
            $token['value'] = $token['token'];
            unset($token['token']);

            return $e->getJsonResponse()->extendData($token);
        }
    }

}
