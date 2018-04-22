<?php

namespace FourPaws\AppBundle\Service;

use Bitrix\Main\Entity\Base;
use Bitrix\Main\Entity\BooleanField;
use Bitrix\Main\Entity\EnumField;
use Bitrix\Main\Entity\Field;
use Bitrix\Main\Entity\Field\IStorable;
use Bitrix\Main\Entity\ScalarField;

/**
 * Class ToBitrixDataArrayConverter
 *
 * @package FourPaws\AppBundle\Service
 */
class ToBitrixDataArrayConverter
{
    public const YES_NO_ENUM = ['N', 'Y'];

    /**
     * @param array $data
     * @param Base  $entity
     * @param array $allowExtraFields
     *
     * @return array
     */
    public function convert(array $data, Base $entity, array $allowExtraFields = [])
    {
        $writableFields = array_filter($entity->getFields(), function (Field $field) {
            return $field instanceof IStorable && $field instanceof ScalarField;
        });
        $result = array_intersect_key($data, array_flip($allowExtraFields));


        foreach ($writableFields as $field) {
            /**
             * @var ScalarField $field
             */
            if (array_key_exists($field->getName(), $data)) {
                $result[$field->getName()] = $this->map($data[$field->getName()] ?? null, $field);
            }
        }

        return $result;
    }

    /**
     * @param             $value
     * @param ScalarField $field
     *
     * @return callable|mixed|null|string
     */
    protected function map($value, ScalarField $field)
    {
        if ($value === null) {
            $value = $field->getDefaultValue();
        }

        if (\is_bool($value)) {
            if ($field instanceof BooleanField) {
                $value = $field->normalizeValue($value);
            }
            if ($field instanceof EnumField) {
                if (array_diff($field->getValues(), static::YES_NO_ENUM) === []) {
                    $value = $value ? 'Y' : 'N';
                }
            }
        }
        return $value;
    }
}
