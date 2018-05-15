<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\AppBundle\Validator\Constraints;

use Doctrine\Common\Annotations\Annotation\Target;
use Symfony\Component\Validator\Constraint;

/**
 * Class LocationCode
 * @package FourPaws\AppBundle\Validator\Constraints
 * @Annotation
 * @Target({"PROPERTY", "METHOD", "ANNOTATION"})
 */
class LocationCode extends Constraint
{
    public const NOT_FOUND_CODE = 'a8f11a9c-e9eb-4e8c-aad4-0c8cbe4d3498';
    public const MORE_THAN_ONE_CODE = '859450ec-e661-4584-b16f-14341088228d';
    public const MIN_TYPE_ID_CODE = '99f6de81-0bfe-4f65-b855-5c0bd1606110';
    public const MAX_TYPE_ID_CODE = '03692b4c-2afe-4705-b3ca-ec811fb01f5b';

    public $moreThanOneMessage = 'Found more than one "{{ found_count }}" location for code "{{ location_code }}"';
    public $notFoundMessage = 'The location code "{{ location_code }}" not found';

    public $minTypeIdMessage = 'The location "{{ location_code }}" type id is less than {{ min }}';
    public $maxTypeIdMessage = 'The location "{{ location_code }}" type id is greater than {{ max }}';
    public $exactMessage = 'The location "{{ location_code }}" should by exactly "{{ type_id }}" type id';

    public $minTypeId;
    public $maxTypeId;

    public $cacheTtl = 86400;

    public function __construct($options = null)
    {
        if (null !== $options && !\is_array($options)) {
            $options = [
                'minTypeId' => $options,
                'maxTypeId' => $options,
            ];
        }

        parent::__construct($options);
    }
}
