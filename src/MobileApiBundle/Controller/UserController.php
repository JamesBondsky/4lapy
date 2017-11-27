<?php

namespace FourPaws\MobileApiBundle\Controller;

use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;

class UserController extends FOSRestController
{
    /**
     * @Rest\Post(path="/user_login", name="user_login")
     */
    public function userLoginAction()
    {
        //check captcha

        //login

        //register
    }
}
