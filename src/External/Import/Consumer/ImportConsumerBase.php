<?php


namespace FourPaws\External\Import\Consumer;


use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use FourPaws\App\Application;
use FourPaws\External\ImportService;
use JMS\Serializer\Serializer;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Bitrix\Highloadblock\DataManager;

abstract class ImportConsumerBase implements ConsumerInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @var Serializer
     */
    protected $serializer;

    /** @var ImportService $importService */
    protected $importService;

    /** @var DataManager */
    protected $personalCouponUsersManager;
    /** @var DataManager */
    protected $personalCouponManager;

    public function __construct(Serializer $serializer, ImportService $importService)
    {
        Application::includeBitrix();

        $this->serializer = $serializer;
        $this->importService = $importService;

        $container = Application::getInstance()->getContainer();
        $this->personalCouponUsersManager = $container->get('bx.hlblock.personalcouponusers');
        $this->personalCouponManager = $container->get('bx.hlblock.personalcoupon');
        $this->setLogger(LoggerFactory::create('ImportConsumerBase', 'import'));
    }

    /**
     * @inheritdoc
     */
    abstract public function execute(AMQPMessage $message);

    /**
     * @return LoggerInterface
     */
    protected function log() : LoggerInterface
    {
        return $this->logger;
    }
}
