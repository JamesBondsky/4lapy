<?php

namespace FourPaws\CatalogBundle\AjaxController;

use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use FourPaws\App\Application;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\App\Response\JsonErrorResponse;
use FourPaws\App\Response\JsonResponse;
use FourPaws\App\Response\JsonSuccessResponse;
use FourPaws\UserBundle\Exception\NotAuthorizedException;
use FourPaws\UserBundle\Service\CurrentUserProviderInterface;
use Psr\Log\LoggerAwareInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;

/**
 * Class ProductInfoController
 *
 * @package FourPaws\CatalogBundle\Controller
 * @Route("/product-report")
 */
class ProductReportController extends Controller implements LoggerAwareInterface
{
    use LazyLoggerAwareTrait;

    protected $currentUserProvider;

    public function __construct(CurrentUserProviderInterface $currentUserProvider)
    {
        $this->currentUserProvider = $currentUserProvider;
    }

    /**
     * @Route("/availability", methods={"POST"})
     *
     * @param Request $request
     *
     * @return JsonResponse
     *
     * @global \CMain $APPLICATION
     * @throws ApplicationCreateException
     */
    public function availabilityAction(Request $request): JsonResponse
    {
        try {
            $user = $this->currentUserProvider->getCurrentUser();
            if (!\array_intersect($user->getGroupsIds(), [
                1,
                4,
                25,
            ])) {
                throw new NotAuthorizedException('');
            }
        } catch (NotAuthorizedException $e) {
            throw new AccessDeniedHttpException('Access denied');
        }

        $articles = $request->get('articles');

        if ($articles) {
            $articles = preg_replace('~\s~', ',', $articles);
        }

        $command = [];
        $command[] = $this->getCommand('b:p:r', $_SERVER['DOCUMENT_ROOT'] . '/upload/product_report.csv');
        if ($articles) {
            $command[] = '--articles=' . $articles;
        }
        if ($step = $request->get('step')) {
            $command[] = '--step=' . $step;
        }

        $process = new Process($command);
        $process->run();
        if ($process->isSuccessful()) {
            $progress = $process->getOutput();

            $response = ['progress' => $progress];
            if ($progress === '100') {
                $response['link'] = '/upload/product_report.csv';
            }

            $result = JsonSuccessResponse::createWithData('', $response);
        } else {
            $result = JsonErrorResponse::create($process->getErrorOutput());
        }

        return $result;
    }

    /**
     * @param string $command
     * @param string $file
     *
     * @return string
     * @throws ApplicationCreateException
     */
    protected function getCommand(string $command, string $file): string
    {
        $php = (new PhpExecutableFinder())->find();

        return \sprintf(
            '%s %s/bin/symfony_console %s --path %s',
            $php,
            Application::getInstance()->getRootDir(),
            $command,
            $file
        );
    }
}
