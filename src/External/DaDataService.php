<?php
/*
 * @copyright Copyright (c) ADV/web-engineering co.
 */

namespace FourPaws\External;

use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use Dadata\Client as DaDataClient;
use Dadata\Response\Address as AddressResponse;
use FourPaws\Adapter\DaDataLocationAdapter;
use FourPaws\Adapter\Model\Input\DadataLocation;
use FourPaws\External\Exception\DaDataExecuteException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

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
     * @throws DaDataExecuteException
     * @return DadataLocation
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
                'region' => $region,
                'city_type_full'=>'город',
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
        return $this->getAddresses($params);
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
                'city_type_full'=>'город',
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
        return $this->getAddresses($params);
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
        return $this->getAddresses($params);
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
            if(!empty($capitalCities)) {
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
            if(!empty($capitalCities)) {
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
            if(!empty($capitalCities)) {
                $capitalCity = current($capitalCities);
            }
        }
        return $capitalCity;
    }

    /**
     * @param array $params
     *
     * @return array
     */
    protected function getAddresses(array $params): array
    {
        $suggestions = [];
        $guzzleClient = new Client();
        try {
            $options = [
                'connect_timeout' => 10,
                'timeout'         => 10,
                'debug'           => false,
                'allow_redirects' => false,
                'headers'         => [
                    'Content-Type'  => 'application/json',
                    'Accept'        => 'application/json',
                    'Authorization' => 'Token ' . $this->token,
                    'X-Secret'      => $this->secret,
                ],
                'json'            => $params,
            ];
            $response = $guzzleClient->request('POST',
                'https://suggestions.dadata.ru/suggestions/api/4_1/rs/suggest/address', $options);
            if ($response->getStatusCode() === 200) {
                $suggestions = json_decode($response->getBody()->getContents(), true)['suggestions'];
            }
        } catch (GuzzleException $e) {
            $this->log->error('Ошибка запроса - ' . $e->getMessage());
        }
        return $suggestions;
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
}
