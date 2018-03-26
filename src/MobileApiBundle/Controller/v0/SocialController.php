<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\MobileApiBundle\Controller\v0;

use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use FourPaws\MobileApiBundle\Dto\Response;

class SocialController extends FOSRestController
{
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
}
