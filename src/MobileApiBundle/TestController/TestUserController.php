<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\MobileApiBundle\TestController;

use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use FourPaws\MobileApiBundle\Services\Fixtures\FixtureService;
use FourPaws\UserBundle\Entity\User;
use FourPaws\UserBundle\Service\UserService;

class TestUserController extends FOSRestController
{
    /**
     * @var UserService
     */
    private $userService;
    /**
     * @var FixtureService
     */
    private $fixtureService;

    public function __construct(UserService $userService, FixtureService $fixtureService)
    {
        $this->userService = $userService;
        $this->fixtureService = $fixtureService;
    }

    /**
     * @Rest\Get("/user/dummy/")
     * @Rest\View()
     */
    public function createDummyUserAction()
    {
        $users = $this->fixtureService->get(User::class, 1);
        /**
         * @var User $fixtureUser
         */
        $fixtureUser = reset($users);
        $user = $this->userService->register($fixtureUser);
        $user->setPassword($fixtureUser->getPassword());
        return $user;
    }

    /**
     * @Rest\Delete("/user/dummy/")
     * @Rest\View()
     */
    public function deleteDummyUsersAction()
    {
        $repository = $this->userService->getUserRepository();
        $users = $repository->findBy([
            'SECOND_NAME' => 'fixture',
        ]);
        $result = true;
        foreach ($users as $user) {
            $result &= $repository->delete($user->getId());
        }

        return ['status' => $result];
    }
}
