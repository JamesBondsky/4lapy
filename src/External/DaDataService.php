<?php
/*
 * @copyright Copyright (c) ADV/web-engineering co.
 */

namespace FourPaws\External;

use Dadata\Client as DaDataClient;
use Dadata\Response\Address as AddressResponse;
use FourPaws\External\Exception\DaDataExecuteException;
use FourPaws\PersonalBundle\Entity\Address;
use GuzzleHttp\Client;

class DaDataService
{
    /**
     * @var DaDataClient
     */
    protected $client;

    public function __construct(string $token, string $secret)
    {
        $this->client = new DaDataClient(new Client(), ['token' => $token, 'secret' => $secret]);
    }

    /**
     * @param Address $address
     *
     * @return bool
     * @throws DaDataExecuteException
     */
    public function isValidAddress(Address $address): bool
    {
        return $this->cleanAddress($address)->qc === AddressResponse::QC_GEO_EXACT;
    }

    /**
     * @param Address $address
     *
     * @return AddressResponse
     * @throws DaDataExecuteException
     */
    public function cleanAddress(Address $address): AddressResponse
    {
        try {
            $response = $this->client->cleanAddress((string)$address);
        } catch (\Exception $e) {
            throw new DaDataExecuteException($e->getMessage(), $e->getCode());
        }

        return $response;
    }
}
