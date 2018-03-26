<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\Form\AjaxController;

use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use Bitrix\Main\SystemException;
use FourPaws\App\Application as App;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\App\Response\JsonResponse;
use FourPaws\App\Response\JsonSuccessResponse;
use FourPaws\AppBundle\Service\AjaxMess;
use FourPaws\Form\Exception\FileSaveException;
use FourPaws\Form\Exception\FileSizeException;
use FourPaws\Form\Exception\FileTypeException;
use FourPaws\Form\FormService;
use FourPaws\Helpers\Exception\WrongPhoneNumberException;
use FourPaws\Helpers\PhoneHelper;
use FourPaws\ReCaptcha\ReCaptchaService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class FeedBackController
 *
 * @package FourPaws\Form\AjaxController
 */
class FeedBackController extends Controller
{
    /** @var AjaxMess */
    private $ajaxMess;

    public function __construct(
        AjaxMess $ajaxMess
    ) {
        $this->ajaxMess = $ajaxMess;
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function addAction(Request $request): JsonResponse
    {
        $data = $request->request->all();

        try {
            $container = App::getInstance()->getContainer();
        } catch (ApplicationCreateException $e) {
            $logger = LoggerFactory::create('system');
            $logger->critical('Ошибка загрузки сервисов - ' . $e->getMessage());
            return $this->ajaxMess->getSystemError();
        }

        try {
            /** @var FormService $formService */
            $formService = $container->get('form.service');

            $requiredFields = [
                'name',
                'email',
                'phone',
                'theme',
                'message',
            ];
            $formatedFields = $formService->getRealNamesFields((int)$data['WEB_FORM_ID']);
            if (!$formService->checkRequiredFields($data,
                array_intersect_key($formatedFields, array_flip($requiredFields)))) {
                return $this->ajaxMess->getEmptyDataError();
            }

            if (!$formService->validEmail($data[$formatedFields['email']])) {
                return $this->ajaxMess->getWrongEmailError();
            }

            try {
                $data[$formatedFields['phone']] = PhoneHelper::normalizePhone($data[$formatedFields['phone']]);
            } catch (WrongPhoneNumberException $e) {
                return $this->ajaxMess->getWrongPhoneNumberException();
            }

            $fileCode = $formatedFields['file'];
            $fileSizeMb = 2 * 1024 * 1024;
            $valid_types = [
                'jpg',
                'png',
                'doc',
                'docx',
            ];

            try {
                $file = $formService->saveFile($fileCode, $fileSizeMb, $valid_types);
                if (!empty($file)) {
                    $data[$fileCode] = $file;
                }
            } catch (FileSaveException $e) {
                /** произошла ошибка соранения фалйа - сохраняем без файла */
            } catch (FileSizeException $e) {
                return $this->ajaxMess->getFileSizeError(2);
            } catch (FileTypeException $e) {
                return $this->ajaxMess->getFileTypeError($valid_types);
            }

            if ($request->request->has('g-recaptcha-response')) {
                /** @var ReCaptchaService $recaptchaService */
                $recaptchaService = $container->get('recaptcha.service');
                if (!$recaptchaService->checkCaptcha()) {
                    return $this->ajaxMess->getFailCaptchaCheckError();
                }
            }

            if ($formService->addResult($data)) {
                $_SESSION['FEEDBACK_SUCCESS'] = 'Y';

                return JsonSuccessResponse::create('Ваша завка принята', 200, [], ['reload' => true]);
            }

            return $this->ajaxMess->getAddError();
        } catch (ServiceNotFoundException|ServiceCircularReferenceException|\RuntimeException|SystemException $e) {
            $logger = LoggerFactory::create('system');
            $logger->critical('Ошибка загрузки сервисов - ' . $e->getMessage());
        }

        return $this->ajaxMess->getSystemError();
    }
}
