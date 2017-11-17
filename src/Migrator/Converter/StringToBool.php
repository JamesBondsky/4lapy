<?php

namespace FourPaws\Migrator\Converter;

/**
 * Class StringToBool
 *
 * Преобразует битриксовый Y/N (или произвольный) в bool
 *
 * @see     \Adv\Bitrixtools\IBlockPropertyType\YesNoPropertyType
 *
 * @package FourPaws\Migrator\Converter
 */
final class StringToBool extends AbstractConverter
{
    const YES_TYPE_BITRIX = 'Y';
    
    const YES_TYPE_RU     = 'да';
    
    private $yes = self::YES_TYPE_BITRIX;
    
    /**
     * @param array $data
     *
     * @return array
     * @throws \Exception
     */
    public function convert(array $data) : array
    {
        $fieldName = $this->getFieldName();
        
        if (!$data[$fieldName]) {
            return $data;
        }
        
        $data[$fieldName] = $data[$fieldName] === $this->yes;
        
        return $data;
    }
    
    /**
     * Устанавливаем собственное значение для "Да"
     *
     * @param $yes
     */
    public function setYes($yes)
    {
        $this->yes = $yes;
    }
}
