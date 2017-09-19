<?php

namespace FourPaws\Migrator\Provider;

use Bitrix\Main\UserGroupTable;
use FourPaws\Migrator\Entity\Result;
use Symfony\Component\HttpFoundation\Response;

class UserGroup extends ProviderAbstract
{
    /**
     * @return array
     */
    public function getMap() : array
    {
        $map = array_keys(array_filter(UserGroupTable::getMap(), self::getScalarEntityMapFilter()));
        
        return array_combine($map, $map);
    }
    
    /**
     * @return string
     */
    public function getPrimary() : string
    {
        return 'ID';
    }
    
    /**
     * @param \Symfony\Component\HttpFoundation\Response $response
     */
    public function save(Response $response)
    {
        $lastTimestamp = null;

        foreach ($this->parseResponse($response) as $item) {

            try {
                $result = $this->addOrUpdateItem($item);
    
                if (!$result->getResult()) {
                    /**
                     * @todo придумать сюда exception
                     */
                    throw new \Exception('Something happened with entity' . $this->entity . ' and primary' . $item[$this->getPrimary()]);
                }
            } catch (\Throwable $e) {
                $this->getLogger()->error($e->getMessage(), $e->getTrace());
            }
        }
    }
    
    public function addItem(array $data) : Result
    {
    
    }
    
    public function updateItem(string $primary, array $data) : Result
    {
        //$result = Cfd
    }
}