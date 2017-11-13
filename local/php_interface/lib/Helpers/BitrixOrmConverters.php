<?php

namespace FourPaws\Helpers;

use Bitrix\Main\Type\Date;
use Bitrix\Main\Type\DateTime;

class BitrixOrmConverters
{
    /**
     * Конвертер для битровых полей времени
     * @see \Bitrix\Main\DB\Result::addFetchDataModifier
     * @param array $rowData
     */
    public static function phpDateTime(array &$rowData)
    {
        foreach ($rowData as &$fieldValue) {
            if ($fieldValue instanceof Date) {
                $fieldValue = \DateTime::createFromFormat(
                    $fieldValue::getFormat(),
                    $fieldValue->toString(),
                    $fieldValue instanceof DateTime ? $fieldValue->getTimeZone() : null
                );
            }
        }
    }
}
