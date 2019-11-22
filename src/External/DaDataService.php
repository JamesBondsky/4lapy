<?php
/*
 * @copyright Copyright (c) ADV/web-engineering co.
 */

namespace FourPaws\External;

use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use Dadata\Response\Address as AddressResponse;
use FourPaws\Adapter\DaDataLocationAdapter;
use FourPaws\Adapter\Model\Input\DadataLocation;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\External\Dadata\DadataClient;
use FourPaws\External\Exception\DaDataExecuteException;
use GuzzleHttp\Client;

/**
 * Class DaDataService
 *
 * @package FourPaws\External
 */
class DaDataService
{
    /**
     * @var DaDataClient
     */
    protected $client;
    protected $token;
    protected $secret;
    protected $log;

    /**
     * DaDataService constructor.
     *
     * @param string $token
     * @param string $secret
     */
    public function __construct(string $token, string $secret)
    {
        $this->token = $token;
        $this->secret = $secret;
        $this->client = new DaDataClient(new Client(), \compact('token', 'secret'));
        $this->log = LoggerFactory::create('dadata');
    }

    /**
     * @param string $address
     *
     * @throws DaDataExecuteException
     * @return bool
     */
    public function validateAddress(string $address): bool
    {
        $response = $this->cleanAddress($address);

        return $this->isValidAddress(
            (new DaDataLocationAdapter())->convertDataToEntity((array)$response, DadataLocation::class)
        );
    }

    /**
     * @param string $address
     *
     * @return DadataLocation
     * @throws ApplicationCreateException
     * @throws DaDataExecuteException
     */
    public function splitAddress(string $address): DadataLocation
    {
        $response = $this->cleanAddress($address);
        return (new DaDataLocationAdapter())->convertDataToEntity((array)$response, DadataLocation::class);
    }


    /**
     * @param DadataLocation $address
     *
     * @return bool
     */
    public function isValidAddress(DadataLocation $address): bool
    {
        return (int)$address->getQc() === AddressResponse::QC_GEO_EXACT;
    }

    /**
     * @param string $region
     *
     * @return array
     */
    public function getAddressesByRegion(string $region): array
    {
        $params = [
            'query'      => $region . ' ',
            'locations'  => [
                'region'         => $region,
                'city_type_full' => 'город',
            ],
            'count'      => 20,
            'from_bound' => [
                'value' => 'city',
            ],
            'to_bound'   => [
//                'value' => 'settlement',
                'value' => 'city',
            ],
        ];
        return $this->client->getAddresses($params);
    }

    /**
     * @param string $street
     * @param string $city
     * @return array
     */
    public function getStreets(string $street, string $city): array
    {
        $params = [
            'query'      => $street,
            'count'      => 20,
            'locations'  => [
                'city'         => $city,
            ],
            'restrict_value' => false,
            'from_bound'   => [
                'value' => 'street',
            ],
            'to_bound'   => [
                'value' => 'street',
            ],
        ];
        return $this->client->getAddresses($params);
    }

    /**
     * @param        $region
     * @param string $fiasCode
     *
     * @return array
     */
    public function getAddressesByRegionByFias($region, string $fiasCode): array
    {
        $params = [
            'query'      => $region . ' ',
            'locations'  => [
                'region_fias_id' => $fiasCode,
                'city_type_full' => 'город',
            ],
            'count'      => 20,
            'from_bound' => [
                'value' => 'city',
            ],
            'to_bound'   => [
//                'value' => 'settlement',
                'value' => 'city',
            ],
        ];
        return $this->client->getAddresses($params);
    }

    /**
     * @param        $region
     * @param string $fiasCode
     *
     * @return array
     */
    public function getAddressesByDistrictByFias($region, string $fiasCode): array
    {
        $params = [
            'query'      => $region . ' ',
            'locations'  => [
                'area_fias_id' => $fiasCode,
//                'city_type_full'=>'город',
            ],
            'count'      => 20,
            'from_bound' => [
                'value' => 'city',
            ],
            'to_bound'   => [
                'value' => 'settlement',
//                'value' => 'city',
            ],
        ];
        return $this->client->getAddresses($params);
    }

    /**
     * @param string $region
     * @param string $districtFias
     *
     * @return mixed|null
     */
    public function getCenterDistrictByFias(string $region, string $districtFias)
    {
        $suggestions = $this->getAddressesByDistrictByFias($region, $districtFias);
        $capitalCity = null;
        if (\is_array($suggestions) && !empty($suggestions)) {
            $capitalCities = [];
            foreach ($suggestions as $item) {
                $data = $item['data'];
                if ((int)$data['capital_marker'] === 1 || (int)$data['capital_marker'] === 3) {
                    $capitalCities[] = $data;
                }
            }
            if (!empty($capitalCities)) {
                $capitalCity = current($capitalCities);
            }
        }
        return $capitalCity;
    }

    /**
     * @param string $region
     * @param string $regionFias
     *
     * @return mixed|null
     */
    public function getCenterRegionByFias(string $region, string $regionFias)
    {
        $suggestions = $this->getAddressesByRegionByFias($region, $regionFias);
        $capitalCity = null;
        if (\is_array($suggestions) && !empty($suggestions)) {
            $capitalCities = [];
            foreach ($suggestions as $item) {
                $data = $item['data'];
                if ((int)$data['capital_marker'] === 2 || (int)$data['capital_marker'] === 3) {
                    $capitalCities[] = $data;
                }
            }
            if (!empty($capitalCities)) {
                $capitalCity = current($capitalCities);
            }
        }
        return $capitalCity;
    }

    /**
     * @param string $region
     *
     * @return mixed|null
     */
    public function getCenterRegion(string $region)
    {
        $suggestions = $this->getAddressesByRegion($region);
        $capitalCity = null;
        if (\is_array($suggestions) && !empty($suggestions)) {
            $capitalCities = [];
            foreach ($suggestions as $item) {
                $data = $item['data'];
                if ((int)$data['capital_marker'] === 2 && (int)$data['capital_marker'] === 3) {
                    $capitalCities[] = $data;
                }
            }
            if (!empty($capitalCities)) {
                $capitalCity = current($capitalCities);
            }
        }
        return $capitalCity;
    }

    /**
     * @param string $address
     *
     * @throws DaDataExecuteException
     * @return AddressResponse
     */
    protected function cleanAddress(string $address): AddressResponse
    {
        try {
            $response = $this->client->cleanAddress($address);
        } catch (\Exception $e) {
            throw new DaDataExecuteException(\get_class($e) . ': ' . $e->getMessage(), $e->getCode());
        }

        return $response;
    }

    /**
     * @param $query
     * @param $level
     * @param $cityKladrId
     * @param $streetKladrId
     * @return array
     */
    public function getKkmSuggestions($query, $level, $cityKladrId, $streetKladrId): array
    {
        if ($level != 'city') {
            $params = [
                'query'      => $query,
                'from_bound' => [
                    'value' => $level
                ],
                'to_bound'   => [
                    'value' => $level
                ]
            ];
        } else {
            //для городов и деревень
            $params = [
                'query'      => $query,
                'from_bound' => [
                    'value' => $level
                ],
                'to_bound'   => [
                    'value' => 'settlement'
                ]
            ];
        }

        if ($streetKladrId) {
            $fias = array_shift($this->client->getFias(['query'=>$streetKladrId]));
            $params['locations'][] = [
                'city' => $fias['data']['city'] ?? $fias['data']['settlement'],
                'street' => $fias['data']['street']
            ];
            $params['locations'][] = [
                'kladr_id' => $streetKladrId
            ];
        } elseif ($cityKladrId) {
            $params['locations'] = [
                'kladr_id' => $cityKladrId
            ];
        }

        return $this->client->getAddresses($params);
    }
}
