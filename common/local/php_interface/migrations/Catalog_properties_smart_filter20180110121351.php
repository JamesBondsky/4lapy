<?php

namespace Sprint\Migration;

use Adv\Bitrixtools\Migration\SprintMigrationBase;
use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use FourPaws\Enum\IblockType;
use FourPaws\Enum\IblockCode;
use Sprint\Migration\Helpers\IblockHelper;

class Catalog_properties_smart_filter20180110121351 extends SprintMigrationBase
{
    protected $description = 'Добавление свойств каталога в умный фильтр';

    protected $productPropertyCodes = [
        '_root_'                         => [
            'BRAND',
        ],
        'koshki'                         => [
            'korm2'                           => [
                'FILTERS' => [
                    'PET_AGE',
                    'FEED_SPECIFICATION',
                    'FLAVOUR',
                    'CONSISTENCE',
                    'COUNTRY',
                    // тип упаковки
                ],
            ],
            'lakomstva-vitaminy-dobavki'      => [
                'FILTERS' => [
                    'PET_AGE',
                    'FLAVOUR',
                    'COUNTRY',
                    // тип упаковки
                ],
            ],
            'miski-kormushki-poilki1'         => [
                'FILTERS' => [
                    'MANUFACTURE_MATERIAL',
                    // цвет, объем
                ],
            ],
            'sumki-perenoski2'                => [
                'FILTERS' => [
                    'MANUFACTURE_MATERIAL',
                    'PET_SIZE',
                ],
            ],
            'igrushki3'                       => [
                'FILTERS' => [
                    'MANUFACTURE_MATERIAL',
                ],
            ],
            'shleyki-osheyniki-povodki'       => [
                'FILTERS' => [
                    'PET_SIZE',
                    'MANUFACTURE_MATERIAL',
                    // цвет
                ],
            ],
            'kogtetochki'                     => [
                'FILTERS' => [
                    'MANUFACTURE_MATERIAL',
                ],
            ],
            'kletki-volery-dveri1'            => [
                'FILTERS' => [
                    'MANUFACTURE_MATERIAL',
                    'PET_SIZE',
                ],
            ],
            'zashchita-ot-blokh-i-kleshchey3' => [
                'FILTERS' => [
                    'PHARMA_GROUP',
                    'PET_AGE',
                    'PET_SIZE',
                    'TRADE_NAME',
                    // тип упаковки
                ],
            ],
        ],
        'sobaki'                         => [
            'korm3'                           => [
                'FILTERS' => [
                    'PET_AGE',
                    'FEED_SPECIFICATION',
                    'PET_SIZE',
                    'FLAVOUR',
                    'CONSISTENCE',
                    'COUNTRY',
                    // тип упаковки
                ],
            ],
            'lakomstva-i-vitaminy1'           => [
                'FILTERS' => [
                    'PET_AGE',
                    'PET_SIZE',
                    'FLAVOUR',
                    // тип упаковки
                ],
            ],
            'odezhda-i-obuv'                  => [
                'FILTERS' => [
                    'MANUFACTURE_MATERIAL',
                    'PET_SIZE',
                    'PET_GENDER'
                    // цвет
                ],
            ],
            'namordniki-osheyniki-povodki'    => [
                'FILTERS' => [
                    'MANUFACTURE_MATERIAL',
                    'PET_SIZE',
                    // цвет
                ],
            ],
            'bizhuteriya'                     => [
                'FILTERS' => [
                    'MANUFACTURE_MATERIAL',
                    // цвет
                ],
            ],
            'kletki-volery-dveri'             => [
                'FILTERS' => [
                    'MANUFACTURE_MATERIAL',
                    'PET_SIZE',
                ],
            ],
            'miski-kormushki-poilki'          => [
                'FILTERS' => [
                    'MANUFACTURE_MATERIAL',
                    'PET_SIZE',
                    // цвет, объем
                ],
            ],
            'sumki-perenoski'                 => [
                'FILTERS' => [
                    'MANUFACTURE_MATERIAL',
                    'PET_SIZE',
                ],
            ],
            'igrushki'                        => [
                'FILTERS' => [
                    'MANUFACTURE_MATERIAL',
                    'PET_SIZE',
                ],
            ],
            'shleyki-osheyniki-povodki2'      => [
                'FILTERS' => [
                    'MANUFACTURE_MATERIAL',
                    'PET_SIZE',
                    // цвет
                ],
            ],
            'zashchita-ot-blokh-i-kleshchey2' => [
                'FILTERS' => [
                    'PHARMA_GROUP',
                    'PET_AGE',
                    'PET_SIZE',
                    'TRADE_NAME',
                    // тип упаковки
                ],
            ],
        ],
        'zashchita-ot-blokh-i-kleshchey' => [
            'FILTERS' => [
                'PHARMA_GROUP',
                'PET_AGE',
                'PET_SIZE',
                'TRADE_NAME',
                // тип упаковки
            ],
        ],
        'gryzuny-i-khorki'               => [
            'korm2'                           => [
                'FILTERS' => [
                    'PET_AGE',
                    'FEED_SPECIFICATION',
                    'PET_TYPE',
                    // тип упаковки
                ],
            ],
            'lakomstva-i-vitaminy1'           => [
                'FILTERS' => [
                    'PET_AGE',
                    'PET_TYPE',
                    // тип упаковки
                ],
            ],
            'domiki-lezhaki-gnezda'           => [
                'FILTERS' => [
                    'MANUFACTURE_MATERIAL',
                    'PET_SIZE',
                ],
            ],
            'igrushki2'                       => [
                'FILTERS' => [
                    'MANUFACTURE_MATERIAL',
                    'PET_SIZE',
                ],
            ],
            'kletki'                          => [
                'FILTERS' => [
                    'MANUFACTURE_MATERIAL',
                    'PET_SIZE',
                ],
            ],
            'miski-kormushki-poilki2'         => [
                'FILTERS' => [
                    'MANUFACTURE_MATERIAL',
                    // цвет, объем
                ],
            ],
            'sumki-perenoski2'                => [
                'FILTERS' => [
                    'MANUFACTURE_MATERIAL',
                    'PET_SIZE',
                ],
            ],
            'zashchita-ot-blokh-i-kleshchey2' => [
                'FILTERS' => [
                    'PHARMA_GROUP',
                    'PET_AGE',
                    'PET_SIZE',
                    'TRADE_NAME',
                    // тип упаковки
                ],
            ],
        ],
        'ptitsy'                         => [
            'korm'                            => [
                'FILTERS' => [
                    'PET_AGE',
                    'FEED_SPECIFICATION',
                    'PET_SIZE',
                    // тип упаковки
                ],
            ],
            'lakomstva-i-vitaminy'            => [
                'FILTERS' => [
                    'PET_AGE',
                    // тип упаковки
                ],
            ],
            'igrushki2'                       => [
                'FILTERS' => [
                    'MANUFACTURE_MATERIAL',
                    'PET_SIZE',
                ],
            ],
            'kletki2'                         => [
                'FILTERS' => [
                    'MANUFACTURE_MATERIAL',
                    'PET_SIZE',
                ],
            ],
            'miski-kormushki-poilki1'         => [
                'FILTERS' => [
                    'MANUFACTURE_MATERIAL',
                    // объем
                ],
            ],
            'sumki-perenoski1'                => [
                'FILTERS' => [
                    'MANUFACTURE_MATERIAL',
                    'PET_SIZE',
                ],
            ],
            'zashchita-ot-blokh-i-kleshchey1' => [
                'FILTERS' => [
                    'PHARMA_GROUP',
                    'PET_AGE',
                    'TRADE_NAME',
                    // тип упаковки
                ],
            ],
        ],
        'ryby'                           => [
            'akvariumy'         => [
                'FILTERS' => [
                    'MANUFACTURE_MATERIAL',
                    // объем
                ],
            ],
            'korm-i-podkormka1' => [
                'FILTERS' => [
                    'FEED_SPECIFICATION',
                    'PET_TYPE',
                    // тип упаковки
                ],
            ],
            'dekor'             => [
                'FILTERS' => [
                    'MANUFACTURE_MATERIAL',
                ],
            ],
            'oborudovanie'      => [
                'FILTERS' => [
                    'PURPOSE',
                ],
            ],
        ],
        'reptilii'                       => [
            'terrariumy-i-podstavki' => [
                'FILTERS' => [
                    'MANUFACTURE_MATERIAL',
                    // объем
                ],
            ],
            'korm-i-podkormka'       => [
                'FILTERS' => [
                    'FEED_SPECIFICATION',
                    'PET_TYPE',
                    // тип упаковки
                ],
            ],
            'dekor1'                 => [
                'FILTERS' => [
                    'MANUFACTURE_MATERIAL',
                ],
            ],
            'oborudovanie1'          => [
                'FILTERS' => [
                    'PURPOSE',
                ],
            ],
        ],
        'veterinarnaya-apteka'           => [
            'FILTERS' => [
                'PET_AGE',
                'PET_SIZE',
                'TRADE_NAME',
                // тип упаковки

            ],
        ],
    ];

    protected $offerPropertyCodes = [
        '_root_' => [
            'KIND_OF_PACKING',
            'COLOUR',
            'VOLUME_REFERENCE',
        ],
    ];

    protected $oldProductPropertyCodes = [
        '_root_' => [
            'PET_AGE',
            'PET_SIZE',
            'PET_GENDER',
            'BRAND',
        ],
    ];

    protected $oldOfferPropertyCodes = [];

    /**
     * @var IblockHelper
     */
    protected $iblockHelper;

    protected $propertyIds;

    public function __construct()
    {
        parent::__construct();
        $this->iblockHelper = $this->getHelper()->Iblock();
    }

    public function up()
    {
        $iblockId = IblockUtils::getIblockId(IblockType::CATALOG, IblockCode::PRODUCTS);
        $this->resetSmartFilter($iblockId);
        if (!$this->addPropsToSmartFilter($iblockId, $this->productPropertyCodes)) {
            return false;
        };

        $iblockId = IblockUtils::getIblockId(IblockType::CATALOG, IblockCode::OFFERS);
        $this->resetSmartFilter($iblockId);
        if (!$this->addPropsToSmartFilter($iblockId, $this->offerPropertyCodes)) {
            return false;
        };

        return true;
    }

    public function down()
    {
        $iblockId = IblockUtils::getIblockId(IblockType::CATALOG, IblockCode::PRODUCTS);
        $this->resetSmartFilter($iblockId);
        if (!$this->addPropsToSmartFilter($iblockId, $this->oldProductPropertyCodes)) {
            return false;
        };

        $iblockId = IblockUtils::getIblockId(IblockType::CATALOG, IblockCode::OFFERS);
        $this->resetSmartFilter($iblockId);
        if (!$this->addPropsToSmartFilter($iblockId, $this->oldOfferPropertyCodes)) {
            return false;
        };

        return true;
    }

    protected function resetSmartFilter($iblockId)
    {
        $sectionIds = [0];
        $sections = \CIBlockSection::GetList([], ['IBLOCK_ID' => $iblockId]);
        while ($section = $sections->Fetch()) {
            $sectionIds[] = $section['ID'];
        }

        foreach ($sectionIds as $sectionId) {
            $links = \CIBlockSectionPropertyLink::GetArray($iblockId, $sectionId);
            foreach ($links as $link) {
                if ($link['SMART_FILTER'] !== 'Y') {
                    continue;
                }

                if ($sectionId === 0) {
                    \CIBlockSectionPropertyLink::Set(
                        0,
                        $link['PROPERTY_ID'],
                        ['IBLOCK_ID' => $iblockId, 'SMART_FILTER' => 'N']
                    );
                } elseif ($link['INHERITED'] !== 'Y') {
                    \CIBlockSectionPropertyLink::Delete($sectionId, $link['PROPERTY_ID']);
                } else {
                    continue;
                }

                $this->log()->info(
                    sprintf(
                        'Свойство c id = %s удалено из умного фильтра для раздела %s инфоблока %s',
                        $link['PROPERTY_ID'],
                        $sectionId,
                        $iblockId
                    )
                );
            }
        }
    }

    protected function addPropsToSmartFilter($iblockId, $propertyData, $iblockSectionId = 0)
    {
        foreach ($propertyData as $code => $data) {
            if (!in_array($code, ['_root_', 'FILTERS'], true)) {
                $section = \CIBlockSection::GetList(
                    [],
                    [
                        'IBLOCK_ID'         => $iblockId,
                        'SECTION_ID' => $iblockSectionId,
                        'CODE'              => $code,
                    ]
                )->Fetch();
                if (!$section) {
                    $this->log()->error('Не найден раздел с кодом ' . $code);

                    return false;
                }

                foreach ($data as $key => $propertyCodes) {
                    if ($key === 'FILTERS') {
                        if (!$this->changeSmartFilterState($iblockId, $section['ID'], $propertyCodes)) {
                            return false;
                        }
                    } else {
                        $childSection = \CIBlockSection::GetList(
                            [],
                            [
                                'IBLOCK_ID'         => $iblockId,
                                'SECTION_ID' => $section['ID'],
                                'CODE'              => $key,
                            ]
                        )->Fetch();
                        if (!$childSection) {
                            $this->log()->error('Не найден раздел с кодом ' . $key);

                            return false;
                        }
                        if (!$this->addPropsToSmartFilter($iblockId, $propertyCodes, $childSection['ID'])) {
                            return false;
                        }
                    }
                }
            } else {
                if (!$this->changeSmartFilterState($iblockId, $iblockSectionId, $data)) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * @param $iblockId
     * @param $sectionId
     * @param $codes
     * @param string $enabled
     *
     * @return bool
     */
    protected function changeSmartFilterState(
        $iblockId,
        $sectionId,
        $codes,
        $enabled = 'Y'
    ) {
        foreach ($codes as $code) {
            if (!isset($this->propertyIds[$code])) {
                if (!$propertyId = $this->iblockHelper->getPropertyId($iblockId, $code)) {
                    $this->log()->error('Не найдено свойство с кодом ' . $code);

                    return false;
                }
                $this->propertyIds[$code] = $propertyId;
            } else {
                $propertyId = $this->propertyIds[$code];
            }

            \CIBlockSectionPropertyLink::Set(
                $sectionId,
                $propertyId,
                $arLink = [
                    'IBLOCK_ID'    => $iblockId,
                    'SMART_FILTER' => $enabled === 'Y' ? 'Y' : 'N',
                ]
            );
            if ($enabled === 'Y') {
                $this->log()->info(
                    sprintf(
                        'Свойство %s добавлено в умный фильтр для раздела %s инфоблока %s',
                        $code,
                        $sectionId,
                        $iblockId
                    )
                );
            } else {
                $this->log()->info(
                    sprintf(
                        'Свойство %s удалено из умного фильтра для раздела %s инфоблока %s',
                        $code,
                        $sectionId,
                        $iblockId
                    )
                );
            }
        }

        return true;
    }
}
