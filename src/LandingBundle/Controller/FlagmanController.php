<?php
declare(strict_types=1);
/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\LandingBundle\Controller;

use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
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
use Articul\Landing\Orm\LectionAppsTable;
use GuzzleHttp\Client;

/**
 * Class BasketController
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
                'UF_EVENT_ID' => (int)$request->get('eventId')
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
     * @Route("/getschedule/{action}/{id}", methods={"GET"})
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
        $result = [];
        
        $this->url .= 'get-schedule/' . $action . '/';
        
        $response = $this->guzzleClient->request('GET', $this->url, [
            'headers' => [
                'Content-Type'  => 'application/json',
                'Authorization' => 'Bearer ' . $this->token,
            ],
        ]);
        
        $body = $response->getBody();
        
        $requestResult = json_decode($body->getContents(), true);

        if ($requestResult[$id]) {
            $actionTime = $requestResult[$id]['exec'];
            
            $hours   = $actionTime / 60;
            $minutes = $actionTime % 60;
            
            $actionTimeForPrint = (int)$hours . ':' . $minutes;
            
            foreach ($requestResult[$id]['times'] as $timeKey => $time) {
                if ($time['status'] == 'Y') {
                    $endTimestamp = strtotime($timeKey) + strtotime($actionTimeForPrint) - strtotime("00:00:00");
                    $endTime      = date('H:i', $endTimestamp);

                    $result[$time['id']] = $timeKey . ' - ' . $endTime;
                }
            }
            
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
        
        return new JsonResponse();
    }
    
    /**
     * @Route("/bookthetime/{id}", methods={"POST"})
     *
     * @param Request $request
     * @param string  $id
     *
     * @return JsonResponse
     * @throws Exception
     *
     * @throws RuntimeException
     */
    public function bookTheTime(Request $request, $id): JsonResponse
    {
        
        // $data = json_decode($request->getContent());

        $this->url .= 'book-the-time/' . $id . '/';
        
        $response = $this->guzzleClient->request('POST', $this->url, [
            'headers' => [
                'Content-Type'  => 'application/json',
                'Authorization' => 'Bearer ' . $this->token,
            ],
            'json'    => [
                "name"    => $request->get('name'),
                "phone"   => $request->get('name'),
                "id"      => $request->get('name'),
                "comment" => $request->get('animal') . ' ' . $request->get('breed') . ' ' . $request->get('service'),
            ],
        ]);
        
        $body = $response->getBody();
        
        return new JsonResponse($body->getContents());
    }
}
