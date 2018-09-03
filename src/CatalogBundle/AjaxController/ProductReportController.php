<?php

namespace FourPaws\CatalogBundle\AjaxController;

use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\App\Response\JsonErrorResponse;
use FourPaws\App\Response\JsonResponse;
use FourPaws\App\Response\JsonSuccessResponse;
use FourPaws\CatalogBundle\Service\AvailabilityReportService;
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
 * @Route("/product-report")
 */
class ProductReportController extends Controller implements LoggerAwareInterface
{
    use LazyLoggerAwareTrait;

    /**
     * @var CurrentUserProviderInterface
     */
    protected $currentUserProvider;

    /**
     * @var AvailabilityReportService
     */
    protected $availabilityReportService;

    public function __construct(
        CurrentUserProviderInterface $currentUserProvider,
        AvailabilityReportService $availabilityReportService
    )
    {
        $this->availabilityReportService = $availabilityReportService;
        $this->currentUserProvider = $currentUserProvider;
    }

    /**
     * @Route("/availability", methods={"POST"})
     *
     * @param Request $request
     *
     * @return JsonResponse
     * @throws ApplicationCreateException
     */
    public function availabilityAction(Request $request): JsonResponse
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

            $articles = \array_filter(\preg_split('~[\s,]~', $request->get('articles')));
            $reportResult = $this->availabilityReportService->export(
                $_SERVER['DOCUMENT_ROOT'] . '/upload/product_report.csv',
                $request->get('step', 0),
                $articles,
                'Windows-1251'
            );

            $result = JsonSuccessResponse::createWithData('', [
                'progress' => round($reportResult->getProgress() * 100),
                'url'      => '/upload/product_report.csv',
            ]);
        } catch (\Exception $e) {
            $this->log()->error(
                \sprintf(
                    'failed to create availability report: %s: %s',
                    \get_class($e),
                    $e->getMessage()
                ),
                ['trace' => $e->getTrace()]);
            $result = JsonErrorResponse::create($e->getMessage());
        }

        return $result;
    }
}
