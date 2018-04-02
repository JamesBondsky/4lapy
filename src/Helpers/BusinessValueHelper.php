<?php

namespace FourPaws\Helpers;

use Bitrix\Main\Entity\Query;
use Bitrix\Sale\Internals\BusinessValueTable;
use Exception;

/**
 * Class BusinessValueHelper
 *
 * @package FourPaws\Helpers
 */
class BusinessValueHelper
{
    protected const DEFAULT_PAYSYSTEM_SETTINGS = [
        'USER_NAME', 'PASSWORD', 'TEST_MODE', 'TWO_STAGE', 'LOGGING'
    ];

    /**
     * @param string $entity
     * @param array $selected
     *
     * @return array
     */
    protected static function getBusinessValue(string $entity, array $selected): array
    {
        try {
            $result = (new Query(BusinessValueTable::getEntity()))
                ->where(
                    Query::filter()->logic('or')
                        ->where('CONSUMER_KEY', $entity)
                        ->where('CONSUMER_KEY', BusinessValueTable::COMMON_CONSUMER_KEY)
                )
                ->whereIn('CODE_KEY', $selected)
                ->where('PROVIDER_KEY', 'VALUE')
                ->setSelect(['*'])
                ->setSelect(['CODE_KEY', 'CONSUMER_KEY', 'PROVIDER_VALUE'])
                ->exec()
                ->fetchAll();
        } catch (Exception $e) {
            /**
             * @todo Log it? Throw it?
             */
            $result = [];
        }

        return self::getEntityValues($entity, $result);
    }

    /**
     * @param int $paySystemId
     * @param array $settings
     *
     * @return array
     */
    public static function getPaysystemSettings(int $paySystemId, array $settings = self::DEFAULT_PAYSYSTEM_SETTINGS): array
    {
        return static::getBusinessValue(\sprintf('PAYSYSTEM_%d', $paySystemId), $settings);
    }

    /**
     * @param string $entity
     * @param array $values
     *
     * @return array
     */
    protected static function getEntityValues(string $entity, array $values): array
    {
        $result = [];

        \usort($values, function ($a, $b) use ($entity) {
            return $a['CONSUMER_KEY'] === $entity ? 1 : -1;
        });

        foreach ($values as $value) {
            $result[$value['CODE_KEY']] = $value['PROVIDER_VALUE'];
        }

        return $result;
    }
}
