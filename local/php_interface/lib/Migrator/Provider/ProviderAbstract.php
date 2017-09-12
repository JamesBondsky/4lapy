<?

namespace FourPaws\Migrator\Provider;

use Symfony\Component\HttpFoundation\Response;

class ProviderAbstract implements ProviderInterface
{
    /**
     * @return array
     */
    public function getMap() : array {
        return [];
    }
    
    /**
     * @param \Symfony\Component\HttpFoundation\Response $response
     *
     * @throws \Exception
     */
    public function save(Response $response) {
        /**
         * @do save
         * @or
         */
        throw new \Exception('@todo log and implement this');
    }
}