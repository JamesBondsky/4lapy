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
    
    /**
     * FlagmanController constructor.
     *
     */
    public function __construct()
    {
        $this->url = getenv('VET_CLINIC');
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
        
        $data = json_decode($request->getContent());
        
        try {
            $successAdding = LectionAppsTable::add([
                'UF_USER_ID' => (int) $data->userId,
                'UF_NAME' => $data->name,
                'UF_PHONE' => $data->phone,
                'UF_EVENT_ID' => (int) $data->eventId,
            ]);

            if ($successAdding) {
                $sits = LectionsTable::query()
                    ->setSelect(['SITS' => 'UTS.FREE_SITS'])
                    ->setFilter(['=ID' => $data->eventId])
                    ->exec()
                    ->fetch()['SITS'];
        
                $newSits = (int)$sits - 1;
                
                //@todo исправить как только реализуют метод update
                \CIBlockElement::SetPropertyValuesEx($data->eventId, 0, ['FREE_SITS' => $newSits]);
            }
        } catch (\Exception $e) {
            return JsonErrorResponse::createWithData('', ['errors' => ['order' => 'Ошибка при создании заявки']]);
        }
        
        $response = JsonErrorResponse::createWithData('Заявка успешно сохранена',
            [],
            200,
            ['reload' => false]);
        
        return $response;
    }
    
    /**
     * @Route("/getschedule/{id}", methods={"GET"})
     *
     * @param Request $request
     *
     * @return JsonResponse
     * @throws Exception
     *
     * @throws RuntimeException
     */
    public function getSchedule(Request $request): JsonResponse
    {
        // $this->url .=
        // $response = $this->guzzleClient->request('GET', $this->url, []);
        // echo '<pre>';
        // print_r($request->get('id'));
        // echo '</pre>';
        // die;
    }
}
