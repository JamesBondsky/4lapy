<?

namespace FourPaws\Migrator\Provider;

use Symfony\Component\HttpFoundation\Response;

interface ProviderInterface
{
    /**
     * @return array
     */
    public function getMap() : array;
    
    /**
     * @param \Symfony\Component\HttpFoundation\Response $response
     */
    public function save(Response $response);
}