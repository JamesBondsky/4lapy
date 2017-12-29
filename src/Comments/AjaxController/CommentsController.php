<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\Comments\AjaxController;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\LoaderException;
use Bitrix\Main\SystemException;
use CBitrixComponent;
use CCommentsComponent;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\App\Response\JsonErrorResponse;
use FourPaws\App\Response\JsonResponse;
use FourPaws\App\Response\JsonSuccessResponse;
use FourPaws\Comments\Exception\EmptyUserDataComments;
use FourPaws\Comments\Exception\ErrorAddComment;
use FourPaws\Helpers\Exception\WrongPhoneNumberException;
use FourPaws\UserBundle\Exception\WrongEmailException;
use Psr\Cache\InvalidArgumentException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;

/**
 * Class CommentsController
 *
 * @package FourPaws\Comments\AjaxController
 * @Route("/comments")
 */
class CommentsController extends Controller
{
    /**
     * @return JsonResponse
     */
    public function addAction() : JsonResponse
    {
        CBitrixComponent::includeComponentClass('fourpaws:comments');
        
        $json = JsonErrorResponse::create('Произошла ошибка, повторите попытку позже');
        try {
            $res = \CCommentsComponent::addComment();
            if ($res) {
                $json =
                    JsonSuccessResponse::create('Ваш комментарий успешно отправлен, он появится здесь после проверки');
            } else {
                $json = JsonErrorResponse::create('Ошибка добавления комментария');
            }
        } catch (WrongPhoneNumberException $e) {
            $json = JsonErrorResponse::create('Введен некорректный номер телефона');
        } catch (EmptyUserDataComments $e) {
            $json = JsonErrorResponse::create($e->getMessage());
        } catch (ErrorAddComment $e) {
            $json = JsonErrorResponse::create($e->getMessage());
        } catch (WrongEmailException $e) {
            $json = JsonErrorResponse::create($e->getMessage());
        } catch (LoaderException $e) {
        } catch (SystemException $e) {
        } catch (ApplicationCreateException $e) {
        } catch (ServiceCircularReferenceException $e) {
        } catch (\RuntimeException $e) {
        } catch (\LogicException $e) {
        }
        
        return $json;
    }
    
    /**
     * @throws \LogicException
     * @return JsonResponse
     */
    public function nextAction() : JsonResponse
    {
        CBitrixComponent::includeComponentClass('fourpaws:comments');
        
        $json = JsonErrorResponse::create('Произошла ошибка, повторите попытку позже');
        try {
            $items = CCommentsComponent::getNextItems();
            
            return JsonSuccessResponse::createWithData('Элементы подгружены', ['items' => $items]);
        } catch (ArgumentException $e) {
            $json = JsonErrorResponse::create('Ошибка - неверные параметры ' . $e->getMessage());
        } catch (LoaderException $e) {
        } catch (SystemException $e) {
        } catch (ApplicationCreateException $e) {
        } catch (ServiceCircularReferenceException $e) {
        } catch (InvalidArgumentException $e) {
        } catch (\RuntimeException $e) {
        }
        
        return $json;
    }
}
