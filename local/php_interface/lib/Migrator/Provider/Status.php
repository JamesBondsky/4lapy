<?php

namespace FourPaws\Migrator\Provider;

class Status extends Sale
{
    /**
     * @return array
     */
    public function getMap() : array
    {
        return [
            'ID'   => 'ID',
            'SORT' => 'SORT',
            'LANG' => 'LANG',
        ];
    }
    
    public function prepareData(array $data) : array
    {
        $data['LANG'] = [$data['LANG']];
        
        return parent::prepareData($data);
    }
}
