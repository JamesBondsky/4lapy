<?php

namespace FourPaws\PersonalBundle\Command;

use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ObjectException;
use Bitrix\Main\SystemException;
use FourPaws\App\Application;
use FourPaws\PersonalBundle\Service\PersonalOffersService;
use FourPaws\UserBundle\Service\UserSearchInterface;
use FourPaws\UserBundle\Service\UserService;
use Psr\Log\LoggerAwareInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class SapCommand
 *
 * @package FourPaws\SapBundle\Command
 */
class GiveCouponsToReturningUsersCommand extends Command implements LoggerAwareInterface
{
    use LazyLoggerAwareTrait;

    /**
     * ImportCommand constructor.
     *
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @throws InvalidArgumentException
     */
    public function configure(): void
    {
        $this->setName('fourpaws:coupons:returning_users:generate')
            ->setDescription('Generate coupons for returning users');
    }


    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return bool
     * @throws ArgumentException
     * @throws ObjectException
     * @throws SystemException
     * @throws \Adv\Bitrixtools\Exception\IblockNotFoundException
     * @throws \Bitrix\Main\LoaderException
     * @throws \FourPaws\PersonalBundle\Exception\CouponNotCreatedException
     * @throws \FourPaws\PersonalBundle\Exception\InvalidArgumentException
     */
    public function execute(InputInterface $input, OutputInterface $output): bool
    {
        $container = Application::getInstance()->getContainer();

        /** @var PersonalOffersService $personalOffersService */
        $personalOffersService = $container->get('personal_offers.service');

        $personalOffer = $personalOffersService->getActiveOffers(['CODE' => $personalOffersService::TIME_PASSED_AFTER_LAST_ORDER_OFFER_CODE]);
        if (!$personalOffer->isEmpty()
            && ($personalOfferId = (int)$personalOffer->first()['ID'])
        ) {
            /** @var UserService $userService */
            $userService = $container->get(UserSearchInterface::class);

            $users = $userService->getUsersWithNoRecentPaidOrders($personalOfferId);

            /** @var int $user */
            $couponsCreated = 0;
            foreach ($users as $user)
            {
                $couponsCreated += $personalOffersService->addUniqueOfferCoupon($user, $personalOffersService::TIME_PASSED_AFTER_LAST_ORDER_OFFER_CODE, '3 month');
                dump('Добавлен купон ' . $couponsCreated);
            }

            $message = 'Добавлено ' . $couponsCreated . ' купонов';

            $this->log()->info(__CLASS__ . '. ' . $message);

            return true;
        }

        $message = 'Купоны не добавлены, т.к. акция неактивна';
        $this->log()->info(__CLASS__ . '. ' . $message);

        return true;
    }
}
