<?php

namespace FourPaws\SaleBundle\Command;

use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use Exception;
use FourPaws\SaleBundle\Service\BasketRulesService;
use Psr\Log\LoggerAwareInterface;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Exception\LogicException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class DiscountResave
 *
 * @package FourPaws\SaleBundle\Command
 */
class DiscountResave extends Command implements LoggerAwareInterface
{
    use LazyLoggerAwareTrait;

    /**
     * @var BasketRulesService
     */
    private $basketRulesService;

    /**
     * DiscountResave constructor.
     *
     * @param null $name
     * @param BasketRulesService $basketRulesService
     *
     * @throws LogicException
     */
    public function __construct($name = null, BasketRulesService $basketRulesService)
    {
        parent::__construct($name);

        $this->basketRulesService = $basketRulesService;
    }

    /**
     * @throws InvalidArgumentException
     */
    protected function configure(): void
    {
        $this->setName('fourpaws:sale:discount:resave')
            ->setDescription('Resave all basket rules');
    }

    /** @noinspection PhpMissingParentCallCommonInspection
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @throws RuntimeException
     */
    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $this->log()->debug('Discount resave start');

        try {
            $this->basketRulesService->resaveAll();
            $this->log()->debug('Discount resave done');
        } catch (Exception $e) {
            $this->log()->error(\sprintf(
                'Discount resave error: %s',
                $e->getMessage()
            ));
        }
    }
}
