<?php
declare(strict_types=1);
/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\LandingBundle\Service;

use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use Articul\Landing\Orm\GroomingTable;
use Articul\Landing\Orm\TrainingAppsTable;
use Bitrix\Iblock\ElementTable;
use Bitrix\Main\Loader;
use Exception;
use FourPaws\App\Application as App;
use Psr\Log\LoggerAwareInterface;
use RuntimeException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Articul\Landing\Orm\LectionsTable;
use Articul\Landing\Orm\TrainingsTable;
use Articul\Landing\Orm\LectionAppsTable;
use Bitrix\Main\Entity\ReferenceField;
use Bitrix\Iblock\SectionTable;

/**
 * Class FlagmanService
 *
 * @package FourPaws\SaleBundle\Controller
 * @Route("/flagman")
 */
class FlagmanService extends Controller implements LoggerAwareInterface
{
    use LazyLoggerAwareTrait;
    
    public function __construct()
    {
        Loader::includeModule('iblock');
    }
    
    public function getElementsBySectionTrainingId($id)
    {
        return TrainingsTable::query()
            ->setSelect(['ID', 'NAME', 'FREE_SITS' => 'UTS.FREE_SITS', 'SITS' => 'UTS.SITS', 'SECTION_NAME' => 'SECTION.NAME'])
            ->setFilter(['=IBLOCK_SECTION_ID' => $id, '=ACTIVE' => 'Y'])
            ->registerRuntimeField(
                new ReferenceField(
                    'SECTION',
                    '\Bitrix\Iblock\SectionTable',
                    ['=this.IBLOCK_SECTION_ID' => 'ref.ID']
                ))
            ->exec()
            ->fetchAll();
    }
    
    public function getElementsBySectionGroomingId($id)
    {
        return GroomingTable::query()
            ->setSelect(['ID', 'NAME', 'FREE' => 'UTS.FREE', 'FREE_VALUE' => 'ENUM.XML_ID'])
            ->registerRuntimeField(
                new ReferenceField(
                    'ENUM',
                    '\Bitrix\Iblock\PropertyEnumerationTable',
                    ['=this.FREE' => 'ref.ID']
                )
            )
            ->setFilter(['=IBLOCK_SECTION_ID' => $id, '=FREE_VALUE' => 'Y'])
            ->exec()
            ->fetchAll();
    }
    
    public function getDaysByClinic($id)
    {
        $result = [];
        
        try {
            $days = SectionTable::query()
                ->setSelect(['ID', 'NAME', 'TIME' => 'TIMES.NAME', 'ACTIVE_TIME' => 'TIMES.ACTIVE'])
                ->registerRuntimeField(
                    new ReferenceField(
                        'TIMES',
                        'Bitrix\Iblock\ElementTable',
                        ['=this.ID' => 'ref.IBLOCK_SECTION_ID']
                    ))
                ->setFilter(['=IBLOCK_SECTION_ID' => $id, '=ACTIVE' => 'Y', '=DEPTH_LEVEL' => 2])
                ->setOrder(['SORT' => 'ASC'])
                ->exec()
                ->fetchAll();
            
            foreach ($days as $key => $day) {
                if (!empty($day['TIME']) && $day['ACTIVE_TIME'] == 'Y') {
                    preg_match('/([0-9]{2,4}).([0-9]{2,4}).([0-9]{2,4})/', $day['NAME'], $matches);
            
                    if (strtotime($matches[0]) < strtotime('today')) {
                        continue;
                    }
    
                    $result[$day['ID']] = [
                        'id' => $day['ID'],
                        'name' => $day['NAME'],
                    ];
                }
            }
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    
        usort($result, function ($a, $b) {
            preg_match('/([0-9]{2,4}).([0-9]{2,4}).([0-9]{2,4})/', $a['name'], $matchesA);
            preg_match('/([0-9]{2,4}).([0-9]{2,4}).([0-9]{2,4})/', $b['name'], $matchesB);
        
            return (strtotime($matchesA[0]) > strtotime($matchesB[0])) ? 1 : -1;
        });
        
        return $result;
    }
    
    public function bookTheTime($id)
    {
        //@todo перенести логику из контролера
    }
}
