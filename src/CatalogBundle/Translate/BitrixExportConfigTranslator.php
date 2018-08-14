<?php

namespace FourPaws\CatalogBundle\Translate;

use CCatalogExport;
use Exception;
use WebArch\BitrixCache\BitrixCache;

/**
 * @todo    автоопределение нужного транслятора
 *
 * Class BitrixExportConfigTranslator
 *
 * @package FourPaws\CatalogBundle\Translate
 */
class BitrixExportConfigTranslator
{
    /** @noinspection ReturnTypeCanBeDeclaredInspection
     *
     * @param $data
     *
     * @return Configuration
     */
    public function translate($data)
    {
        \parse_str($data['SETUP_VARS'], $configurationData);

        return (new Configuration())
            ->setIblockId($configurationData['IBLOCK_ID'])
            ->setSectionIds($configurationData['V'])
            ->setSiteId($configurationData['SITE_ID'])
            ->setServerName($configurationData['SETUP_SERVER_NAME'])
            ->setCompanyName($configurationData['COMPANY_NAME'])
            ->setExportFile($configurationData['SETUP_FILE_NAME'])
            ->setXmlData(\unserialize($configurationData['XML_DATA'], ['allowed_classes' => false]) ?: [])
            ->setIsHttps($configurationData['USE_HTTPS'] === 'Y')
            ->setIsFilterAvailable($configurationData['FILTER_AVAILABLE'] === 'Y')
            ->setIsReferrersDisable($configurationData['DISABLE_REFERERS'] === 'Y')
            ->setIsPermissionsCheck($configurationData['CHECK_PERMISSIONS'] === 'Y')
            ->setMaxExecutionTime($configurationData['MAX_EXECUTION_TIME']);
    }

    /**
     * @param int $profileId
     *
     * @return array
     *
     * @throws Exception
     */
    public function getProfileData(int $profileId): array
    {
        $cache = (new BitrixCache())
            ->withId(\sprintf(
                'export_profile_%d',
                $profileId
            ))
            ->withTime(60 * 60 * 3);

        return $cache->resultOf(function () use ($profileId) {
            return CCatalogExport::GetByID($profileId);
        });
    }
}
