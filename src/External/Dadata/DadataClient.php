<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 15.05.18
 * Time: 16:08
 */

namespace FourPaws\External\Dadata;


use Dadata\Client;

class DadataClient extends Client
{
    /**
     * @param array $params
     *
     * @return array
     */
    public function getAddresses(array $params): array
    {
        $suggestions = [];

        $response = $this->query($this->baseSuggestionsUrl . 'suggest/address', $params);

        if (\is_array($response) && 0 < \count($response)) {
            $suggestions = $response;
        }

        return $suggestions;
    }
}