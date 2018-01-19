<?php

namespace FourPaws\SaleBundle\OrderStorage;

use Bitrix\Sale\Fuser;

class Session extends Base
{
    const SESSION_CODE = 'ORDER';

    const FIELDS_SESSION_CODE = 'FIELDS';

    const PROPERTIES_SESSION_CODE = 'PROPERTIES';

    /**
     * @{inheritdoc}
     */
    public static function create(int $fuserId = null)
    {
        if ($fuserId != Fuser::getId()) {
            return false;
        }

        $fields = $_SESSION[static::SESSION_CODE][static::FIELDS_SESSION_CODE] ?? [];
        $properties = $_SESSION[static::SESSION_CODE][static::PROPERTIES_SESSION_CODE] ?? [];

        return static::init($fields, $properties);
    }

    /**
     * @{inheritdoc}
     */
    public static function clear(int $fuserId = null): bool
    {
        if (!static::checkFuserId($fuserId)) {
            return false;
        }

        unset($_SESSION[static::SESSION_CODE]);

        return true;
    }

    /**
     * @{inheritdoc}
     */
    public function save(): bool
    {
        $_SESSION[static::SESSION_CODE][static::FIELDS_SESSION_CODE] = $this->fields->toArray();
        $_SESSION[static::SESSION_CODE][static::PROPERTIES_SESSION_CODE] = $this->properties->toArray();

        return true;
    }

    protected static function checkFuserId($fuserId)
    {
        return !$fuserId || $fuserId == Fuser::getId();
    }
}
