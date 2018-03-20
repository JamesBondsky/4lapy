<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\MobileApiBundle\Services\Api;

use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use FourPaws\LocationBundle\Exception\CityNotFoundException;
use FourPaws\LocationBundle\LocationService;
use FourPaws\MobileApiBundle\Dto\Object\City;
use FourPaws\MobileApiBundle\Exception\SystemException;
use Psr\Log\LoggerAwareInterface;

class CityService implements LoggerAwareInterface
{
    use LazyLoggerAwareTrait;

    /**
     * @var LocationService
     */
    private $locationService;

    public function __construct(LocationService $locationService)
    {
        $this->locationService = $locationService;
    }

    /**
     * @param string   $query
     * @param null|int $limit
     * @throws \FourPaws\MobileApiBundle\Exception\SystemException
     * @return City[]|Collection
     * @todo change metro check by metroways
     */
    public function search(string $query, ?int $limit = 50): Collection
    {
        try {
            $locations = $this->locationService->findLocation($query, $limit);
        } catch (CityNotFoundException $e) {
            $locations = [];
        } catch (\Exception $e) {
            $this->log()->error($e->getMessage(), ['query' => $query]);
            throw new SystemException('System Exception');
        }
        return (new ArrayCollection($locations))
            ->filter(function ($data) {
                return
                    $data &&
                    \is_array($data) &&
                    $data['NAME'] &&
                    $data['CODE'];
            })
            ->map(function (array $data) {
                return (new City())
                    ->setId($data['CODE'])
                    ->setTitle($data['NAME'])
                    ->setPath(array_map(function ($item) {
                        return $item['NAME'];
                    }, $data['PATH']))
                    ->setHasMetro($data['CODE'] === LocationService::LOCATION_CODE_MOSCOW);
            });
    }
}
