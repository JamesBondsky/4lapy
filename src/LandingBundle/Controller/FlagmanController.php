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

/**
 * Class BasketController
 *
 * @package FourPaws\SaleBundle\Controller
 * @Route("/flagman")
 */
class FlagmanController extends Controller implements LoggerAwareInterface
{
    use LazyLoggerAwareTrait;
    
    /**
     * FlagmanController constructor.
     *
     */
    public function __construct()
    {
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
            return JsonErrorResponse::createWithData('', ['errors' => ['order' => 'Ошибка при создании заявки']]);
        }
        
        $data = json_decode($request->getContent());
        try {
        
        } catch (\Exception $e) {}
        //@todo код сохранения заявки в хайлоад блок
        //@todo если он успешен то выполняем следающий код
        $success = true;
        
        if ($success) {
            $sits =  LectionsTable::query()
                ->setSelect(['SITS' => 'UTS.FREE_SITS'])
                ->setFilter(['=ID' => $data->eventId])
                ->exec()
                ->fetch()['SITS'];
            
            $newSits = (int) $sits - 1;

            //@todo исправить как только реализуют метод update
            \CIBlockElement::SetPropertyValuesEx($data->eventId, 0, ['FREE_SITS' => $newSits]);
        }

        $response = JsonErrorResponse::createWithData('Заявка успешно сохранена',
            ['11' => '1112'],
            200,
            ['reload' => false]);
         return $response;
    }
}
