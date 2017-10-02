<?php

namespace FourPaws\Migrator\Converter;

/**
 * Class StringToYesNo
 *
 * Преобразует битриксовый Y/N (или произвольный) в 1/0 для YesNoProperty
 * @see \Adv\Bitrixtools\IBlockPropertyType\YesNoPropertyType
 *
 * @package FourPaws\Migrator\Converter
 */
final class StringToYesNo extends AbstractConverter
{
    private $yes = 'Y';

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

        $data[$fieldName] = $data[$fieldName] == $this->yes ? '1' : '0';

        return $data;
    }
    
    /**
     * Устанавливаем собственное значение для "Да"
     *
     * @param $yes
     */
    public function setYes($yes) {
        $this->yes = $yes;
    }
}