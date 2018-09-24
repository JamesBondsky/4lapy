<?php

namespace FourPaws\FormBundle\AjaxController;

use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use DateTime;
use FOS\RestBundle\Controller\Annotations as Rest;
use FourPaws\App\Application;
use FourPaws\App\Response\JsonSuccessResponse;
use FourPaws\AppBundle\Callback\CallbackService;
use FourPaws\AppBundle\Service\AjaxMess;
use FourPaws\FormBundle\Exception\FileSaveException;
use FourPaws\FormBundle\Exception\FileSizeException;
use FourPaws\FormBundle\Exception\FileTypeException;
use FourPaws\FormBundle\Service\FormService;
use FourPaws\Helpers\Exception\WrongPhoneNumberException;
use FourPaws\Helpers\PhoneHelper;
use FourPaws\ReCaptchaBundle\Service\ReCaptchaInterface;
use Psr\Log\LoggerAwareInterface;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class FormController
 *
 * @todo    add middleware (by form type)
 * @Rest\Route('/add')
 *
 * @package FourPaws\FormBundle\AjaxController
 */
class FormController extends Controller implements LoggerAwareInterface
{
    use LazyLoggerAwareTrait;

    private const MAX_FORM_FILE_SIZE = 2 * 1024 * 1024;

    /**
     * @var FormService
     */
    private $formService;
    /**
     * @var AjaxMess
     */
    private $ajaxMessService;
    /**
     * @var CallbackService
     */
    private $callbackService;
    /**
     * @var ReCaptchaInterface
     */
    private $recaptchaService;

    /**
     * FormController constructor.
     *
     * @param FormService        $formService
     * @param AjaxMess           $ajaxMessService
     * @param ReCaptchaInterface $recaptchaService
     */
    public function __construct(FormService $formService, AjaxMess $ajaxMessService, ReCaptchaInterface $recaptchaService)
    {
        $this->formService = $formService;
        $this->ajaxMessService = $ajaxMessService;
        $this->recaptchaService = $recaptchaService;
        $this->callbackService = Application::getInstance()->getContainer()->get('callback.service');
    }

    /**
     * @todo ParamConverter
     * @todo Decomposition
     * @todo Validators
     * @todo Symfony Forms
     * @todo MiddleWare
     *
     * @Route("/feedback/", methods={"POST"})
     *
     * @param Request $request
     *
     * @return JsonResponse
     *
     * @throws RuntimeException
     */
    public function addFeedbackAction(Request $request): JsonResponse
    {
        $formId = (int)$request->get('WEB_FORM_ID');

        $_SESSION['FEEDBACK_SUCCESS'] = 'Y';

        $response = $this->getFormResponse(
            $formId,
            $this->formService->getFormFieldsByRequest($request),
            [
                'name',
                'email',
                'phone',
                'theme',
                'message',
            ],
            [
                'jpg',
                'png',
                'doc',
                'docx',
            ]
        );

        if (null === $response) {
            $response = JsonSuccessResponse::create('Ваша завка принята', 200, [], ['reload' => true]);
            $_SESSION['FEEDBACK_SUCCESS'] = 'Y';
        }

        return $response;
    }

    /**
     * @todo единый action
     *
     * @param int   $formId
     * @param array $formData
     * @param array $requiredFields
     * @param array $availableFileTypes
     *
     * @return JsonResponse
     */
    protected function getFormResponse(int $formId, array $formData, array $requiredFields, array $availableFileTypes = []): ?JsonResponse
    {
        if (!$this->recaptchaService->checkCaptcha()) {
            return $this->ajaxMessService->getFailCaptchaCheckError();
        }

        $formattedFields = $this->formService->getRealNamesFields($formId);

        if (!$this->formService->checkRequiredFields($formData, \array_intersect_key($formattedFields, \array_flip($requiredFields)))) {
            return $this->ajaxMessService->getEmptyDataError();
        }

        if (!$this->formService->validEmail($formData[$formattedFields['email']])) {
            return $this->ajaxMessService->getWrongEmailError();
        }

        try {
            if ($formattedFields['phone']) {
                $data[$formattedFields['phone']] = PhoneHelper::formatPhone($formData[$formattedFields['phone']]);
            }
        } catch (WrongPhoneNumberException $e) {
            return $this->ajaxMessService->getWrongPhoneNumberException();
        }

        try {
            if ($availableFileTypes && $formattedFields['file']) {
                $file = $this->formService->saveFile($formattedFields['file'], self::MAX_FORM_FILE_SIZE, $availableFileTypes);

                if (!$file) {
                    $data[$formattedFields['file']] = $file;
                }
            }
        } catch (FileSaveException $e) {
            $this->log()->error(\sprintf(
                'File save error: %s',
                $e->getMessage()
            ));
        } catch (FileSizeException $e) {
            return $this->ajaxMessService->getFileSizeError(2);
        } catch (FileTypeException $e) {
            return $this->ajaxMessService->getFileTypeError($availableFileTypes);
        }

        if ($this->formService->addResult($formData)) {
            /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
            return null;
        }

        return $this->ajaxMessService->getAddError();
    }

    /**
     * @todo ParamConverter
     * @todo Decomposition
     * @todo Validators
     * @todo Symfony Forms
     * @todo MiddleWare
     *
     * @Route("/faq/", methods={"POST"})
     *
     * @param Request $request
     *
     * @return JsonResponse
     *
     * @throws RuntimeException
     */
    public function addFaqAction(Request $request): JsonResponse
    {
        $formId = (int)$request->get('WEB_FORM_ID');
        $response = $this->getFormResponse(
            $formId,
            $this->formService->getFormFieldsByRequest($request),
            [
                'name',
                'email',
                'phone',
                'message',
            ]
        );

        if (null === $response) {
            $response = JsonSuccessResponse::create('Спасибо! В ближайшее время специалист свяжется с Вами и ответит на вопрос');
        }

        return $response;
    }

    /**
     * @todo ParamConverter
     * @todo Decomposition
     * @todo Validators
     * @todo Symfony Forms
     * @todo MiddleWare
     *
     * @Route("/callback/", methods={"POST"})
     *
     * @param Request $request
     *
     * @return JsonResponse
     *
     * @throws RuntimeException
     */
    public function addCallbackAction(Request $request): JsonResponse
    {
        $formId = (int)$request->get('WEB_FORM_ID');
        $data = $this->formService->getFormFieldsByRequest($request);
        $formattedFields = $this->formService->getRealNamesFields($formId);
        $phone = PhoneHelper::formatPhone($data[$formattedFields['phone']], PhoneHelper::FORMAT_URL);

        $response = $this->getFormResponse(
            $formId,
            $data,
            [
                'name',
                'phone',
                'time_call',
            ],
            []
        );

        if (null === $response) {
            $response = JsonSuccessResponse::create('Ваша завка принята');

            if ($phone) {
                $this->callbackService->send(
                    $phone,
                    (new DateTime())->format('Y-m-d H:i:s')
                );
            }
        }

        return $response;
    }
}
