<?php

namespace FourPaws\UserBundle\AjaxController;

use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use FourPaws\Adapter\DaDataLocationAdapter;
use FourPaws\Adapter\Model\Output\BitrixLocation;
use FourPaws\App\Application;
use FourPaws\App\Response\JsonErrorResponse;
use FourPaws\App\Response\JsonResponse;
use FourPaws\App\Response\JsonSuccessResponse;
use FourPaws\LocationBundle\Exception\CityNotFoundException;
use FourPaws\UserBundle\Service\UserService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class CityController
 * @package FourPaws\UserBundle\Controller
 * @Route("/city")
 */
class CityController extends Controller
{
    use LazyLoggerAwareTrait;

    /**@var UserService */
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
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
        $dadataService = Application::getInstance()->getContainer()->get('dadata.service');
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
                $city,
                200,
                ['reload' => true]
            );
        } catch (CityNotFoundException $e) {
            if($dadata !== null){
                try {
                    if(empty($dadata['area_fias_id'])){
                        throw new CityNotFoundException('населенный пункт не найден');
                    }
                    $code = $dadataService->getCenterDistrictByFias($regionName, $dadata['area_fias_id']);

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
                        $city,
                        200,
                        ['reload' => true]
                    );
                } catch (CityNotFoundException $e) {
                    try {
                        if(empty($dadata['region_fias_id'])){
                            throw new CityNotFoundException('населенный пункт не найден');
                        }
                        $code = $dadataService->getCenterRegionByFias($regionName, $dadata['region_fias_id']);

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
                            $city,
                            200,
                            ['reload' => true]
                        );
                    } catch (CityNotFoundException $e) {
                        $response = JsonErrorResponse::create($e->getMessage());
                    }
                }
            } else{
                try {
                    $code = $dadataService->getCenterRegion($regionName);

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
                        $city,
                        200,
                        ['reload' => true]
                    );
                } catch (CityNotFoundException $e) {
                    $response = JsonErrorResponse::create($e->getMessage());
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
            $response = JsonErrorResponse::create($e->getMessage());
        }

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
