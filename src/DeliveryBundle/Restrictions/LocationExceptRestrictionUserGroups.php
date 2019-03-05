<?php

namespace FourPaws\DeliveryBundle\Restrictions;

use Bitrix\Sale\Internals\CollectableEntity;
use Bitrix\Main\Localization\Loc;

class LocationExceptRestrictionUserGroups extends \Bitrix\Sale\Delivery\Restrictions\Base
{
    /**
     * @param $personTypeId
     * @param array $params
     * @param int $deliveryId
     * @return bool
     */
    public static function check($usergroupId, array $params, $deliveryId = 0)
    {
        if (is_array($params) && isset($params['USER_GROUP_ID'])) {
            return sizeof(array_intersect($usergroupId, $params['USER_GROUP_ID'])) > 0;
        }

        return true;
    }

    /**
     * @param CollectableEntity $entity
     * @return int
     */
    public static function extractParams(CollectableEntity $entity)
    {
        global $USER;
        if ($USER->isAuthorized()) {
            $usergroupId = $USER->GetUserGroupArray();
        } else {
            $usergroupId = [2];
        }
        return $usergroupId;
    }

    /**
     * @return mixed
     */
    public static function getClassTitle()
    {
        return 'По группе пользователей';
    }

    /**
     * @return mixed
     */
    public static function getClassDescription()
    {
        return 'Ограничение по группе пользователей';
    }

    /**
     * @param int $deliveryId
     * @return array
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public static function getParamsStructure($deliveryId = 0)
    {
        $personTypeList = [];

        $dbRes = \Bitrix\Main\GroupTable::getList();

        while ($personType = $dbRes->fetch()) {
            $personTypeList[$personType['ID']] = $personType['NAME'] . ' (' . $personType['ID'] . ')';
        }

        return [
            'USER_GROUP_ID' => [
                'TYPE' => 'ENUM',
                'MULTIPLE' => 'Y',
                'LABEL' => Loc::getMessage('SALE_DLVR_RSTR_BY_PERSON_TYPE_NAME'),
                'OPTIONS' => $personTypeList
            ]
        ];
    }

    /**
     * @param $mode
     * @return int
     */
    public static function getSeverity($mode)
    {
        return \Bitrix\Sale\Delivery\Restrictions\Manager::SEVERITY_STRICT;
    }
}
