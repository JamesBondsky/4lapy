<?php

namespace FourPaws\User\Model;

use Bitrix\Main\Entity\AddResult;
use Bitrix\Main\Entity\UpdateResult;

class User
{
    private $fields;
    
    private $userId;
    
    /**
     * User constructor.
     *
     * @param int $userId
     */
    public function __construct(int $userId)
    {
        $this->userId = $userId;
    }

    /**
     * @param $name
     */
    public function __get($name)
    {
        /**
         * @todo get properties
         */
    }
    
    /**
     * @param array $data
     *
     * @return \Bitrix\Main\Entity\AddResult
     */
    public static function add(array $data)
    {
        $result = new AddResult();
        
        $cUser = new \CUser();
        $id    = $cUser->Add($data);
        
        if ($id) {
            $result->setId($id);
        } else {
            $result->addErrors([$cUser->LAST_ERROR]);
        }
        
        return $result;
    }
    
    /**
     * @param mixed $primary
     * @param array $data
     *
     * @return \Bitrix\Main\Entity\UpdateResult
     */
    public static function update($primary, array $data)
    {
        
        $result = new UpdateResult();
        
        $cUser = new \CUser();
        $cUser->Update($primary, $data);
        
        if (!$cUser->Update($primary, $data)) {
            $result->addErrors([$cUser->LAST_ERROR]);
        }
        
        return $result;
    }
}
