<?php
declare(strict_types=1);
/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\LandingBundle\Controller;

use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use Articul\Landing\Orm\GroomingAppsTable;
use Articul\Landing\Orm\GroomingTable;
use Articul\Landing\Orm\TrainingAppsTable;
use Bitrix\Iblock\ElementTable;
use Bitrix\Main\Loader;
use Exception;
use FourPaws\App\Application as App;
use FourPaws\App\Response\JsonErrorResponse;
use FourPaws\App\Response\JsonResponse;
use FourPaws\App\Response\JsonSuccessResponse;
use Psr\Log\LoggerAwareInterface;
use RuntimeException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Articul\Landing\Orm\LectionsTable;
use Articul\Landing\Orm\TrainingsTable;
use Articul\Landing\Orm\LectionAppsTable;
use GuzzleHttp\Client;
use FourPaws\LandingBundle\Service\FlagmanService;

/**
 * Class FlagmanController
 *
 * @package FourPaws\SaleBundle\Controller
 * @Route("/flagman")
 */
class FlagmanController extends Controller implements LoggerAwareInterface
{
    use LazyLoggerAwareTrait;

    private $guzzleClient;
    private $token = 'dsvbgdfFBn5434tyhFfd544gdfbDS4ggdsDSDtf';
    private $url;

    /**
     * FlagmanController constructor.
     *
     */
    public function __construct()
    {
        $this->url          = getenv('VET_CLINIC');
        $this->guzzleClient = new Client();
    }

    /**
     * @Route("/add/", methods={"POST"})
     *
     * @param Request $request
     *
     * @return JsonResponse
     * @throws Exception
     *
     * @throws RuntimeException
     */
    public function addAction(Request $request): JsonResponse
    {
        if (!Loader::includeModule('articul.landing')) {
            return JsonErrorResponse::createWithData('', ['errors' => ['order' => 'Модуль для сохранения заявок не подключен']]);
        }

        // $data = json_decode($request->getContent());

        try {
            $successAdding = LectionAppsTable::add([
                //'UF_USER_ID' => (int) $data->userId,
                'UF_NAME'     => $request->get('name'),
                'UF_PHONE'    => $request->get('phone'),
                'UF_EVENT_ID' => (int)$request->get('eventId'),
                'UF_EMAIL'    => $request->get('email')
            ]);

            if ($successAdding) {
                $sits = LectionsTable::query()
                    ->setSelect(['SITS' => 'UTS.FREE_SITS'])
                    ->setFilter(['=ID' => (int)$request->get('eventId')])
                    ->exec()
                    ->fetch()['SITS'];

                $newSits = (int)$sits - 1;

                //@todo исправить как только реализуют метод update
                \CIBlockElement::SetPropertyValuesEx($request->get('eventId'), 0, ['FREE_SITS' => $newSits]);
            }
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => 0,
                'app'     => 'Ошибка при сохранении заявки',
                'errors'  => ['message' => $e->getMessage()],
            ]);

        }

        $response = new JsonResponse([
            'success' => 1,
            'app'     => 'Заявка успешно сохранена',
            'errors'  => [],
        ]);

        return $response;
    }

    /**
     * @Route("/getschedule/{action}/{id}/", methods={"GET"})
     *
     * @param Request $request
     * @param string  $id
     *
     * @return JsonResponse
     * @throws Exception
     *
     * @throws RuntimeException
     */
    public function getSchedule(Request $request, $action, $id): JsonResponse
    {
        //@todo отрефакторить дублирование и вынести в сервис
        $result = [];
    
        $flagmanService = new FlagmanService();
        $elements       = $flagmanService->getElementsBySectionGroomingId($id);

        foreach ($elements as $key => $element) {
            if ($element['FREE_VALUE'] == 'N') {
                unset($elements[$key]);
                continue;
            }
        
            preg_match('/^[0-9]{2}/', $element['NAME'], $matches);
            if ($matches[0] <= date('h')) {
                unset($elements[$key]);
                continue;
            }
        
            $result[(string) $element['ID']] = $element['NAME'];
        }
        
        if ($result) {
            return new JsonResponse([
                'success' => 1,
                'data'    => $result,
                'errors'  => [],
            ]);
        }
    
        return new JsonResponse([
            'success' => 0,
            'errors'  => ['message' => 'Такого дня нет =('],
        ]);
    }

    /**
     * @Route("/bookthetime/{idType}/", methods={"POST"})
     *
     * @param Request $request
     * @param string  $idType
     *
     * @return JsonResponse
     * @throws Exception
     *
     * @throws RuntimeException
     */
    public function bookTheTime(Request $request, $idType): JsonResponse
    {
        // $flagmanService = new FlagmanService();
        // $bookingResult = $flagmanService->bookTheTime($id);
        //@todo сори за жирный контроллер и дублирование
        if (!Loader::includeModule('articul.landing')) {
            return JsonErrorResponse::createWithData('', ['errors' => ['order' => 'Модуль для сохранения заявок не подключен']]);
        }
    
        try {
            $successAdding = GroomingAppsTable::add([
                'UF_NAME'     => $request->get('name'),
                'UF_PHONE'    => $request->get('phone'),
                'UF_EVENT_ID' => (int)$request->get('id'),
                'UF_EMAIL'    => $request->get('email'),
            ]);
        
            if ($successAdding) {
                \CIBlockElement::SetPropertyValuesEx($request->get('id'), 0, ['FREE' => 'N']);
                \CEvent::Send('GROOMING_SERVICE', 's1', [
                    'NAME'  => $request->get('name'),
                    'PHONE' => $request->get('phone'),
                    'DATE'  => $request->get('date'),
                    'TIME'  => $request->get('time'),
                    'EMAIL' => $request->get('email'),
                ]);
            }
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => 'N',
                'errors'  => ['message' => $e->getMessage()],
            ]);
        
        }
    
        $response = new JsonResponse([
            'success' => 'Y',
            'errors'  => [],
        ]);
    
        return $response;
    }

    /**
     * @Route("/getlocalschedule/{id}/", methods={"GET"})
     *
     * @param Request $request
     * @param string  $id
     *
     * @return JsonResponse
     * @throws Exception
     *
     * @throws RuntimeException
     */
    public function getLocalSchedule(Request $request, $id): JsonResponse
    {
        $result = [];

        $flagmanService = new FlagmanService();
        $elements       = $flagmanService->getElementsBySectionTrainingId($id);

        foreach ($elements as $key => $element) {
            if ($element['FREE_SITS'] <= 0) {
                unset($elements[$key]);
                continue;
            }

            $result[$element['ID']] = $element['NAME'];
    
            // $result[$key] = [
            //     'timeId' => $element['ID'],
            //     'time' => $element['NAME'],
            // ];
        }
        
        if ($result) {
            return new JsonResponse([
                'success' => 1,
                'data'    => $result,
                'errors'  => [],
            ]);
        }

        return new JsonResponse([
            'success' => 0,
            'errors'  => ['message' => 'Такого дня нет =('],
        ]);
    }

    /**
     * @Route("/bookthetimelocal/", methods={"POST"})
     *
     * @param Request $request
     *
     * @return JsonResponse
     * @throws Exception
     *
     * @throws RuntimeException
     */
    public function bookTheTimeLocal(Request $request): JsonResponse
    {
        // $flagmanService = new FlagmanService();
        // $bookingResult = $flagmanService->bookTheTime($id);
        //@todo сори за жирный контроллер
        if (!Loader::includeModule('articul.landing')) {
            return JsonErrorResponse::createWithData('', ['errors' => ['order' => 'Модуль для сохранения заявок не подключен']]);
        }

        try {
            $successAdding = TrainingAppsTable::add([
                'UF_NAME'     => $request->get('name'),
                'UF_PHONE'    => $request->get('phone'),
                'UF_EVENT_ID' => (int)$request->get('id'),
                'UF_EMAIL'    => $request->get('email'),
            ]);

            if ($successAdding) {
                $sits = TrainingsTable::query()
                    ->setSelect(['SITS' => 'UTS.FREE_SITS'])
                    ->setFilter(['=ID' => (int)$request->get('id')])
                    ->exec()
                    ->fetch()['SITS'];

                $newSits = (int)$sits - 1;

                //@todo исправить как только реализуют метод update
                \CIBlockElement::SetPropertyValuesEx($request->get('id'), 0, ['FREE_SITS' => $newSits]);
                \CEvent::Send('TRAINING_SERVICE', 's1', [
                    'NAME'  => $request->get('name'),
                    'PHONE' => $request->get('phone'),
                    'DATE'  => $request->get('date'),
                    'TIME'  => $request->get('time'),
                    'EMAIL' => $request->get('email'),
                ]);
            }
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => 'N',
                'errors'  => ['message' => $e->getMessage()],
            ]);

        }

        $response = new JsonResponse([
            'success' => 'Y',
            'errors'  => [],
        ]);

        return $response;
    }
}
