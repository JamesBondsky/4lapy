<?php
namespace FourPaws\External\SmsTraffic\Transport;

use GuzzleHttp\Client as HttpClient;

/**
 * Class GuzzleHttpTransport
 */
class GuzzleHttpTransport implements TransportInterface
{
    public const REQUEST_TIMEOUT = 120;

    /**
     * Makes post http request
     * @param string $url
     * @param array  $postData
     * @return string
     */
    public function doRequest($url, array $postData) : string
    {
        $client = new HttpClient();
        $response = $client->post($url, ['form_params' => $postData, 'timeout' => self::REQUEST_TIMEOUT]);

        return $response->getBody()->__toString();
    }
}
