<?php

namespace FourPaws\EcommerceBundle\Preset\Bitrix;

use FourPaws\EcommerceBundle\Mapper\ArrayMapperInterface;
use FourPaws\EcommerceBundle\Service\GoogleEcommerceService;

/**
 * Class MapperPreset
 *
 * @package FourPaws\EcommerceBundle\Preset\Bitrix
 */
class MapperPreset
{
    /**
     * @var GoogleEcommerceService
     */
    private $googleEcommerceService;

    /**
     * MapperPreset constructor.
     *
     * @param GoogleEcommerceService $googleEcommerceService
     */
    public function __construct(GoogleEcommerceService $googleEcommerceService)
    {
        $this->googleEcommerceService = $googleEcommerceService;
    }

    /**
     * @return ArrayMapperInterface
     */
    public function mapperSliderFactory(): ArrayMapperInterface
    {
        return $this->googleEcommerceService->getArrayMapper(
            [
                'id' => function ($item, $k) {
                    return $item['CODE'] ?: $item['ID'];
                },
                'name' => 'NAME',
                'creative' => 'NAME',
                'position' => function ($item, $k) {
                    return \sprintf(
                        'slot%d',
                        $k + 1
                    );
                }
            ]
        );
    }
}
