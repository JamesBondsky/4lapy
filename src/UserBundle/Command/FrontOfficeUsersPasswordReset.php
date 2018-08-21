<?php
/**
 * Created by PhpStorm.
 * Date: 17.08.2018
 * Time: 17:12
 * @author      Makeev Ilya
 * @copyright   ADV/web-engineering co.
 */

namespace FourPaws\UserBundle\Command;

use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ArgumentTypeException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use FourPaws\UserBundle\Entity\User;
use FourPaws\UserBundle\Exception\BitrixRuntimeException;
use FourPaws\UserBundle\Exception\ConstraintDefinitionException;
use FourPaws\UserBundle\Exception\InvalidIdentifierException;
use FourPaws\UserBundle\Exception\NotFoundException;
use FourPaws\UserBundle\Repository\UserRepository;
use FourPaws\UserBundle\Service\UserPasswordService;
use Psr\Log\LoggerAwareInterface;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\LogicException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class FrontOfficeUsersPasswordReset
 * @package FourPaws\UserBundle\Command
 */
class FrontOfficeUsersPasswordReset extends Command implements LoggerAwareInterface
{
    use LazyLoggerAwareTrait;

    /**
     * @var UserPasswordService
     */
    private $userPasswordService;
    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * FrontOfficeUsersPasswordReset constructor.
     *
     * @param UserPasswordService $userPasswordService
     *
     * @param UserRepository $userRepository
     *
     * @throws LogicException
     */
    public function __construct(UserPasswordService $userPasswordService, UserRepository $userRepository)
    {
        $this->userPasswordService = $userPasswordService;
        $this->userRepository = $userRepository;

        parent::__construct();
    }


    protected function configure(): void
    {
        $this->setName('fourpaws:front_office_users:password:reset')
            ->setDescription('Сброс пароля группе FRONT_OFFICE_USERS');
    }

    /** @noinspection PhpMissingParentCallCommonInspection
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @throws RuntimeException
     * @throws InvalidIdentifierException
     * @throws ConstraintDefinitionException
     * @throws ArgumentException
     * @throws ArgumentTypeException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $this->log()->debug('Resetting passwords for members of "FRONT_OFFICE_USERS" group...');
        $users = $this->userRepository->findByGroupCode('FRONT_OFFICE_USERS');
        $cnt = 0;
        /** @var User $user */
        foreach ($users as $user) {
            if ($user->getGroups()->count() === 1) {
                try {
                    $this->userPasswordService->resetPassword($user->getId());
                    ++$cnt;
                } catch (NotFoundException | BitrixRuntimeException $e) {
                    $this->log()->error(\sprintf(
                        'Error: %s',
                        $e->getMessage()
                    ));
                }
            }
        }
        if ($cnt) {
            $this->log()->debug('Successfully reset password for ' . $cnt . ' users.');
        } else {
            $this->log()->debug('There is no one user with only "FRONT_OFFICE_USERS" group.');
        }
    }
}