<?php
declare(strict_types=1);
/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\LandingBundle\Service;

use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
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
use GuzzleHttp\Client;

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
    
    public function getElementsBySectionId($id)
    {
        return TrainingsTable::query()
            ->setSelect(['ID', 'NAME', 'FREE_SITS' => 'UTS.FREE_SITS', 'SITS' => 'UTS.SITS'])
            ->setFilter(['=IBLOCK_SECTION_ID' => $id, '=ACTIVE' => 'Y'])
            ->exec()
            ->fetchAll();
    }
    
    public function bookTheTime($id)
    {
        //@todo перенести логику из контролера
    }
}
