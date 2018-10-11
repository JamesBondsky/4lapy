<?php

namespace FourPaws\SaleBundle\AjaxController;

use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\App\Response\JsonErrorResponse;
use FourPaws\App\Response\JsonResponse;
use FourPaws\App\Response\JsonSuccessResponse;
use FourPaws\SaleBundle\Service\Reports\RROrderReportService;
use FourPaws\UserBundle\Exception\NotAuthorizedException;
use FourPaws\UserBundle\Service\CurrentUserProviderInterface;
use Psr\Log\LoggerAwareInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Class ProductInfoController
 *
 * @package FourPaws\CatalogBundle\Controller
 * @Route("/order-report")
 */
class OrderReportController extends Controller implements LoggerAwareInterface
{
    use LazyLoggerAwareTrait;

    /**
     * @var CurrentUserProviderInterface
     */
    protected $currentUserProvider;

    /**
     * @var RROrderReportService
     */
    protected $rrOrderReportService;

    public function __construct(
        CurrentUserProviderInterface $currentUserProvider,
        RROrderReportService $rrOrderReportService
    )
    {
        $this->rrOrderReportService = $rrOrderReportService;
        $this->currentUserProvider = $currentUserProvider;
    }

    /**
     * @Route("/retail-rocket", methods={"POST"})
     *
     * @param Request $request
     *
     * @return JsonResponse
     * @throws ApplicationCreateException
     */
    public function retailRocketAction(Request $request): JsonResponse
    {
        try {
            try {
                $user = $this->currentUserProvider->getCurrentUser();
                if (!\array_intersect($user->getGroupsIds(), [
                    1,
                    4,
                    25,
                ])) {
                    throw new NotAuthorizedException('Access denied');
                }
            } catch (NotAuthorizedException $e) {
                throw new AccessDeniedHttpException('Access denied');
            }

            $from = $request->get('from', '');
            if (!$date = date_create_from_format('Y-m-d', $from)) {
                $date = (new \DateTime())->modify('-1 year');
            }

            $reportResult = $this->rrOrderReportService->export(
                $_SERVER['DOCUMENT_ROOT'] . '/upload/retail_rocket_orders_report.csv',
                $request->get('step', 0),
                $date->setTime(0, 0, 0, 0)
            );

            $result = JsonSuccessResponse::createWithData('', [
                'processed' => $reportResult->getCountProcessed(),
                'total'     => $reportResult->getCountTotal(),
                'progress'  => round($reportResult->getProgress() * 100),
                'url'       => '/upload/retail_rocket_orders_report.csv',
            ]);
        } catch (\Exception $e) {
            $this->log()->error(
                \sprintf(
                    'failed to create RR order report: %s: %s',
                    \get_class($e),
                    $e->getMessage()
                ),
                ['trace' => $e->getTrace()]);
            $result = JsonErrorResponse::create($e->getMessage());
        }

        return $result;
    }
}
