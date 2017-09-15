<?

namespace FourPaws\Migrator\Provider;

use Bitrix\Main\Entity\ScalarField;
use FourPaws\Migrator\Entity\MapTable;
use FourPaws\Migrator\Entity\Result;
use FourPaws\Migrator\Provider\Exceptions\FailResponse;
use Symfony\Component\HttpFoundation\Response;

abstract class ProviderAbstract implements ProviderInterface
{
    protected $entity;
    
    /**
     * @return array
     */
    abstract public function getMap() : array;
    
    /**
     * @return string
     */
    abstract public function getPrimary() : string;
    
    /**
     * @param \Symfony\Component\HttpFoundation\Response $response
     *
     * @throws \Exception
     */
    abstract public function save(Response $response);
    
    /**
     * @param string $entityName
     */
    public function setEntityName(string $entityName)
    {
        $this->entity = $entityName;
    }
    
    /**
     * ProviderAbstract constructor.
     *
     * @param string $entityName
     */
    public function __construct(string $entityName)
    {
        $this->setEntityName($entityName);
    }
    
    /**
     * @param \Symfony\Component\HttpFoundation\Response $response
     *
     * @return mixed
     * @throws \FourPaws\Migrator\Provider\Exceptions\FailResponse
     */
    protected function parseResponse(Response $response)
    {
        if (!$response->isOk()) {
            throw new FailResponse($response->getContent(), $response->getStatusCode());
        }
        
        /**
         * @todo переделать на специально обученные классы
         */
        return json_decode($response->getContent(),
                           JSON_FORCE_OBJECT | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_APOS);
    }
    
    /**
     * @return \Closure
     */
    public function getScalarEntityMapFilter() : \Closure
    {
        return function ($value) {
            $whenArray  = is_array($value) && !$value['expression'] && !$value['reference'];
            $whenObject = $value instanceof ScalarField;
            
            return $whenArray || $whenObject;
        };
    }
    
    /**
     * @param bool $result
     * @param int  $timestamp
     *
     * @return \FourPaws\Migrator\Entity\Result
     */
    public function getItemResultObject(bool $result, int $timestamp = null) : Result
    {
        return new Result($result, $timestamp);
    }
}