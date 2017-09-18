<?

namespace FourPaws\Migrator\Client;

use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use Circle\RestClientBundle\Services\RestClient;
use FourPaws\App\Application;
use FourPaws\Migrator\Provider\ProviderInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;

abstract class ClientAbstract implements ClientInterface, LoggerAwareInterface
{
    /**
     * @todo move it to settings
     */
    const BASE_PATH   = 'http://old4lapy.e.adv.ru/migrate';
    
    const API_PATH    = '';
    
    const ENTITY_NAME = '';
    
    /**
     * @var RestClient
     */
    protected $client;
    
    protected $options;
    
    protected $limit;
    
    protected $force;
    
    protected $provider;
    
    protected $logger;
    
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
    public function getLogger()
    {
        return $this->logger;
    }
    
    /**
     * ClientAbstract constructor.
     *
     * @param \FourPaws\Migrator\Provider\ProviderInterface $provider
     * @param array                                         $options
     */
    public function __construct(ProviderInterface $provider, array $options = [])
    {
        $this->limit    = (int)$options['limit'];
        $this->force    = (bool)$options['force'];
        $this->provider = $provider;
        
        $this->setClient();
        $this->setLogger(LoggerFactory::create('migrator_' . static::ENTITY_NAME));
    }
    
    /**
     * @return \FourPaws\Migrator\Provider\ProviderInterface
     */
    public function getProvider() : ProviderInterface
    {
        return $this->provider;
    }
    
    /**
     * @return bool
     */
    public function save() : bool
    {
        try {
            $this->getProvider()->save($this->query());
            
            return true;
        } catch (\Throwable $e) {
            $this->getLogger()->error($e->getMessage(), $e->getTrace());
            
            return false;
        }
    }
    
    /**
     * Set client from DI
     */
    protected function setClient()
    {
        $container = Application::getInstance()->getContainer();
        
        $this->client = $container->get('circle.restclient');
        $options      = $container->getParameter('migrator');
        
        foreach ($options as $key => $value) {
            if (constant($key)) {
                $this->options[constant($key)] = $value;
            }
        }
    }
    
    /**
     * @return \Circle\RestClientBundle\Services\RestClient
     */
    protected function getClient() : RestClient
    {
        return $this->client;
    }
    
    /**
     * @param array $options
     *
     * @return string
     */
    protected function getBaseUrl(array $options = []) : string
    {
        return $this::BASE_PATH . static::API_PATH . ($options ? '?' . http_build_query($options) : '');
    }
    
    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function query()
    {
        $client  = $this->getClient();
        $options = ['limit' => $this->limit,];
        
        if (!$this->force) {
            $options['timestamp'] = $this->getLastTimestamp();
        }

        return $client->get($this->getBaseUrl($options), $this->options);
    }
    
    /**
     * @return int
     */
    public function getLastTimestamp() : int
    {
        /**
         * @todo
         */
        return 0;
    }
}