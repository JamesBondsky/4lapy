<?php


namespace FourPaws\PersonalBundle\Service;


use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use FourPaws\App\Application;
use FourPaws\External\Manzana\Dto\BalanceRequest;
use FourPaws\External\ManzanaPosService;
use FourPaws\UserBundle\Exception\NotAuthorizedException;
use FourPaws\UserBundle\Service\CurrentUserProviderInterface;
use FourPaws\UserBundle\Service\UserService;
use Psr\Log\LoggerAwareInterface;

class StampService implements LoggerAwareInterface
{
    use LazyLoggerAwareTrait;

    public const MARK_RATE = 400;
    public const MARKS_PER_RATE = 1;

    public const DISCOUNT_LEVELS = [
        1 => [
            'LEVEL' => 1,
            'MARKS_NEEDED' => 7,
            'DISCOUNT' => 10,
        ],
        2 => [
            'LEVEL' => 2,
            'MARKS_NEEDED' => 15,
            'DISCOUNT' => 20,
        ],
        3 => [
            'LEVEL' => 3,
            'MARKS_NEEDED' => 25,
            'DISCOUNT' => 30,
        ],
    ];
    /**
     * @var UserService
     */
    protected $currentUserProvider;
    /**
     * @var ManzanaPosService
     */
    protected $manzanaPosService;
    /**
     * @var int
     */
    protected $activeStampsCount;

    public function __construct()
    {
        $container = Application::getInstance()->getContainer();
        $this->currentUserProvider = $container->get(CurrentUserProviderInterface::class);
        $this->manzanaPosService = Application::getInstance()->getContainer()->get('manzana.pos.service');
    }


    /**
     * @param bool|null $withoutCache
     * @return int
     * @throws \FourPaws\External\Manzana\Exception\ExecuteErrorException
     * @throws \FourPaws\External\Manzana\Exception\ExecuteException
     * @throws NotAuthorizedException
     */
    public function getActiveStampsCount(?bool $withoutCache = false): int //TODO answer with this value in new API method
    {
        if (!$this->activeStampsCount || $withoutCache) {
            $discountCardNumber = $this->currentUserProvider->getCurrentUser()->getDiscountCardNumber();

            if (!$discountCardNumber) {
                return 0;
            }
            $balanceResponse = $this->manzanaPosService->executeBalanceRequest((new BalanceRequest())->setCardByNumber($discountCardNumber));

            if (!$balanceResponse->isErrorResponse()) {
                $this->activeStampsCount = $balanceResponse->getCardStatusActiveBalance();
            } else {
                $this->log()->error(__METHOD__ . '. Не удалось получить balanceResponse по карте ' . $discountCardNumber . '. Ошибка: ' . $balanceResponse->getMessage());
                $this->activeStampsCount = 0;
            }
        }

        return $this->activeStampsCount;
    }
}