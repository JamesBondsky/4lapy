<?php

namespace FourPaws\KkmBundle\Service;

use Bitrix\Main\NotSupportedException;
use Bitrix\Main\ObjectNotFoundException;
use Bitrix\Sale\UserMessageException;
use Exception;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\Catalog\Collection\OfferCollection;
use FourPaws\Catalog\Query\OfferQuery;
use FourPaws\DeliveryBundle\Entity\CalculationResult\CalculationResultInterface;
use FourPaws\DeliveryBundle\Exception\NotFoundException;
use FourPaws\DeliveryBundle\Service\DeliveryService;
use FourPaws\StoreBundle\Exception\NotFoundException as StoreBundleNotFoundException;
use SimpleXMLElement;
use Bitrix\Main\SystemException;
use Psr\Log\LoggerAwareInterface;
use Bitrix\Main\ArgumentException;
use FourPaws\External\DaDataService;
use Bitrix\Main\ObjectPropertyException;
use FourPaws\LocationBundle\LocationService;
use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use FourPaws\KkmBundle\Repository\Table\KkmTokenTable;
use FourPaws\External\Dostavista\Exception\KkmException;

/**
 * Class KkmService
 *
 * @package FourPaws\KkmBundle\Service
 */
class KkmService implements LoggerAwareInterface
{
    use LazyLoggerAwareTrait;

    const TOKEN_LENGTH = 16;

    const CHARACTERS = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

    const YANDEX_GEOCODE_URL = 'http://geocode-maps.yandex.ru/1.x/?geocode=';

    const YANDEX_API_KEY = 'ad666cd3-80be-4111-af2d-209dddf2c55e';

    const DELIVERY_CODES = [
        DeliveryService::INNER_DELIVERY_CODE
    ];

    const YANDEX_ADDRESS_COMPONENT = [
        'country'  => 'country',
        'province' => 'region/area',
        'locality' => 'city',
        'street'   => 'street',
        'house'    => 'house'
    ];

    /**
     * @var DaDataService $daDataService
     */
    private $daDataService;

    /**
     * @var LocationService $daDataService
     */
    private $locationService;

    /**
     * @var DeliveryService $deliveryService
     */
    private $deliveryService;

    /**
     * KkmService constructor.
     * @param DaDataService $daDataService
     * @param LocationService $locationService
     */
    public function __construct(DaDataService $daDataService, LocationService $locationService, DeliveryService $deliveryService)
    {
        $this->daDataService = $daDataService;
        $this->locationService = $locationService;
        $this->deliveryService = $deliveryService;
    }

    /**
     * @param string $token
     * @return array
     */
    public function validateToken($token): array
    {
        if (strlen($token) != static::TOKEN_LENGTH) {
            $res = [
                'success' => false,
                'error'   => 'Token Length error!'
            ];
        } else {
            try {
                $dbResult = KkmTokenTable::query()
                    ->setSelect(['*'])
                    ->setFilter(['token' => $token])
                    ->exec();
                $tokensCnt = $dbResult->getSelectedRowsCount();
                switch ($tokensCnt) {
                    case 0:
                        $res = [
                            'success' => false,
                            'error'   => 'Token not Found!'
                        ];
                        break;
                    case 1:
                        $tokenData = $dbResult->fetch();
                        $res = [
                            'success' => true,
                            'id'      => $tokenData['id']
                        ];
                        break;
                    default:
                        $res = [
                            'success' => false,
                            'error'   => 'Multiple token Found!'
                        ];
                }
            } catch (ObjectPropertyException|ArgumentException|SystemException $e) {
                $res = [
                    'success' => false,
                    'error'   => $e->getMessage()
                ];
            }
        }

        if (!$res['success']) {
            $this->log()->error($res['error'], ['token' => $token]);
        }

        return $res;
    }

    /**
     * @return string
     * @throws Exception
     */
    private function generateToken(): string
    {
        $randomString = '';
        for ($i = 0; $i < static::TOKEN_LENGTH; $i++) {
            $randomString .= static::CHARACTERS[rand(0, strlen(static::CHARACTERS) - 1)];
        }
        return $randomString;
    }

    /**
     * @param string $id
     * @return array
     */
    public function updateToken(string $id): array
    {
        try {
            $token = $this->generateToken();
            KkmTokenTable::update(
                $id,
                [
                    'token' => $token
                ]
            );
            $res = [
                'success' => true,
                'token'   => $token
            ];
        } catch (Exception $e) {
            $res = [
                'success' => false,
                'error'   => $e->getMessage()
            ];
        }

        if (!$res['success']) {
            $this->log()->error($res['error']);
        } else {
            $this->log()->notice('kkm change token to ' . $token);
        }

        return $res;
    }

    /**
     * @param string $query
     * @return array
     */
    public function getSuggestions($query): array
    {
        //check text length
        try {
            if (mb_strlen($query) < 5) {
                throw new KkmException('Text is too short', 200);
            }

            $suggestions = $this->daDataService->getKkmSuggestions($query);

            if (count($suggestions) == 0) {
                throw new KkmException('Suggestions not found by query', 200);
            }

            foreach ($suggestions as $key => &$suggestion) {
                if ($suggestion['value'] && $suggestion['data']['city_kladr_id']) {
                    $suggestion = [
                        'address'  => $suggestion['value'],
                        'kladr_id' => $suggestion['data']['city_kladr_id']
                    ];
                } else {
                    unset($suggestions[$key]);
                }
            }

            $res = [
                'success'     => true,
                'text'        => $query,
                'suggestions' => $suggestions
            ];

        } catch (KkmException $e) {
            $res = [
                'success' => false,
                'text'    => $query,
                'error'   => $e->getMessage(),
                'code'    => $e->getCode()
            ];
        }

        if (!$res['success']) {
            $this->log()->error($res['error']);
        }

        return $res;
    }

    /**
     * @param string $query
     * @return array
     */
    public function geocode($query): array
    {
        try {
            if (mb_strlen($query) < 5) {
                throw new KkmException('Text is too short', 200);
            }

            $xmlResponse = simplexml_load_file(static::YANDEX_GEOCODE_URL . urlencode($query) . '&key=' . urlencode(static::YANDEX_API_KEY) . '&results=1');

            if (!$xmlResponse instanceof SimpleXMLElement) {
                throw new KkmException('Yandex api failed', 200);
            }

            $arrayResponse = json_decode(json_encode($xmlResponse), TRUE);
            $found = $arrayResponse['GeoObjectCollection']['metaDataProperty']['GeocoderResponseMetaData']['found'];

            if ($found == 0) {
                throw new KkmException('Object by query not found', 200);
            }

            $geoObject = $arrayResponse['GeoObjectCollection']['featureMember']['GeoObject'];
            $address = $geoObject['metaDataProperty']['GeocoderMetaData']['Address'];
            $addressComponent = $address['Component'];

            $addressRes = [
                'text'        => $address['formatted'],
                'precision'   => $geoObject['metaDataProperty']['GeocoderMetaData']['precision'],
                'postal_code' => $address['postal_code'],
            ];

            foreach ($addressComponent as $component) {
                $kind = static::YANDEX_ADDRESS_COMPONENT[$component['kind']];
                $name = $component['name'];
                if ($kind) {
                    if (strpos($kind, '/') === false) {
                        $addressRes[$kind] = $name;
                    } else {
                        $provinces = explode('/', $kind);
                        if ($addressRes[$provinces[0]] == null) {
                            $addressRes[$provinces[0]] = $name;
                        } else {
                            $addressRes[$provinces[1]] = $name;
                        }
                    }
                }
            }

            $addressRes['pos'] = $geoObject['Point']['pos'];

            $res = [
                'success' => true,
                'text'    => $query,
                'address' => $addressRes
            ];
        } catch (KkmException $e) {
            $res = [
                'success' => false,
                'text'    => $query,
                'error'   => $e->getMessage(),
                'code'    => $e->getCode()
            ];
        }

        if (!$res['success']) {
            $this->log()->error($res['error']);
        }

        return $res;
    }

    /**
     * @param string $kladrId
     * @param array $products
     * @return array
     */
    public function getDeliveryRules(string $kladrId, array $products): array
    {
        try {
            if (!$kladrId) {
                throw new KkmException('kladr_id is too short', 200);
            }

            if (count($products) == 0) {
                throw new KkmException('empty products array', 200);
            }

            try {
                $location = $this->locationService->findLocationByExtService('KLADR', $kladrId);
                if (count($location) == 0) {
                    throw new KkmException('Bitrix location not found by kladr_id', 200);
                }
            } catch (SystemException|ObjectPropertyException|ArgumentException $e) {
                throw new KkmException($e->getMessage(), $e->getCode());
            }
            $location = current($location)['CODE'];

            $quantities = [];
            $offerXmlIds = [];
            foreach ($products as $product) {
                $offerXmlIds[] = $product['uid'];
                $quantities[$product['uid']] = $product['count'];
            }

            /** @var OfferCollection $offers */
            $offers = (new OfferQuery())
                ->withSelect([
                    'ID',
                    'XML_ID',
                ])
                ->withFilter([
                    'XML_ID' => $offerXmlIds
                ])
                ->exec();

            if ($offers->count() == 0) {
                throw new KkmException('Offers not found', 200);
            }

            if ($offers->count() != count($quantities)) {
                throw new KkmException('Some offers not found', 200); //TODO кинуть экспешн что не все офферы найдены и какие имеено
            }

            try {
                $deliveries = $this->deliveryService->getByOfferCollection($offers, $quantities, $location, static::DELIVERY_CODES);
            } catch (
            ArgumentException|NotSupportedException|
            ObjectNotFoundException|UserMessageException|
            SystemException|ApplicationCreateException|
            NotFoundException|StoreBundleNotFoundException $e) {
                throw new KkmException($e->getMessage(), $e->getCode());
            }

            $rc = false;
            $innerDeliveryAvailable = false;
            $deliveryPrice = false;
            $deliveryDate = false;
            /** @var CalculationResultInterface $delivery */
            foreach ($deliveries as $delivery) {
                if ($delivery->getDeliveryCode() == DeliveryService::INNER_DELIVERY_CODE) {
                    $innerDeliveryAvailable = true;
                    $rc = true;
                    $deliveryPrice = $delivery->getDeliveryPrice();
                    $deliveryDate = $delivery->getDeliveryDate()->format('d.m.Y');
                }
            }

            if(!$innerDeliveryAvailable){
                throw new KkmException('Delivery 4lapy is not available', 200);
            }

            $deliveryRules = [
                "rc"      => $rc,
                "courier" => [
                    "price" => $deliveryPrice,
                    "date"  => [$deliveryDate],
                    "time"  => [
                        1,
                        2
                    ]
                ]
            ];

            $res = [
                'success'        => true,
                'delivery_rules' => $deliveryRules
            ];
        } catch (KkmException $e) {
            $res = [
                'success'  => false,
                'kladr_id' => $kladrId,
                'products' => $products,
                'error'    => $e->getMessage(),
                'code'     => $e->getCode()
            ];
        }

        if (!$res['success']) {
            $this->log()->error($res['error']);
        }

        return $res;
    }
}
