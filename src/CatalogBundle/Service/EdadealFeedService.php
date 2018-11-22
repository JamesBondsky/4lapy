<?php

namespace FourPaws\CatalogBundle\Service;

use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use FourPaws\CatalogBundle\Exception\ArgumentException;
use FourPaws\CatalogBundle\Translate\Configuration;
use FourPaws\CatalogBundle\Translate\ConfigurationInterface;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;
use Psr\Log\LoggerAwareInterface;
use RuntimeException;
use Symfony\Component\Filesystem\Exception\IOException;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Class EdadealFeedService
 *
 * @package FourPaws\CatalogBundle\Service
 */
class EdadealFeedService extends FeedService implements LoggerAwareInterface
{
    use LazyLoggerAwareTrait;

    /**
     * EdadealFeedService constructor.
     *
     * @param SerializerInterface $serializer
     * @param Filesystem $filesystem
     */
    public function __construct(SerializerInterface $serializer, Filesystem $filesystem)
    {
        parent::__construct($serializer, $filesystem, '');
    }

    /**
     * @param ConfigurationInterface $configuration
     * @param int $step
     * @param string $stockID
     *
     * If need to continue, return true. Else - false.
     *
     * @return boolean
     *
     * @throws \Adv\Bitrixtools\Exception\IblockNotFoundException
     */
    public function process(ConfigurationInterface $configuration, int $step, string $stockID = null): bool
    {
        $arResult = [
            'catalog' => [],
            'offers' => [],
            'version' => 2
        ];

        /*
         *
         * {
                  "conditions": "Предложения действительны для Москвы, Переславль-Залесского и Костромской области",
                  "date_end": "2017-01-10T23:59:59+03:00",
                  "date_start": "2017-01-01T00:00:00+03:00",
                  "id": "1234",
                  "image": "https://retailer.ru/catalogs/1234.jpg",
                  "is_main": true,
                  "offers": [
                    "11111",
                    "22222",
                    "33333"
                  ],
                  "target_regions": [
                    "Москва",
                    "Ярославская область, Переславль-Залесский",
                    "Костромская область, Островский район, село Адищево"
                  ]
                },
         *
         */


        $time = ConvertTimeStamp(time(), 'FULL');

        $arOrder = [
            'ID' => 'ASC'
        ];
        $arFilter = [
            'IBLOCK_ID' => IblockUtils::getIblockId(IblockType::PUBLICATION, IblockCode::SHARES),
            'ACTIVE' => 'Y',
            '>=DATE_ACTIVE_FROM' => $time,
            '<=DATE_ACTIVE_TO' => $time
        ];

        $arSelect = [
            'ID',
            'IBLOCK_ID',
            'DATE_ACTIVE_FROM',
            'DATE_ACTIVE_TO',
            'PREVIEW_PICTURE'
        ];

        $dbShare = \CIBlockElement::GetList($arOrder, $arFilter, false, false, $arSelect);
        while ($cibeShare = $dbShare->GetNextElement()) {
            $share = $cibeShare->GetFields();
            $share['PROPERTIES'] = $cibeShare->GetProperties();
            $arResult['catalog'][$share['ID']] = [
                'conditions' => 'Условия действия акции',   //-
                'date_end' => $share['DATE_ACTIVE_TO'],     // формат
                'date_start' => $share['DATE_ACTIVE_FROM'], // формат
                'id' => $share['ID'],
                'image' => '', //
                'is_main' => true,
                'offers' => [

                ],
                'target_regions' => '' //нет
            ];

            $arImages[$share['ID']] = $share['PREVIEW_PICTURE'];
        }

        dump($arResult);

        return true;
    }

}
