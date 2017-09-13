<?

namespace FourPaws\Migrator\Client;

use Circle\RestClientBundle\Services\RestClient;
use FourPaws\App\Application;
use FourPaws\Migrator\Provider\ProviderInterface;

abstract class ClientAbstract implements ClientInterface
{
    /**
     * @todo move it to settings
     */
    const BASE_PATH   = '/migrate';
    
    const API_PATH    = '';
    
    const ENTITY_NAME = '';
    
    protected $client;
    
    protected $limit;
    
    protected $force;
    
    protected $provider;
    
    /**
     * ClientAbstract constructor.
     *
     * @param \FourPaws\Migrator\Provider\ProviderInterface $provider
     * @param array                                         $options
     */
    public function __construct(ProviderInterface $provider, array $options = []) {
        $this->limit    = (int)$options['limit'];
        $this->force    = (bool)$options['force'];
        $this->provider = $provider;
        
        $this->setClient();
    }
    
    /**
     * @return \FourPaws\Migrator\Provider\ProviderInterface
     */
    public function getProvider() : ProviderInterface {
        return $this->provider;
    }
    
    /**
     * @return bool
     */
    public function save() : bool {
        try {
            $this->getProvider()->save($this->query());

            return true;
        } catch (\Throwable $e) {
            /**
             * @todo log it
             */

            return false;
        }
    }

    /**
     * Set client from DI
     */
    protected function setClient() {
        $this->client = Application()->get('rest.client');
    }
    
    /**
     * @return \Circle\RestClientBundle\Services\RestClient
     */
    protected function getClient() : RestClient {
        return $this->client;
    }
    
    /**
     * @param array $options
     *
     * @return string
     */
    protected function getBaseUrl(array $options = []) : string {
        return $this::BASE_PATH . static::API_PATH . ($options ? '?' . http_build_query($options) : '');
    }
    
    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function query() {
        $client  = $this->getClient();
        $options = ['limit' => $this->limit,];
        
        if (!$this->force) {
            $options['timestamp'] = $this->getLastTimestamp();
        }
        
        return $client->get($this->getBaseUrl($options));
    }
    
    /**
     * @return int
     */
    public function getLastTimestamp() : int {
        /**
         * @todo
         */
        return 0;
    }
}