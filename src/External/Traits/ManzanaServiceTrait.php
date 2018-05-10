<?php

namespace FourPaws\External\Traits;

use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use FourPaws\App\Application;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\UserBundle\Repository\UserRepository;
use JMS\Serializer\Serializer;
use Meng\AsyncSoap\SoapClientInterface;
use Psr\Log\LoggerAwareTrait;
use RuntimeException;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

/**
 * Trait ManzanaServiceTrait
 *
 * @package FourPaws\External\Traits
 */
trait ManzanaServiceTrait
{
    use LoggerAwareTrait;

    protected $client;
    protected $serializer;
    protected $parameters;
    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * ManzanaService constructor.
     *
     * @param Serializer $serializer
     * @param SoapClientInterface $client
     * @param array $parameters
     *
     * @throws ApplicationCreateException
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    public function __construct(Serializer $serializer, SoapClientInterface $client, array $parameters)
    {
        $this->serializer = $serializer;
        $this->client = $client;
        $this->parameters = $parameters;

        $this->userRepository = Application::getInstance()->getContainer()->get(UserRepository::class);
    }

    /**
     * @throws RuntimeException
     */
    public function setServiceLogger(): void
    {
        if (!$this->logger) {
            $this->setLogger(LoggerFactory::create('manzana'));
        }
    }
}
