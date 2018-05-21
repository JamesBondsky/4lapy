<?php

namespace FourPaws\Migrator\Entity;

use Bitrix\Main\Application;
use FourPaws\Migrator\Client\UserGroup as UserGroupClient;
use FourPaws\Migrator\Entity\Exceptions\AddException;
use FourPaws\Migrator\Entity\Exceptions\UpdateException;

class User extends AbstractEntity
{
    /**
     * Считаем удалённого со старого сайта администратора нашим админом
     *
     * EXTERNAL -> INTERNAL
     *
     * @return array
     * @throws \Exception
     */
    public function setDefaults() : array
    {
        if ($this->checkEntity()) {
            return [];
        }
        
        /**
         * @todo магию потом в конфигурацию
         */
        $map = [
            529643 => 1,
        ];
        
        foreach ($map as $key => $item) {
            $result = MapTable::addEntity($this->entity, $key, $item);
            
            if (!$result->isSuccess()) {
                throw new \Exception("Error: \n" . implode("\n", $result->getErrorMessages()));
            }
        }
        
        LazyTable::handleLazy($this->entity, array_keys($map));
        
        return $map;
    }
    
    /**
     * !Привязка пользователя к группам не обновляется, только создаётся!
     *
     * @param string $primary
     * @param array  $data
     *
     * @return \FourPaws\Migrator\Entity\UpdateResult
     *
     * @throws \FourPaws\Migrator\Entity\Exceptions\UpdateException
     * @throws \Bitrix\Main\DB\SqlQueryException
     */
    public function updateItem(string $primary, array $data) : UpdateResult
    {
        $user = new \CUser();
        
        if (!$user->Update($primary, $data)) {
            throw new UpdateException("User #{$primary} update error: $user->LAST_ERROR");
        }
        
        $this->setRawPassword($primary, $data['PASSWORD'], $data['CHECKWORD']);
        
        return new UpdateResult(true, $primary);
    }
    
    /**
     * @param string $primary
     * @param array  $data
     *
     * @return \FourPaws\Migrator\Entity\AddResult
     *
     * @throws \FourPaws\Migrator\Entity\Exceptions\AddException
     * @throws \Bitrix\Main\DB\SqlQueryException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Exception
     */
    public function addItem(string $primary, array $data) : AddResult
    {
        $groups = $data['GROUPS'];
        $user   = new \CUser();
        
        $id = $user->Add($data);
        
        if (!$id) {
            throw new AddException("User #{$primary} add error: $user->LAST_ERROR");
        }
        
        $this->setRawPassword($id, $data['PASSWORD'], $data['CHECKWORD']);
        
        MapTable::addEntity($this->entity, $primary, $id);
        
        $this->setInternalKeys(['groups' => $groups], $id, UserGroupClient::ENTITY_NAME);
        
        return new AddResult(true, $id);
    }
    
    /**
     * @param int    $id
     * @param string $password
     * @param string $checkword
     *
     * @throws \Bitrix\Main\DB\SqlQueryException
     */
    public function setRawPassword(int $id, string $password, string $checkword)
    {
        $query = vsprintf('UPDATE b_user SET PASSWORD=\'%2$s\', CHECKWORD=\'%3$s\' WHERE id=\'%1$d\'', func_get_args());
        
        Application::getConnection()->query($query);
    }
    
    /**
     * @param array  $data
     * @param string $internal
     * @param string $entity
     *
     * @throws \Bitrix\Main\ArgumentException
     */
    public function setInternalKeys(array $data, string $internal, string $entity): void
    {
        if ($data['groups']) {
            $groups = MapTable::getInternalIdListByExternalIdList($data['groups'], $entity);
            
            \CUser::SetUserGroup($internal, $groups);
        }
    }
    
    /**
     * @param string $field
     * @param string $primary
     * @param        $value
     *
     * @return \FourPaws\Migrator\Entity\UpdateResult
     * @throws \FourPaws\Migrator\Entity\Exceptions\UpdateException
     */
    public function setFieldValue(string $field, string $primary, $value) : UpdateResult
    {
        $cUser = new \CUser();
        
        if ($cUser->Update($primary, [$field => $value])) {
            return new UpdateResult(true, $primary);
        }
        
        throw new UpdateException("Update field with primary {$primary} error: {$cUser->LAST_ERROR}");
    }
}
