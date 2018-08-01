<?php

namespace FourPaws\SaleBundle\Command;

use Adv\Bitrixtools\Tools\BitrixUtils;
use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\ArgumentOutOfRangeException;
use Bitrix\Main\NotImplementedException;
use Bitrix\Main\ObjectException;
use Bitrix\Main\ObjectNotFoundException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Bitrix\Sale\Internals\OrderPropsValueTable;
use Bitrix\Sale\Internals\PaymentTable;
use Bitrix\Sale\Order;
use Bitrix\Sale\Payment;
use Exception;
use FourPaws\BitrixOrm\Model\Share;
use FourPaws\BitrixOrm\Query\ShareQuery;
use FourPaws\SaleBundle\EventController\Event;
use FourPaws\SaleBundle\Service\BasketRulesService;
use Psr\Log\LoggerAwareInterface;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Exception\LogicException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class CheckActionActivity
 *
 * @package FourPaws\SaleBundle\Command
 */
class CheckActionActivity extends Command implements LoggerAwareInterface
{
    use LazyLoggerAwareTrait;

    /**
     * CheckActionActivity constructor.
     *
     * @param null $name
     *
     * @throws LogicException
     */
    public function __construct($name = null)
    {
        parent::__construct($name);
    }

    /**
     * @throws InvalidArgumentException
     */
    protected function configure(): void
    {
        $this->setName('fourpaws:sale:action:activity:check')
            ->setDescription('Deactivate actions depending on their activity period');
    }

    /** @noinspection PhpMissingParentCallCommonInspection
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @throws Exception
     * @throws ArgumentException
     * @throws SystemException
     *
     * @global $APPLICATION
     */
    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        global $APPLICATION;
        $date = new \DateTime();
        $actions = (new ShareQuery())
            ->withFilter([
                'ACTIVE' => BitrixUtils::BX_BOOL_TRUE,
                [
                    '<DATE_ACTIVE_TO' => $date->format('d.m.Y H:i:s'),
                ]
            ])
            ->withOrder(['ID' => 'DESC'])
            ->exec();

        /** @var Share $action */
        foreach ($actions as $action) {
            $e = new \CIBlockElement();
            if ($e->Update($action->getId(), ['ACTIVE' => BitrixUtils::BX_BOOL_FALSE])) {
                $this->log()->info(
                    \sprintf('Deactivated action %s (#%s)', $action->getName(), $action->getId())
                );
            } else {
                $message = $APPLICATION->GetException() ? $APPLICATION->GetException()->GetString() : '';
                $this->log()->error(
                    \sprintf(
                        'Failed to deactivate action %s (#%s): %s',
                        $action->getName(),
                        $action->getId(),
                        $message
                    )
                );
            }
        }

        $this->log()->info('Task finished');
    }
}
