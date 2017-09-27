<?php

namespace FourPaws\Migrator\Entity;

use \Bitrix\Main\Application;
use \FourPaws\Migrator\Client\UserGroup as UserGroupClient;

class User extends AbstractEntity
{
    /**
     * Считаем удалённого со старого сайта администратора нашим админом
     *
     * EXTERNAL -> INTERNAL
     */
    public function setDefaults()
    {
        if ($this->checkEntity()) {
            return;
        }
        
        $map = [
            529643 => 1,
        ];
        
        foreach ($map as $key => $item) {
            $result = MapTable::addEntity($this->entity, $key, $item);
            
            if (!$result->isSuccess()) {
                throw new \Exception("Error: \n" . implode("\n", $result->getErrorMessages()));
            }
        }
    }
    
    /**
     * !Привязка пользователя к группам не обновляется, только создаётся!
     *
     * @param string $primary
     * @param array  $data
     *
     * @return \FourPaws\Migrator\Entity\Result
     */
    public function updateItem(string $primary, array $data) : Result
    {
        $user = new \CUser();
        
        if (!($success = $user->Update($primary, $data))) {
            $this->getLogger()->error("User #{$primary} update error: $user->LAST_ERROR");
        } else {
            $this->setPassword($primary, $data['PASSWORD'], $data['CHECKWORD']);
        }
        
        return (new Result($success, $primary));
    }
    
    /**
     * @param string $primary
     * @param array  $data
     *
     * @return \FourPaws\Migrator\Entity\Result
     */
    public function addItem(string $primary, array $data) : Result
    {
        $groups = $data['GROUPS'];
        $user   = new \CUser();
        
        $id = $user->Add($data);
        
        if ($id) {
            $this->setPassword($id, $data['PASSWORD'], $data['CHECKWORD']);
            
            MapTable::addEntity($this->entity, $primary, $id);
            
            $this->setInternalKeys(['groups' => $groups], $id, UserGroupClient::ENTITY_NAME);
        } else {
            $this->getLogger()->error("User #{$primary} add error: $user->LAST_ERROR");
        }
        
        return (new Result($id > 0, $id));
    }
    
    /**
     * @param int    $id
     * @param string $password
     * @param string $checkword
     *
     * @return \Bitrix\Main\DB\Result
     */
    public function setPassword(int $id, string $password, string $checkword)
    {
        $query = vsprintf('UPDATE b_user SET PASSWORD=\'%2$s\', CHECKWORD=\'%3$s\' WHERE id=\'%1$d\'', func_get_args());
        
        return Application::getConnection()->query($query);
    }
    
    /**
     * @param array  $data
     * @param string $internal
     * @param string $entity
     */
    public function setInternalKeys(array $data, string $internal, string $entity)
    {
        if ($data['groups']) {
            $groups = MapTable::getInternalIdListByExternalIdList($data['groups'], $entity);
            
            (new \CUser())->SetUserGroup($internal, $groups);
        }
    }
}