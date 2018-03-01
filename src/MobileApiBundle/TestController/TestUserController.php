<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\MobileApiBundle\TestController;

use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use FourPaws\MobileApiBundle\Services\Fixtures\FixtureService;
use FourPaws\UserBundle\Entity\User;
use FourPaws\UserBundle\Repository\UserRepository;

class TestUserController extends FOSRestController
{
    /**
     * @var FixtureService
     */
    private $fixtureService;
    /**
     * @var UserRepository
     */
    private $userRepository;

    public function __construct(UserRepository $userRepository, FixtureService $fixtureService)
    {
        $this->fixtureService = $fixtureService;
        $this->userRepository = $userRepository;
    }

    /**
     * @Rest\Get("/user/dummy/")
     * @Rest\View(serializerGroups={"dummy"})
     * @throws \RuntimeException
     * @throws \FourPaws\UserBundle\Exception\ValidationException
     * @throws \FourPaws\UserBundle\Exception\BitrixRuntimeException
     * @throws \FourPaws\UserBundle\Exception\InvalidIdentifierException
     * @throws \FourPaws\UserBundle\Exception\ConstraintDefinitionException
     */
    public function createDummyUserAction()
    {
        $users = $this->fixtureService->get(User::class, 10);
        /**
         * @var User $user
         */
        $user = $users[array_rand($users, 1)];
        $user->setActive(true);
        if ($this->userRepository->create($user)) {
            \CUser::SetUserGroup($user->getId(), [6]);
            if ($loadedUser = $this->userRepository->find($user->getId())) {
                $loadedUser->setPassword($user->getPassword());

                return ['status' => true, 'user' => $loadedUser];
            }
        }
        return ['status' => false];
    }

    /**
     * @Rest\Delete("/user/dummy/")
     * @Rest\View()
     * @throws \FourPaws\UserBundle\Exception\InvalidIdentifierException
     * @throws \FourPaws\UserBundle\Exception\ConstraintDefinitionException
     * @throws \FourPaws\UserBundle\Exception\BitrixRuntimeException
     */
    public function deleteDummyUsersAction()
    {
        $users = $this->userRepository->findBy([
            'SECOND_NAME' => 'fixture',
        ]);
        $result = true;
        foreach ($users as $user) {
            $result &= $this->userRepository->delete($user->getId());
        }

        return ['status' => $result, 'count' => \count($users)];
    }

    /**
     * @Rest\Delete("/user/dummy/{id}/")
     * @Rest\View()
     * @param int $id
     *
     * @throws \FourPaws\UserBundle\Exception\InvalidIdentifierException
     * @throws \FourPaws\UserBundle\Exception\ConstraintDefinitionException
     * @throws \FourPaws\UserBundle\Exception\BitrixRuntimeException
     * @return array
     */
    public function deleteDummyUserAction(int $id)
    {
        $users = $this->userRepository->findBy([
            'SECOND_NAME' => 'fixture',
            'ID'          => $id,
        ]);
        $result = true;
        foreach ($users as $user) {
            $result &= $this->userRepository->delete($user->getId());
        }

        return ['status' => $result, 'count' => \count($users)];
    }

    /**
     * @Rest\Delete("/user/{id}/")
     * @Rest\View()
     * @param int $id
     *
     * @throws \FourPaws\UserBundle\Exception\InvalidIdentifierException
     * @throws \FourPaws\UserBundle\Exception\ConstraintDefinitionException
     * @throws \FourPaws\UserBundle\Exception\BitrixRuntimeException
     * @return array
     */
    public function deleteUserAction(int $id)
    {
        $users = $this->userRepository->findBy([
            'ID' => $id,
        ]);
        $result = true;
        foreach ($users as $user) {
            $result &= $this->userRepository->delete($user->getId());
        }

        return ['status' => $result, 'count' => \count($users)];
    }
}
