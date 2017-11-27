<?php

namespace FourPaws\MobileApiBundle\Controller;

use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use FourPaws\MobileApiBundle\Dto\Error;
use FourPaws\MobileApiBundle\FormType\UserLoginFormType;
use FourPaws\User\Exceptions\WrongPasswordException;
use FourPaws\User\UserService;
use Swagger\Annotations\Parameter;
use Swagger\Annotations\Response;
use Symfony\Component\HttpFoundation\Request;

class UserController extends FOSRestController
{
    /**
     * @var UserService
     */
    private $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * @Rest\Post(path="/user_login", name="user_login")
     * @Parameter(
     *     name="token",
     *     in="query",
     *     type="string",
     *     required=true,
     *     description="identifier token from /start request"
     * )
     * @Response(
     *     response="200"
     * )
     * @Rest\View()
     * @param Request $request
     * @throws \Symfony\Component\Form\Exception\AlreadySubmittedException
     * @throws \FourPaws\User\Exceptions\TooManyUserFoundException
     * @return \FourPaws\MobileApiBundle\Dto\Response
     */
    public function userLoginAction(Request $request)
    {
        $result = new \FourPaws\MobileApiBundle\Dto\Response();

        $form = $this->createForm(UserLoginFormType::class, null, ['csrf_protection' => false]);
        $form->submit($request->request->all());
        if ($form->isValid()) {
            $data = $form->getData();
            if ($this->userService->isExist($data['login'])) {
                try {
                    $this->userService->login($data['login'], $data['password']);
                } catch (WrongPasswordException $exception) {
                    /**
                     * todo change code to constant
                     */
                    $result->addError(new Error(2344, 'Не верный логин или пароль'));
                }
            } else {
                $isRegistred = $this->userService->register([
                    'LOGIN' => $data['login'],
                    'PASSWORD' => $data['password'],
                ]);



                /**
                 * todo update session
                 */
            }
            /**
             * todo login
             */
        }
        /**
         * todo add error result
         */

        return $result;
    }
}
