<?php
declare(strict_types=1);

namespace FourPaws\LandingBundle\Controller;

use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use Articul\Landing\Orm\GroomingAppsTable;
use Articul\Landing\Orm\TrainingAppsTable;
use Bitrix\Iblock\SectionTable;
use Bitrix\Main\Loader;
use Exception;
use FourPaws\App\Application as App;
use FourPaws\App\Response\JsonErrorResponse;
use FourPaws\App\Response\JsonResponse;
use Psr\Log\LoggerAwareInterface;
use RuntimeException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Articul\Landing\Orm\LectionsTable;
use Articul\Landing\Orm\TrainingsTable;
use Articul\Landing\Orm\LectionAppsTable;
use FourPaws\LandingBundle\Service\FlagmanService;
use Bitrix\Main\Entity\ReferenceField;

/**
 * Class FlagmanController
 *
 * @package FourPaws\SaleBundle\Controller
 * @Route("/flagman")
 */
class FlagmanController extends Controller implements LoggerAwareInterface
{
    use LazyLoggerAwareTrait;
    
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
        
        try {
            $lastOurTime = 0;
            $lastDayTime = 0;
            
            $sits = LectionsTable::query()
                ->setSelect(['SITS' => 'UTS.FREE_SITS'])
                ->setFilter(['=ID' => (int)$request->get('id')])
                ->exec()
                ->fetch()['SITS'];
            
            if ($sits <= 0) {
                return new JsonResponse([
                    'success' => 0,
                    'last'    => 0,
                    'errors'  => ['message' => 'Извините, но все места заняты.'],
                ]);
            } elseif ($sits == 1) {
                $lastOurTime = 1;
            }
            
            $successAdding = LectionAppsTable::add([
                'UF_NAME'         => $request->get('name'),
                'UF_PHONE'        => $request->get('phone'),
                'UF_EVENT_ID'     => (int)$request->get('id'),
                'UF_EMAIL'        => $request->get('email'),
                'UF_DATE_CREATE'  => date('d-m-Y h:i:s'),
                'UF_LECTION_NAME' => $request->get('lection_name'),
                'UF_LECTION_DATE' => $request->get('lection_date'),
            ]);
            
            if ($successAdding) {
                $newSits = (int)$sits - 1;
                
                \CIBlockElement::SetPropertyValuesEx($request->get('id'), 0, ['FREE_SITS' => $newSits]);
                
                $sectionId = LectionsTable::query()
                    ->setSelect(['SECTION_ID' => 'SECTION.ID'])
                    ->setFilter(['=ID' => (int)$request->get('id')])
                    ->registerRuntimeField(new ReferenceField(
                        'SECTION',
                        'Bitrix\Iblock\SectionTable',
                        ['=this.IBLOCK_SECTION_ID' => 'ref.ID']
                    ))
                    ->exec()
                    ->fetch()['SECTION_ID'];
                
                $stillHasTime = LectionsTable::query()
                    ->setSelect(['ID'])
                    ->setFilter(['=IBLOCK_SECTION_ID' => $sectionId, '=ACTIVE' => 'Y', '>UTS.FREE_SITS' => 0])
                    ->exec()
                    ->fetchAll();
                
                if (!$stillHasTime) {
                    $lastDayTime = 1;
                }
                
                \CEvent::Send('LECTION_SERVICE', 's1', [
                    'NAME'  => $request->get('name'),
                    'PHONE' => $request->get('phone'),
                    'DATE'  => $request->get('date'),
                    'TIME'  => $request->get('time'),
                    'EMAIL' => $request->get('email'),
                ]);
                
                
                $response = new JsonResponse([
                    'success'       => 1,
                    'last_our_time' => $lastOurTime,
                    'last_day_time' => $lastDayTime,
                    'errors'        => [],
                ]);
                
                return $response;
            }
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => 'N',
                'errors'  => ['message' => $e->getMessage()],
            ]);
            
        }
    }
    
    /**
     * @Route("/get_days_by_clinic/{id}/", methods={"GET"})
     *
     * @param Request $request
     * @param string  $id
     *
     * @return JsonResponse
     * @throws Exception
     *
     * @throws RuntimeException
     */
    public function getDaysByClinic(Request $request, $id): JsonResponse
    {
        $result = [];
        
        $flagmanService = new FlagmanService();
        $result         = $flagmanService->getDaysByClinic($id);
        
        if ($result) {
            return new JsonResponse([
                'success' => 1,
                'data'    => $result,
                'errors'  => [],
            ]);
        }
        
        return new JsonResponse([
            'success' => 0,
            'errors'  => ['message' => 'Такой клиники нет =('],
        ]);
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
        
        $sectionName = SectionTable::query()
            ->setSelect(['NAME'])
            ->setFilter(['=ID' => $id])
            ->exec()
            ->fetch()['NAME'];
        
        preg_match('/([0-9]{2,4}).([0-9]{2,4}).([0-9]{2,4})/', $sectionName, $sectionMatches);
        
        foreach ($elements as $key => $element) {
            if ($element['FREE_VALUE'] == 'N') {
                unset($elements[$key]);
                continue;
            }
            
            preg_match('/^[0-9]{2}/', $element['NAME'], $matches);
            if ($matches[0] <= date('H') && $sectionMatches[0] == date('d.m.Y')) {
                unset($elements[$key]);
                continue;
            }
            
            $result[$key] = [
                'timeId' => $element['ID'],
                'time'   => $element['NAME'],
            ];
        }
        
        usort($result, function ($a, $b) {
            preg_match('/^([0-9]{2})/', $a['time'], $matchesA);
            preg_match('/^([0-9]{2})/', $b['time'], $matchesB);
            
            return ($matchesA[0] > $matchesB[0]) ? 1 : -1;
        });
        
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
        $name    = $request->get('name');
        $phone   = $request->get('phone');
        $id      = $request->get('id');
        $email   = $request->get('email');
        $animal  = $request->get('animal');
        $breed   = $request->get('breed');
        $service = $request->get('service');
        $clinic  = $request->get('clinic');
        $date    = $request->get('date');
        $time    = $request->get('time');
        
        // $flagmanService = new FlagmanService();
        // $bookingResult = $flagmanService->bookTheTime($id);
        //@todo сори за жирный контроллер и дублирование
        if (!Loader::includeModule('articul.landing')) {
            return JsonErrorResponse::createWithData('', ['errors' => ['order' => 'Модуль для сохранения заявок не подключен']]);
        }
        
        try {
            $successAdding = GroomingAppsTable::add([
                'UF_NAME'     => $name,
                'UF_PHONE'    => $phone,
                'UF_EVENT_ID' => (int)$id,
                'UF_EMAIL'    => $email,
                'UF_ANIMAL'   => $animal,
                'UF_BREED'    => $breed,
                'UF_SERVICE'  => $service,
                'UF_CLINIC'   => $clinic,
                'UF_DATE'     => $date,
            ]);
            
            if ($successAdding) {
                \CIBlockElement::SetPropertyValuesEx($request->get('id'), ['ACTIVE' => 'N', 'PROPERTY_VALUES' => ['FREE' => 'N']]);
                $sender = App::getInstance()->getContainer()->get('expertsender.service');
                $sender->sendGroomingEmail($name, $phone, $email, $animal, $breed, $service, $clinic, $date);
                
                \CEvent::Send('GROOMING_SERVICE', 's1', [
                    'NAME'    => $name,
                    'PHONE'   => $phone,
                    'DATE'    => $date,
                    'TIME'    => $time,
                    'EMAIL'   => $email,
                    'ANIMAL'  => $animal,
                    'BREED'   => $breed,
                    'SERVICE' => $service,
                    'CLINIC'  => $clinic,
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
            
            preg_match('/^[0-9]{2}/', $element['NAME'], $matches);
            preg_match('/([0-9]{2,4}).([0-9]{2,4}).([0-9]{2,4})/', $element['SECTION_NAME'], $matchesDate);
            if ($matches[0] <= date('H') && $matchesDate[0] == date('d.m.Y')) {
                unset($elements[$key]);
                continue;
            }
            
            $result[$key] = [
                'timeId' => $element['ID'],
                'time'   => $element['NAME'],
            ];
        }
        
        usort($result, function ($a, $b) {
            preg_match('/^([0-9]{2})/', $a['time'], $matchesA);
            preg_match('/^([0-9]{2})/', $b['time'], $matchesB);
            
            return ($matchesA[0] > $matchesB[0]) ? 1 : -1;
        });
        
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
        //@todo сори за жирный контроллер а так же отрефакторить повторяющийся код, изначально он таким не был, но куча изменений требований, сделали свое дело
        if (!Loader::includeModule('articul.landing')) {
            return JsonErrorResponse::createWithData('', ['errors' => ['order' => 'Модуль для сохранения заявок не подключен']]);
        }
        
        try {
            $name = $request->get('name');
            $phone = $request->get('phone');
            $id = $request->get('id');
            $email = $request->get('email');
            $time = $request->get('time');
            $date = $request->get('date');
            
            $successAdding = TrainingAppsTable::add([
                'UF_NAME'     => $name,
                'UF_PHONE'    => $phone,
                'UF_EVENT_ID' => (int)$id,
                'UF_EMAIL'    => $email,
            ]);
            
            if ($successAdding) {
                $sits = TrainingsTable::query()
                    ->setSelect(['SITS' => 'UTS.FREE_SITS'])
                    ->setFilter(['=ID' => (int)$id])
                    ->exec()
                    ->fetch()['SITS'];
                
                $newSits = (int)$sits - 1;
                
                //@todo исправить как только реализуют метод update
                \CIBlockElement::SetPropertyValuesEx($id, 0, ['FREE_SITS' => $newSits]);
    
                // $sender = App::getInstance()->getContainer()->get('expertsender.service');
                // $sender->sendTrainingEmail($name, $phone, $email, $animal, $breed, $service, $clinic, $date);
                \CEvent::Send('TRAINING_SERVICE', 's1', [
                    'NAME'  => $name,
                    'PHONE' => $phone,
                    'DATE'  => $date,
                    'TIME'  => $time,
                    'EMAIL' => $email,
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
