<?php

namespace FourPaws\Migrator\Entity;

class User extends AbstractEntity
{
    public function setDefaults()
    {
        /**
         * Нечего связывать по умолчанию
         */
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
        $user   = new \CUser();
    
        if(!($success = $user->Update($primary, $data))) {
            $this->getLogger()->error("User #{$primary} update error: $user->LAST_ERROR");
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
            $groups = MapTable::getInternalIdListByExternalIdList($groups, $this->entity);
            $user->SetUserGroup($id, $groups);
        } else {
            $this->getLogger()->error("User #{$primary} add error: $user->LAST_ERROR");
        }
        
        return (new Result($id > 0, $id));
    }
}