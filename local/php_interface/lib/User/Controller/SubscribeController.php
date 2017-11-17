<?php

namespace FourPaws\User\Controller;

use FourPaws\App\Application;
use FourPaws\App\Response\JsonErrorResponse;
use FourPaws\App\Response\JsonSuccessResponse;
use FourPaws\External\Exception\ExpertsenderServiceException;
use FourPaws\External\ExpertsenderService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class SubscribeController extends Controller
{
    /**@var ExpertsenderService */
    protected $expertsenderService;
    
    public function __construct()
    {
        $this->expertsenderService = Application::getInstance()->getContainer()->get('expertsender.service');
    }
    
    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function subscribeAction(Request $request) : JsonResponse
    {
        $rawEmail = $request->request->get('email') ?? '';
        
        /**
         * @todo сгенерировать ссылку для подтверждения email'а?
         */
        
        try {
            $email = !filter_var($rawEmail, FILTER_SANITIZE_EMAIL);
            
            if (!$email) {
                throw new \InvalidArgumentException('Неверный email.');
            }
            
            $this->expertsenderService->simpleSubscribe($email);
        } catch (ExpertsenderServiceException $e) {
            return JsonErrorResponse::create($e->getMessage());
        } catch (\Exception $e) {
            return JsonErrorResponse::create($e->getMessage());
        }
        
        return JsonSuccessResponse::create('Спасибо за подписку на рассылку!<br> Мы отправили вам письмо с дальнейшими инструкциями.');
    }
}
