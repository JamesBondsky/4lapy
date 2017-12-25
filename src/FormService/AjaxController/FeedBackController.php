<?php
namespace FourPaws\FormService\AjaxController;

use FourPaws\App\Application as App;
use FourPaws\App\Response\JsonResponse;
use FourPaws\App\Response\JsonErrorResponse;
use FourPaws\App\Response\JsonSuccessResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class FeedBackController
 * @package FourPaws\UserBundle\AjaxController
 */
class FeedBackController extends Controller
{
    /**
     * @param Request $request
     *
     * @return JsonResponse
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     * @throws \Bitrix\Main\SystemException
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException
     */
    public function addAction(Request $request) : JsonResponse
    {
        $data = $request->request->getIterator()->getArrayCopy();
        $webFormId = $data['WEB_FORM_ID'];
        $reCaptchaService = App::getInstance()->getContainer()->get('recaptcha.service');
        $reCaptchaService->checkCaptcha();
        global $USER;
        $userID = 0;
        if($USER->IsAuthorized()) {
            $userID = (int)$USER->GetID();
        }
        unset($data['web_form_submit'], $data['WEB_FORM_ID']);
        
        $formResult = new \CFormResult();
        $res = $formResult->Add($webFormId, $data, 'N', $userID > 0 ? $userID : false);
        if($res){
            JsonSuccessResponse::create('Ваша завка принята');
        }
        else{
            return JsonErrorResponse::create('Произошла ошибка при сохранении');
        }
        
        return JsonErrorResponse::create('Неизвестаня ошибка');
    }
}