<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\MobileApiBundle\Controller;

use Doctrine\Common\Collections\Collection;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use FourPaws\MobileApiBundle\Dto\Error;
use FourPaws\MobileApiBundle\Dto\Request\InfoRequest;
use FourPaws\MobileApiBundle\Dto\Response;
use FourPaws\MobileApiBundle\Enum\InfoEnum;
use FourPaws\MobileApiBundle\Services\Api\InfoService;

class InfoController extends FOSRestController
{
    /**
     * @var InfoService
     */
    private $infoService;

    public function __construct(InfoService $infoService)
    {
        $this->infoService = $infoService;
    }

    /**
     * @Rest\Get("/social/")
     */
    public function getSocialsAction()
    {
        $response = new Response();
        $response->setData([
            'vkontakte'     =>
                [
                    'web'     => 'http://vk.com/4lapy_ru',
                    'ios'     => 'vk://vk.com/4lapy_ru',
                    'android' => '',
                ],
            'facebook'      =>
                [
                    'web'     => 'https://www.facebook.com/4laps',
                    'ios'     => 'fb://profile/137001486387927',
                    'android' => '',
                ],
            'instagram'     =>
                [
                    'web'     => 'https://www.instagram.com/4lapy.ru/',
                    'ios'     => 'instagram://user?username=4lapy.ru',
                    'android' => 'link',
                ],
            'odnoklassniki' =>
                [
                    'web'     => 'https://ok.ru/chetyre.lapy',
                    'ios'     => 'odnoklassniki://ok.ru/group/51483118272694',
                    'android' => '',
                ],
        ]);
        return $this->view($response);
    }

    /**
     * Получить статичные разделы
     *
     * @Rest\Get("/info/")
     * @Rest\View()
     *
     * @param InfoRequest        $infoRequest
     * @param Collection|Error[] $apiErrors
     *
     * @throws \Adv\Bitrixtools\Exception\IblockNotFoundException
     * @return Response
     */
    public function getInfoAction(InfoRequest $infoRequest, Collection $apiErrors): Response
    {
        $response = new Response();
        $response->setErrors($apiErrors);
        if ($response->getErrors()->count()) {
            return $response;
        }

        switch ($infoRequest->getType()) {
            case InfoEnum::NEWS:
                $response->setData($this->infoService->getNews($infoRequest->getInfoId()));
                break;
            case InfoEnum::LETTERS:
                $response->setData($this->infoService->getArticles($infoRequest->getInfoId()));
                break;
            case InfoEnum::ACTION:
                $response->setData($this->infoService->getActions($infoRequest->getInfoId()));
                break;
            default:
                $response->addError(new Error(100, 'Test'));
        }

        return $response;
    }
}
