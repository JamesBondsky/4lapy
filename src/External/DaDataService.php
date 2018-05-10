<?php
/*
 * @copyright Copyright (c) ADV/web-engineering co.
 */

namespace FourPaws\External;

use Dadata\Client as DaDataClient;
use Dadata\Response\Address as AddressResponse;
use FourPaws\Adapter\DaDataLocationAdapter;
use FourPaws\Adapter\Model\Input\DadataLocation;
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

    /**
     * DaDataService constructor.
     *
     * @param string $token
     * @param string $secret
     */
    public function __construct(string $token, string $secret)
    {
        $this->client = new DaDataClient(new Client(), \compact('token', 'secret'));
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
