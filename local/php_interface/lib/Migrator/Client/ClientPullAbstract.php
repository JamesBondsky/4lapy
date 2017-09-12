<?

namespace FourPaws\Migrator\Client;

abstract class ClientPullAbstract implements ClientPullInterface
{
    protected $limit;
    
    protected $force;
    
    public abstract function getBaseClientList() : array;
    
    public abstract function getClientList() : array;
    
    /**
     * ClientPullAbstract constructor.
     *
     * @param array $options
     */
    public function __construct(array $options = []) {
        $this->limit = (int)$options['limit'];
        $this->force = (bool)$options['force'];
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
            /** @todo implement exception */
            
            return false;
        }
    }
}