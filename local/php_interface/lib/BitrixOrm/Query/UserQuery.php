<?php

namespace FourPaws\BitrixOrm\Query;

use FourPaws\BitrixOrm\Collection\CollectionBase;
use FourPaws\BitrixOrm\Collection\UserCollection;

class UserQuery extends QueryBase
{
    const UF_PATTERN = 'UF_';
    
    /**
     * @inheritDoc
     */
    public function exec() : CollectionBase
    {
        return new UserCollection($this->doExec());
    }
    
    /**
     * @param array $select
     *
     * @return array
     */
    public function explodeSelect(array $select) : array
    {
        $result = [];
        
        foreach ($select as $field) {
            if (strpos($field, self::UF_PATTERN) === 0) {
                $result['SELECT'][] = $field;
            } else {
                $result['FIELDS'][] = $field;
            }
        }
        
        return $result;
    }
    
    /**
     * @inheritDoc
     */
    public function doExec() : \CDBResult
    {
        $order      = $this->getOrder();
        $filter     = $this->getFilterWithBase();
        $parameters = $this->explodeSelect($this->getSelectWithBase());
        
        if ($this->nav) {
            $parameters['NAV_PARAMS'] = $this->getNav();
        }
        
        return \CUser::GetList($order, $filter, $parameters);
    }
    
    /**
     * @inheritDoc
     */
    public function getBaseFilter() : array
    {
        return [];
    }
    
    /**
     * @inheritDoc
     */
    public function getBaseSelect() : array
    {
        return [
            'ID',
            'LOGIN',
            'EMAIL',
            'PERSONAL_PHONE',
        ];
    }
}
