<?php

/**
 * @copyright Copyright (c) NotAgency
 */

namespace FourPaws\MobileApiBundle\Services\Api;

class CompatibilityService
{
    private const SUPPORTED = [
        'ios' => [
            '1.4.0',
            '1.3.9',
            '6440',
            '1.3.8',
            '1.3.7',
            '1.3.6',
            '1.3.5',
            '1.3.4',
            '1.3.3',
            '1.3.2',
            '1.3.1',
            '1.3.0',
            '1.2',
            '1.1',
            '1.0'
        ],
        'android' => [
            'beta',
            '2.4.5',
            '2.4.4',
            '2.4.3',
            '2.4.2',
            '2.4.1',
            '2.4',
            '2.3.1',
            '2.3',
            '2.2',
            '2.1',
            '2.0',
            '1.8'
        ]
    ];

    /**
     * @param 'ios'|'android' $osType
     * @param string $buildVersion
     * @return bool
     */
    public function isBlocked($osType, $buildVersion)
    {
        if (!array_key_exists($osType,self::SUPPORTED)) {
            return true;
        } else {
            return !in_array($buildVersion, self::SUPPORTED[$osType]);
        }
    }
}
