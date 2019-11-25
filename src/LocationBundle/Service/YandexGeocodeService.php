<?php

namespace FourPaws\LocationBundle\Service;

use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use Exception;
use FourPaws\AppBundle\Exception\InvalidArgumentException;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use WebArch\BitrixCache\BitrixCache;
use function GuzzleHttp\Psr7\build_query;

class YandexGeocodeService
{
    use LazyLoggerAwareTrait;

    public const DEFAULT_COORDS = [0, 0];

    protected const PARAM_API_KEY = '8bb38591-0ddc-44f1-a86c-7e5d50e8cac3';

    protected const YANDEX_GEOCODER_URL = 'https://geocode-maps.yandex.ru/1.x/';

    protected const CACHE_TIME = 2592000;

    public $apiKey;

    /**
     * YandexGeocodeService constructor.
     */
    public function __construct()
    {
        $this->apiKey = self::PARAM_API_KEY;
    }

    /**
     * @param $city
     * @return array
     */
    public function getCityCoords($city): array
    {
        if (!$city || empty($city)) {
            throw new InvalidArgumentException('yandex_geocoder.empty.city');
        }

        $geocode = function () use ($city) {
            $queryParams = [
                'geocode' => $city,
                'apikey' => $this->apiKey,
                'results' => 1,
            ];

            $xml = simplexml_load_string(file_get_contents(self::YANDEX_GEOCODER_URL . '?' . build_query($queryParams)));

            $coords = (string)$xml->GeoObjectCollection->featureMember->GeoObject->Point->pos;

            if (empty($coords)) {
                throw new NotFoundHttpException('yandex_geocoder.return.empty.coords');
            }

            $this->geocodeLog('-', 'yandex.city.geocode', $city);

            return explode(' ', $coords);
        };

        try {
            $coords = (new BitrixCache())
                ->withId('yandex_geocoder' . $city)
                ->withTag('yandex_geocoder:' . $city)
                ->withTime(static::CACHE_TIME)
                ->resultOf($geocode);

            return [(float)$coords[1], (float)$coords[0]];
        } catch (Exception $e) {
            $this->log()->error(sprintf('Yandex geocoder error: city "%s", error "%s"', $city, $e->getMessage()));
            return self::DEFAULT_COORDS;
        }
    }

    /**
     * @param $url
     * @param $endPoint
     * @param $query
     * @throws Exception
     */
    public function geocodeLog($url, $endPoint, $query): void
    {
        $logger = new Logger('geolocation use');
        if (!is_dir($_SERVER['DOCUMENT_ROOT'] . '/local/logs/') && !mkdir($concurrentDirectory = $_SERVER['DOCUMENT_ROOT'] . '/local/logs/', 0775) && !is_dir($concurrentDirectory)) {
            throw new \RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
        }

        $userIp = $_SERVER['REMOTE_ADDR'];

        $logger->pushHandler(new StreamHandler($_SERVER['DOCUMENT_ROOT'] . '/local/logs/useYandexGeolocation-' . date('m.d.Y') . '.log', Logger::NOTICE));

        $this->setLogger($logger);

        $this->log()->notice(sprintf('%s, url: %s, endpoint - %s, query - "%s"', $userIp, $url, $endPoint, $query));
    }
}
