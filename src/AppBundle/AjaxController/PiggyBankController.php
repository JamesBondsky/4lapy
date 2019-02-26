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
use FourPaws\PersonalBundle\Service\PiggyBankService;
use Picqer\Barcode\BarcodeGeneratorPNG;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\HttpFoundation\Request;


/**
 * Class PiggyBankController
 *
 * @package FourPaws\AppBundle\AjaxController
 */
class PiggyBankController extends Controller
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

            if (!ProtectorHelper::checkToken($request->get(ProtectorHelper::getField(ProtectorHelper::TYPE_PIGGY_BANK_EMAIL_SEND)), ProtectorHelper::TYPE_PIGGY_BANK_EMAIL_SEND)) {
                throw new JsonResponseException($this->ajaxMess->getWrongParamsError());
            }


            try {
                /** @var ExpertsenderService $sender */
                $sender = App::getInstance()->getContainer()->get('expertsender.service');
                /** @var PiggyBankService $piggyBankService */
                $piggyBankService = App::getInstance()->getContainer()->get('piggy_bank.service');
                $barcodeGenerator = new BarcodeGeneratorPNG();
                $coupon = $piggyBankService->getActiveCoupon()['COUPON_NUMBER'];

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

                $sender->sendPiggyBankEmail(
                    $USER->GetID(),
                    $USER->GetFullName(),
                    $request->get('email'),
                    $coupon,
                    'data:image/png;base64,' . $barcodeGenerator->getBarcode($coupon, \Picqer\Barcode\BarcodeGenerator::TYPE_CODE_128, 2.803149606299213, 127)
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

            $token = ProtectorHelper::generateToken(ProtectorHelper::TYPE_PIGGY_BANK_EMAIL_SEND);
            return JsonSuccessResponse::createWithData('Купон отправлен на почту', [
                'field' => $token['field'],
                'value' => $token['token'],
            ]);

        } catch (JsonResponseException $e) {

            $token = ProtectorHelper::generateToken(ProtectorHelper::TYPE_PIGGY_BANK_EMAIL_SEND);
            $token['value'] = $token['token'];
            unset($token['token']);

            return $e->getJsonResponse()->extendData($token);
        }
    }

}
