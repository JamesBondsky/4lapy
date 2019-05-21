<?php

namespace FourPaws\KkmBundle\Service;

use Bitrix\Main\NotSupportedException;
use Bitrix\Main\ObjectNotFoundException;
use Bitrix\Sale\UserMessageException;
use Exception;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\Catalog\Collection\OfferCollection;
use FourPaws\Catalog\Model\Offer;
use FourPaws\Catalog\Query\OfferQuery;
use FourPaws\DeliveryBundle\Entity\CalculationResult\CalculationResultInterface;
use FourPaws\DeliveryBundle\Entity\CalculationResult\DeliveryResultInterface;
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
use FourPaws\KkmBundle\Exception\KkmException;

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
        'province' => 'region/district',
        'area'     => 'area',
        'locality' => 'city',
        'street'   => 'street',
        'house'    => 'house'
    ];

    const RESPONSE_STATUSES = [
        'success'        => [
            'code'    => 200,
            'message' => 'Успешно'
        ],
        'success_empty'  => [
            'code'    => 204,
            'message' => 'Успешно, но тело ответа пустое'
        ],
        'syntax_error'   => [
            'code'    => 400,
            'message' => 'В запросе синтаксическая ошибка'
        ],
        'unauthorized'   => [
            'code'    => 401,
            'message' => 'Для доступа к запрашиваемому ресурсу требуется аутентификация'
        ],
        'internal_error' => [
            'code'    => 500,
            'message' => 'Внутренняя ошибка сервера. Обратитесь к администратору сайта'
        ]
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
     * @param DeliveryService $deliveryService
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
                'error'   => static::RESPONSE_STATUSES['syntax_error']['message'] . ': поле токена слишком короткое или пустое!',
                'code'    => static::RESPONSE_STATUSES['syntax_error']['code']
            ];
        } else {
            try {
                $dbResult = KkmTokenTable::query()
                    ->setSelect(['id', 'token', 'store_code'])
                    ->setFilter(['token' => $token])
                    ->exec();
                $tokensCnt = $dbResult->getSelectedRowsCount();
                switch ($tokensCnt) {
                    case 0:
                        $res = [
                            'success' => false,
                            'error'   => static::RESPONSE_STATUSES['unauthorized']['message'] . ': данный токен не найден в системе!',
                            'code'    => static::RESPONSE_STATUSES['unauthorized']['code']
                        ];
                        break;
                    case 1:
                        $tokenData = $dbResult->fetch();
                        $res = [
                            'success'    => true,
                            'id'         => $tokenData['id'],
                            'token'      => $tokenData['token'],
                            'store_code' => $tokenData['store_code']
                        ];
                        break;
                    default:
                        $res = [
                            'success' => false,
                            'error'   => static::RESPONSE_STATUSES['unauthorized']['message'] . ': по данному токену найдено несколько записей в таблице!',
                            'code'    => static::RESPONSE_STATUSES['unauthorized']['code']
                        ];
                }
            } catch (ObjectPropertyException|ArgumentException|SystemException $e) {
                $res = [
                    'success' => false,
                    'error'   => $e->getMessage(),
                    'code'    => $e->getCode()
                ];
            }
        }

        if (!$res['success']) {
            $this->log()->error($res['code'] . ' ' . $res['error'], ['token' => $token]);
        }

        return $res;
    }

    /**
     * @return string
     * @throws Exception
     */
    public function generateToken(): string
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
    /*public function updateToken(string $id): array
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
    }*/

    /**
     * @param string $query
     * @return array
     */
    public function getSuggestions($query): array
    {
        //check text length
        try {
            if (mb_strlen($query) < 5) {
                throw new KkmException(
                    static::RESPONSE_STATUSES['syntax_error']['message'] . ': текст слишком короткий',
                    static::RESPONSE_STATUSES['syntax_error']['code']
                );
            }

            $suggestions = $this->daDataService->getKkmSuggestions($query);

            if (count($suggestions) == 0) {
                throw new KkmException(
                    static::RESPONSE_STATUSES['success_empty']['message'] . ': по вводимым данным подсказки не найдены',
                    static::RESPONSE_STATUSES['success_empty']['code']
                );
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
            $this->log()->error($res['code'] . ' ' . $res['error'], ['query' => $query]);
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
                throw new KkmException(
                    static::RESPONSE_STATUSES['syntax_error']['message'] . ': адрес слишком короткий',
                    static::RESPONSE_STATUSES['syntax_error']['code']
                );
            }

            $xmlResponse = simplexml_load_file(static::YANDEX_GEOCODE_URL . urlencode($query) . '&key=' . urlencode(static::YANDEX_API_KEY) . '&results=1');

            if (!$xmlResponse instanceof SimpleXMLElement) {
                throw new KkmException(
                    static::RESPONSE_STATUSES['internal_error']['message'] . ': ошибка запроса в яндекс АПИ',
                    static::RESPONSE_STATUSES['internal_error']['code']
                );
            }

            $arrayResponse = json_decode(json_encode($xmlResponse), true);
            $found = $arrayResponse['GeoObjectCollection']['metaDataProperty']['GeocoderResponseMetaData']['found'];

            if ($found == 0) {
                throw new KkmException(
                    static::RESPONSE_STATUSES['success_empty']['message'] . ': не найдено совпадений для данного адреса',
                    static::RESPONSE_STATUSES['success_empty']['code']
                );
            }

            $geoObject = $arrayResponse['GeoObjectCollection']['featureMember']['GeoObject'];
            $address = $geoObject['metaDataProperty']['GeocoderMetaData']['Address'];
            $addressComponent = $address['Component'];

            $addressRes = [
                'text'        => $address['formatted'],
                'precision'   => $geoObject['metaDataProperty']['GeocoderMetaData']['precision'],
                'postal_code' => $address['postal_code']
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
            $this->log()->error($res['code'] . ' ' . $res['error'], ['query' => $query]);
        }

        return $res;
    }

    /**
     * @param string $kladrId
     * @param array $products
     * @param $storeCode
     * @return array
     */
    public function getDeliveryRules(string $kladrId, array $products, $storeCode): array
    {
        try {
            if (!$kladrId) {
                throw new KkmException(
                    static::RESPONSE_STATUSES['syntax_error']['message'] . ': кладр ID слишком короткий',
                    static::RESPONSE_STATUSES['syntax_error']['code']
                );
            }

            if (count($products) == 0) {
                throw new KkmException(
                    static::RESPONSE_STATUSES['syntax_error']['message'] . ': товары не переданы',
                    static::RESPONSE_STATUSES['syntax_error']['code']
                );
            }

            try {
                $location = $this->locationService->findLocationByExtService('KLADR', $kladrId);
                if (count($location) == 0) {
                    throw new KkmException(
                        static::RESPONSE_STATUSES['internal_error']['message'] . ': не найдено местоположение Битрикса по кладр ID: ' . $kladrId,
                        static::RESPONSE_STATUSES['internal_error']['code']
                    );
                }
            } catch (SystemException|ObjectPropertyException|ArgumentException $e) {
                throw new KkmException(
                    static::RESPONSE_STATUSES['internal_error']['message'] . $e->getMessage(),
                    static::RESPONSE_STATUSES['internal_error']['code']
                );
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
                throw new KkmException(
                    static::RESPONSE_STATUSES['syntax_error']['message'] . ': не найдены товары',
                    static::RESPONSE_STATUSES['syntax_error']['code']
                );
            }

            if ($offers->count() != count($quantities)) {
                $missedOffers = [];
                foreach ($products as $product) {
                    /** @var OfferCollection $filteredCollection */
                    $filteredCollection = $offers->filter(function ($offer) use ($product) {
                        /** @var Offer $offer */
                        return $offer->getXmlId() == $product['uid'];
                    });
                    if ($filteredCollection->count() == 0) {
                        $missedOffers[] = $product['uid'];
                    }
                    unset($filteredCollection);
                }
                throw new KkmException(
                    static::RESPONSE_STATUSES['syntax_error']['message'] . ': товары ' . implode(', ', $missedOffers) . ' не найдены на сайте',
                    static::RESPONSE_STATUSES['syntax_error']['code']
                );
            }

            try {
                $deliveries = $this->deliveryService->getByOfferCollection($offers, $quantities, $location, static::DELIVERY_CODES);
            } catch (
            ArgumentException|NotSupportedException|
            ObjectNotFoundException|UserMessageException|
            SystemException|ApplicationCreateException|
            NotFoundException|StoreBundleNotFoundException $e) {
                throw new KkmException(
                    static::RESPONSE_STATUSES['internal_error']['message'] . $e->getMessage(),
                    static::RESPONSE_STATUSES['internal_error']['code']
                );
            }

            $rc = false;
            $innerDeliveryAvailable = false;
            $deliveryPrice = false;
            $deliveryDates = [];
            $intervals = [];
            /** @var CalculationResultInterface $delivery */
            foreach ($deliveries as $delivery) {
                if ($delivery->getDeliveryCode() == DeliveryService::INNER_DELIVERY_CODE) {
                    /** @var DeliveryResultInterface $delivery */
                    $innerDeliveryAvailable = true;
                    $rc = true;
                    $deliveryPrice = $delivery->getDeliveryPrice();
                    try {
                        $nextDeliveries = $this->deliveryService->getNextDeliveries($delivery, 10);
                        foreach ($nextDeliveries as $nextDelivery) {
                            $deliveryDates[] = FormatDate('d.m.Y', $nextDelivery->getDeliveryDate()->getTimestamp());
                        }
                    } catch (ArgumentException|ApplicationCreateException|NotFoundException| StoreBundleNotFoundException $e) {
                        throw new KkmException(
                            static::RESPONSE_STATUSES['internal_error']['message'] . $e->getMessage(),
                            static::RESPONSE_STATUSES['internal_error']['code']
                        );
                    }
                    foreach ($delivery->getAvailableIntervals() as $interval) {
                        $intervals[] = str_replace(' ', '', (string)$interval);
                    }
                }
            }

            if (!$innerDeliveryAvailable) {
                throw new KkmException(
                    static::RESPONSE_STATUSES['internal_error']['message'] . ': доставка не разрешена для заданного местоположения',
                    static::RESPONSE_STATUSES['internal_error']['code']
                );
            }

            $deliveryRules = [
                'rc'      => $rc,
                'courier' => [
                    'price' => $deliveryPrice,
                    'date'  => $deliveryDates,
                    'time'  => $intervals
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
