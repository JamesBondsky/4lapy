<?php

namespace FourPaws\UserBundle\AjaxController;

use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use FourPaws\Adapter\DaDataLocationAdapter;
use FourPaws\Adapter\Model\Output\BitrixLocation;
use FourPaws\App\Response\JsonErrorResponse;
use FourPaws\App\Response\JsonResponse;
use FourPaws\App\Response\JsonSuccessResponse;
use FourPaws\External\DaDataService;
use FourPaws\LocationBundle\Exception\CityNotFoundException;
use FourPaws\UserBundle\Service\UserService;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use FourPaws\App\Geo\Geo as SxGeo;

/**
 * Class CityController
 * @package FourPaws\UserBundle\Controller
 * @Route("/city")
 */
class CityController extends Controller
{
    use LazyLoggerAwareTrait;

    const DEFAULT_CITY_NAME = 'Москва';
    const DEFAULT_CITY_CODE = '0000073738';

    /**@var UserService */
    protected $userService;

    /**@var DaDataService */
    protected $daDataService;

    public function __construct(UserService $userService, DaDataService $daDataService)
    {
        $this->userService = $userService;
        $this->daDataService = $daDataService;
    }

    /**
     * @Route("/set/", methods={"POST", "GET"})
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function setAction(Request $request): JsonResponse
    {
        $code = $request->get('code');
        $codeList = json_decode($code, true);
        $dadata = null;
        $dadataLocationAdapter = new DaDataLocationAdapter();
        $dadataNotFound = false;
        if (\is_array($codeList) || \is_array($code)) {
            if(\is_array($codeList) && !empty($codeList)){
                $code = $codeList;
            }
            $dadata = $code;
            /** @var BitrixLocation $bitrixLocation */
            $bitrixLocation = $dadataLocationAdapter->convertFromArray($code);

            $code = $bitrixLocation->getCode();
            $name = $bitrixLocation->getName();
            $regionName = $bitrixLocation->getRegion();
            if(empty($code)){
                $dadataNotFound = true;
            }
        } else {
            $code = $request->request->get('code') ?? '';
            $name = $request->request->get('name') ?? '';
            $regionName = $request->request->get('region_name') ?? '';
        }

        try {
            if($dadata !== null && $dadataNotFound){
                throw new CityNotFoundException('населенный пункт не найден');
            }
            $city = $this->userService->setSelectedCity($code, $name, $regionName);
            if(\is_bool($city)){
                throw new CityNotFoundException('населенный пункт не найден');
            }
            $response = JsonSuccessResponse::createWithData(
                'Условия приобретения товаров будут пересчитаны после изменения выбранного региона',
                $city ?? [],
                200,
                ['reload' => true]
            );
        } catch (CityNotFoundException $e) {
            if($dadata !== null){
                try {
                    if(empty($dadata['area_fias_id'])){
                        throw new CityNotFoundException('населенный пункт не найден');
                    }
                    $code = $this->daDataService->getCenterDistrictByFias($regionName, $dadata['area_fias_id']);

                    if(empty($code)){
                        throw new CityNotFoundException('населенный пункт не найден');
                    }

                    /** @var BitrixLocation $bitrixLocation */
                    $bitrixLocation = $dadataLocationAdapter->convertFromArray($code);
                    $code = $bitrixLocation->getCode();

                    if(empty($code)){
                        throw new CityNotFoundException('населенный пункт не найден');
                    }

                    $city = $this->userService->setSelectedCity($code);
                    if(\is_bool($city)){
                        throw new CityNotFoundException('населенный пункт не найден');
                    }
                    $response = JsonSuccessResponse::createWithData(
                        'Условия приобретения товаров будут пересчитаны после изменения выбранного региона',
                        $city ?? [],
                        200,
                        ['reload' => true]
                    );
                } catch (CityNotFoundException $e) {
                    try {
                        if(empty($dadata['region_fias_id'])){
                            throw new CityNotFoundException('населенный пункт не найден');
                        }
                        $code = $this->daDataService->getCenterRegionByFias($regionName, $dadata['region_fias_id']);

                        if(empty($code)){
                            throw new CityNotFoundException('населенный пункт не найден');
                        }

                        /** @var BitrixLocation $bitrixLocation */
                        $bitrixLocation = $dadataLocationAdapter->convertFromArray($code);
                        $code = $bitrixLocation->getCode();

                        if(empty($code)){
                            throw new CityNotFoundException('населенный пункт не найден');
                        }

                        $city = $this->userService->setSelectedCity($code);
                        if(\is_bool($city)){
                            throw new CityNotFoundException('населенный пункт не найден');
                        }
                        $response = JsonSuccessResponse::createWithData(
                            'Условия приобретения товаров будут пересчитаны после изменения выбранного региона',
                            $city ?? [],
                            200,
                            ['reload' => true]
                        );
                    } catch (CityNotFoundException $e) {
                        $response = JsonErrorResponse::createWithData($e->getMessage());
                    }
                }
            } else{
                try {
                    $code = $this->daDataService->getCenterRegion($regionName);

                    if(empty($code)){
                        throw new CityNotFoundException('населенный пункт не найден');
                    }

                    /** @var BitrixLocation $bitrixLocation */
                    $bitrixLocation = $dadataLocationAdapter->convertFromArray($code);
                    $code = $bitrixLocation->getCode();

                    if(empty($code)){
                        throw new CityNotFoundException('населенный пункт не найден');
                    }

                    $city = $this->userService->setSelectedCity($code);
                    if(\is_bool($city)){
                        throw new CityNotFoundException('населенный пункт не найден');
                    }
                    $response = JsonSuccessResponse::createWithData(
                        'Условия приобретения товаров будут пересчитаны после изменения выбранного региона',
                        $city ?? [],
                        200,
                        ['reload' => true]
                    );
                } catch (CityNotFoundException $e) {
                    $response = JsonErrorResponse::createWithData($e->getMessage());
                }
            }
        } catch (\Exception $e) {
            $this->log()->error(
                sprintf('cannot set user city: %s', $e->getMessage()),
                [
                    'code' => $code,
                    'name' => $name,
                    'regionName' => $regionName
                ]
            );
            $response = JsonErrorResponse::createWithData($e->getMessage());
        }

        return $response;
    }

    /**
     * @Route("/use_yandex_geolocation/", methods={"POST"})
     *
     * @param Request $request
     *
     * @return JsonResponse
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     * @throws \Exception
     */
    public function useGeolocationAction(Request $request): JsonResponse
    {
        $logger = new Logger('geolocation use');
        if (!is_dir($_SERVER['DOCUMENT_ROOT'] . '/local/logs/')) {
            mkdir($_SERVER['DOCUMENT_ROOT'] . '/local/logs/', 0775);
        }
        $logger->pushHandler(new StreamHandler($_SERVER['DOCUMENT_ROOT'] . '/local/logs/useYandexGeolocation-' . date('m.d.Y') . '.log',
            Logger::NOTICE));

        $this->setLogger($logger);

        $this->log()->notice('Использование геолокации пользователем',
            ['user_ip' => $_SERVER['REMOTE_ADDR']]);

        $response = JsonSuccessResponse::createWithData(
            'Запись в лог об использовании геолокации прошла успешно',
            [],
            200,
            ['reload' => true]
        );
        return $response;
    }

    /**
     * @Route("/get_geolocation/", methods={"POST"})
     *
     * @param Request $request
     *
     * @return JsonResponse
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     * @throws \Exception
     */
    public function useGetGeolocationAction(Request $request): JsonResponse
    {
        $sxGeo = new SxGeo();
        $sxGeo->setCityFromSxgeo();
        $cityName = $sxGeo->getCityName();

        if (!$cityName) {
            $cityCode = static::DEFAULT_CITY_CODE;
        } else {
            $dbVars = \CSaleLocation::GetList(
                null,
                [
                    'CITY_NAME' => $cityName
                ],
                false,
                false,
                [
                    'CITY_NAME',
                    'CODE'
                ]
            );

            if ($vars = $dbVars->Fetch()) {
                $cityCode = $vars['CODE'];
            } else {
                $cityCode = static::DEFAULT_CITY_CODE;
            }
        }

        $response = JsonSuccessResponse::createWithData(
            'Местоположение успешно определено',
            [
                'city_name' => $cityName,
                'city_code' => $cityCode
            ],
            200,
            ['reload' => true]
        );

        return $response;
    }

    /**
     * @Route("/get_geolocation_city_code/", methods={"POST"})
     *
     * @param Request $request
     * @return JsonResponse
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     */
    public function getGeolocationCityCode(Request $request): JsonResponse
    {
        $cityName = json_decode($request->getContent(), true)['city_name'];
        if (!$cityName) {
            $cityCode = static::DEFAULT_CITY_CODE;
        } else {
            $dbVars = \CSaleLocation::GetList(
                null,
                [
                    'CITY_NAME' => $cityName
                ],
                false,
                false,
                [
                    'CITY_NAME',
                    'CODE'
                ]
            );
            if ($vars = $dbVars->Fetch()) {
                $cityCode = $vars['CODE'];
            } else {
                $cityCode = static::DEFAULT_CITY_CODE;
            }
        }

        $response = JsonSuccessResponse::createWithData(
            'Местоположение успешно определено',
            [
                'city_code' => $cityCode
            ],
            200,
            ['reload' => true]
        );

        return $response;
    }

    /**
     * @Route("/get/", methods={"GET"})
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function getAction(Request $request): JsonResponse
    {
        try {
            $city = $this->userService->getSelectedCity();
            $response = JsonSuccessResponse::createWithData('', $city);
        } catch (\Exception $e) {
            $this->log()->error(sprintf('cannot get user city: %s', $e->getMessage()));
            $response = JsonErrorResponse::create($e->getMessage());
        }

        return $response;
    }
}
