<?php

namespace FourPaws\Helpers\Traits;

use Bitrix\Main\Type\Date;
use Bitrix\Main\Type\DateTime;

trait BitrixDateSerialization
{
    /**
     * @param $data
     *
     * @return null
     */
    public function serializeBitrixDate($data)
    {
        if (!($data instanceof Date)) {
            $data = null;
        }
    
        return $data;
    }
    
    /**
     * @param $data
     *
     * @return \Bitrix\Main\Type\DateTime|null
     */
    public function deSerializeBitrixDate($data)
    {
        if (!($data instanceof Date)) {
            if (\strlen($data) > 0) {
                /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
                $data = new DateTime($data, 'd.m.Y');
            } else {
                $data = null;
            }
        }
    
        return $data;
    }
    
    /**
     * @param $data
     *
     * @return null
     */
    public function serializeBitrixDateTime($data)
    {
        if (!($data instanceof DateTime)) {
            $data = null;
        }
    
        return $data;
    }
    
    /**
     * @param $data
     *
     * @return \Bitrix\Main\Type\DateTime|null
     */
    public function deSerializeBitrixDateTime($data)
    {
        if (!($data instanceof DateTime)) {
            if (\strlen($data) > 0) {
                /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
                $data = new DateTime($data, 'd.m.Y');
            } else {
                $data = null;
            }
        }
    
        return $data;
    }
}