<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\AppBundle\Validator\Constraints;

use Bitrix\Sale\Location\LocationTable;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class LocationCodeValidator extends ConstraintValidator
{
    /**
     * @var LocationTable
     */
    private $locationTable;

    public function __construct()
    {
        $this->locationTable = new LocationTable();
    }

    /**
     * @param                         $value
     * @param Constraint|LocationCode $constraint
     * @throws UnexpectedTypeException
     */
    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof LocationCode) {
            throw new UnexpectedTypeException($constraint, LocationCode::class);
        }

        if (null === $value || '' === $value) {
            return;
        }


        $result = $this->locationTable::query()
            ->addFilter('=CODE', $value)
            ->addSelect('TYPE_ID')
            ->setCacheTtl($constraint->cacheTtl)
            ->exec();

        $countFound = $result->getSelectedRowsCount();

        if ($countFound === 0) {
            $this->context->buildViolation($constraint->notFoundMessage)
                ->setParameter('{{ location_code }}', $this->formatValue($value))
                ->setInvalidValue($value)
                ->setCode(LocationCode::NOT_FOUND_CODE)
                ->addViolation();

            return;
        }

        if ($countFound > 1) {
            $this->context->buildViolation($constraint->moreThanOneMessage)
                ->setParameter('{{ location_code }}', $this->formatValue($value))
                ->setParameter('{{ found_count }}', $countFound)
                ->setInvalidValue($value)
                ->setCode(LocationCode::MORE_THAN_ONE_CODE)
                ->addViolation();

            return;
        }

        $element = (array)$result->fetch();
        $typeId = (int)($element['TYPE_ID'] ?? 0);

        if (null !== $constraint->minTypeId && $constraint->minTypeId > $typeId) {
            $this->context->buildViolation(
                $constraint->minTypeId === $constraint->maxTypeId ? $constraint->exactMessage : $constraint->minTypeIdMessage
            )
                ->setParameter('{{ location_code }}', $this->formatValue($value))
                ->setParameter('{{ min }}', $constraint->minTypeId)
                ->setParameter('{{ type_id }}', $constraint->minTypeId)
                ->setInvalidValue($value)
                ->setPlural((int)$constraint->minTypeId)
                ->setCode(LocationCode::MIN_TYPE_ID_CODE)
                ->addViolation();
        }

        if (null !== $constraint->maxTypeId && $constraint->maxTypeId < $typeId) {
            $this->context->buildViolation(
                $constraint->minTypeId === $constraint->maxTypeId ? $constraint->exactMessage : $constraint->maxTypeIdMessage
            )
                ->setParameter('{{ location_code }}', $this->formatValue($value))
                ->setParameter('{{ max }}', $constraint->maxTypeId)
                ->setParameter('{{ type_id }}', $constraint->maxTypeId)
                ->setInvalidValue($value)
                ->setPlural((int)$constraint->maxTypeId)
                ->setCode(LocationCode::MAX_TYPE_ID_CODE)
                ->addViolation();
        }
    }
}
