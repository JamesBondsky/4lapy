<?php

namespace FourPaws\AppBundle\Service;

use FourPaws\AppBundle\Entity\UserFieldEnumValue;
use FourPaws\AppBundle\Collection\UserFieldEnumCollection;

class UserFieldEnumService
{
    /** @var array */
    private $enumValueData = [];
    /** @var array */
    private $enumUserFieldValues = [];

    /**
     * @param int $id
     *
     * @return UserFieldEnumValue
     */
    public function getEnumValueEntity(int $id) : UserFieldEnumValue
    {
        $data = $this->getEnumValueData($id);
        if ($data === null) {
            $this->obtainEnumValueData($id);
            $data = $this->getEnumValueData($id);
        }

        return new UserFieldEnumValue($data ?? []);
    }

    /**
     * @param int $userFieldId
     * @return UserFieldEnumCollection
     */
    public function getEnumValueCollection(int $userFieldId) : UserFieldEnumCollection
    {
        $collection = new UserFieldEnumCollection();

        $values = $this->getEnumUserFieldValues($userFieldId);
        if ($values === null) {
            $this->obtainEnumUserFieldValues($userFieldId);
            $values = $this->getEnumUserFieldValues($userFieldId);
        }
        foreach ($values as $id) {
            $collection->set($id, $this->getEnumValueEntity($id));
        }

        return $collection;
    }

    /**
     * @param int $id
     * @return array|null
     */
    protected function getEnumValueData(int $id)
    {
        return $this->enumValueData[$id] ?? null;
    }

    /**
     * @param int $id
     * @param array $data
     */
    private function setEnumValueData(int $id, array $data)
    {
        $this->enumValueData[$id] = $data;
    }

    /**
     * @param int $id
     */
    protected function obtainEnumValueData(int $id)
    {
        $enumItems = $this->findByParams(
            [
                'filter' => [
                    'ID' => $id
                ]
            ]
        );
        $items = [];
        while($item = $enumItems->Fetch()) {
            $items[$item['ID']] = $item;
        }
        $this->setEnumValueData($id, ($items[$id] ?? []));
    }

    /**
     * @param int $userFieldId
     * @return array|null
     */
    protected function getEnumUserFieldValues(int $userFieldId)
    {
        return $this->enumUserFieldValues[$userFieldId] ?? null;
    }

    /**
     * @param int $userFieldId
     * @param array $values
     */
    private function setEnumUserFieldValues(int $userFieldId, array $values)
    {
        $this->enumUserFieldValues[$userFieldId] = $values;
    }

    /**
     * @param int $userFieldId
     */
    protected function obtainEnumUserFieldValues(int $userFieldId)
    {
        $enumItems = $this->findByParams(
            [
                'filter' => [
                    'USER_FIELD_ID' => $userFieldId
                ]
            ]
        );
        $values = [];
        while($item = $enumItems->Fetch()) {
            $values[] = $item['ID'];
            $this->setEnumValueData($item['ID'], $item);
        }
        $this->setEnumUserFieldValues($userFieldId, $values);
    }

    /**
     * @param array $params
     * @return \CAllDBResult
     */
    protected function findByParams(array $params = [])
    {
        $params['order'] = $params['order'] ?? ['SORT' => 'ASC'];
        $params['filter'] = $params['filter'] ?? [];
        // результат выборки кешируется внутри метода
        $enumItems = (new \CUserFieldEnum())->GetList(
            $params['order'],
            $params['filter']
        );

        return $enumItems;
    }
}
