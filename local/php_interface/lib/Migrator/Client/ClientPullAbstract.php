<?

namespace FourPaws\Migrator\Client;

use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;

/**
 * Class ClientPullAbstract
 *
 * @package FourPaws\Migrator\Client
 */
abstract class ClientPullAbstract implements ClientPullInterface, LoggerAwareInterface
{
    protected $limit = 0;
    
    protected $force = false;

    protected $logger;
    
    /**
     * @return \FourPaws\Migrator\Client\ClientInterface[] array
     */
    public abstract function getBaseClientList() : array;
    
    /**
     * @return \FourPaws\Migrator\Client\ClientInterface[] array
     */
    public abstract function getClientList() : array;
    
    /**
     * ClientPullAbstract constructor.
     *
     * @param array $options
     */
    public function __construct(array $options = []) {
        $this->limit = (int)$options['limit'];
        $this->force = (bool)$options['force'];

        $this->setLogger(LoggerFactory::create('migrator_' . str_replace('\\', '_', static::class)));
    }
    
    /**
     * @return bool
     */
    public function save() : bool {
        try {
            /** @var \FourPaws\Migrator\Client\ClientInterface $client */
            foreach ($this->getBaseClientList() as $client) {
                $client->getProvider()->save($client->query());
            }
            
            foreach ($this->getClientList() as $client) {
                $client->getProvider()->save($client->query());
            }
            
            return true;
        } catch (\Exception $e) {
            $this->getLogger()->error($e->getMessage());

            return false;
        }
    }
    
    /**
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
    
    /**
     * @return LoggerInterface
     */
    public function getLogger() {
        return $this->logger;
    }
}