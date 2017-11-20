<?php

namespace FourPaws\Migrator\Converter;

/**
 * Class Stm
 *
 * Конвертер, специфичный для проекта.
 * Сливает Stm и преобразует его в YesNo.
 * @see \Adv\Bitrixtools\IBlockPropertyType\YesNoPropertyType
 *
 * @package FourPaws\Migrator\Converter
 */
class Stm extends AbstractConverter
{
    const PROPERTY_STM_LIST = [
        'PROPERTY_STM',
        'PROPERTY_STM_OTHER',
        'PROPERTY_STM_S_KORM',
    ];
    
    /**
     * @param array $data
     *
     * @return array
     * @throws \Exception
     */
    public function convert(array $data) : array
    {
        $stm = '0';
        
        foreach ($this::PROPERTY_STM_LIST as $property) {
            if ($data[$property] === 'Y') {
                $stm = '1';
                
                unset($data[$property]);
            }
        }
        
        $data[$this::PROPERTY_STM_LIST[0]] = $stm;
        
        return $data;
    }
}