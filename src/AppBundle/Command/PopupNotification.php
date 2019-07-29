<?php

namespace FourPaws\AppBundle\Command;

use FourPaws\App\Application;
use FourPaws\External\ExpertsenderService;
use FourPaws\PersonalBundle\Service\PersonalOffersService;
use FourPaws\UserBundle\Repository\UserRepository;
use FourPaws\UserBundle\Service\UserSearchInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Bitrix\Highloadblock\DataManager;
use Bitrix\Main\Entity\ReferenceField;
use Bitrix\Main\Entity\Query;
use CUser;

class PopupNotification extends Command
{
    /** @var PersonalOffersService $personalOffersService */
    protected $personalOffersService;
    /** @var DataManager $personalCouponManager */
    protected $personalCouponManager;
    /** @var DataManager */
    protected $personalCouponUsersManager;
    /** @var UserSearchInterface $userService */
    protected $userService;

    protected const TYPE = 'type';

    protected const DEFAULT_TYPE = 'last';

    public function __construct(string $name = null)
    {
        parent::__construct($name);
        $container = Application::getInstance()->getContainer();

        $this->personalOffersService = $container->get('personal_offers.service');
        $this->personalCouponManager = $container->get('bx.hlblock.personalcoupon');
        $this->personalCouponUsersManager = $container->get('bx.hlblock.personalcouponusers');
        $this->userService = $container->get(UserSearchInterface::class);
    }


    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this
            ->setName('fourpaws:popup:notification')
            ->setDescription('Reindex all catalog in Elasticsearch. Also could create index if it doesn\'t exist.')
            ->addOption(
                self::TYPE,
                't',
                InputOption::VALUE_OPTIONAL,
                'Режим работы - рассылка о старте или окончании срока действия. Принимает значения start и last'
            );
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $type = $input->getOption(self::TYPE) ?: static::DEFAULT_TYPE;

        $currentDateTime = new \DateTime();

        if ($type == static::DEFAULT_TYPE) {
            $currentDateTime->modify('+4 day');

            $filter = [
                'DATE_ACTIVE_TO' => $currentDateTime->format('d.m.Y'),
            ];
        } else {
            $filter = [
                'DATE_ACTIVE_FROM' => $currentDateTime->format('d.m.Y'),
            ];
        }
        $offers = $this->personalOffersService->getActiveOffers($filter);

        $usersIds = [];
        $offersIds = [];

        foreach ($offers as $offer) {
            $offersIds[] = $offer['ID'];
        }

        $promoCodeUserLinkId = $this->personalCouponUsersManager::query()
            ->setSelect(['*'])
            ->registerRuntimeField(
                new ReferenceField(
                    'USER_COUPONS', $this->personalCouponManager::getEntity()->getDataClass(),
                    Query\Join::on('this.UF_COUPON', 'ref.ID')
                        ->whereIn('ref.UF_OFFER', $offersIds),
                    ['join_type' => 'INNER']
                )
            )
            ->exec()
            ->fetchAll();

        foreach ($promoCodeUserLinkId as $promoCodeUserLinkIdItem) {
            $usersIds[$promoCodeUserLinkIdItem['UF_USER_ID']][] = $promoCodeUserLinkIdItem;
        }

        $usersIdsReset = [];

        $newValue = implode(' ', array_fill(0, 4, 0));

        foreach ($usersIds as $users) {
            foreach ($users as $userItem) {
                if (!in_array($userItem['UF_USER_ID'], $usersIdsReset)) {
                    $this->userService->setModalsCounters($userItem['UF_USER_ID'], $newValue);
                    $usersIdsReset[] = $userItem['UF_USER_ID'];
                }

                $this->personalCouponUsersManager::update($userItem['ID'], [
                    'UF_SHOWN' => false,
                ]);

                $this->userService->sendNotifications([$userItem['UF_USER_ID']], $userItem['ID'], $type == static::DEFAULT_TYPE ? ExpertsenderService::PERSONAL_OFFER_COUPON_START_SEND_EMAIL : ExpertsenderService::PERSONAL_OFFER_COUPON_END_SEND_EMAIL, $userItem['UF_COUPON'], new \DateTime(), new \DateTime(), true);
            }
        }
    }
}
