<?php

namespace FourPaws\App\Response;

use FourPaws\App\Application;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\App\Model\ResponseContent\JsonContent;
use Symfony\Component\HttpFoundation\JsonResponse as BaseJsonResponse;

/**
 * Class JsonErrorResponse
 *
 * @package FourPaws\App\Model
 */
class JsonResponse extends BaseJsonResponse
{

    /**
     * @param string $message
     * @param bool $success
     * @param array $data
     * @param array $options
     * @see \FourPaws\App\Model\ResponseContent\JsonContent
     *
     * @return JsonContent
     */
    public static function buildContent($message = '', $success = true, $data = null, $options = []): JsonContent
    {
        $content = new JsonContent($message, $success, $data);

        if ($options['redirect']) {
            $content->withRedirect($options['redirect']);
        }

        if (isset($options['reload'])) {
            $content->withReload((bool)$options['reload']);
        }

        return $content;
    }

    /**
     * @param null  $data
     * @param int   $status
     * @param array $headers
     *
     * @return static
     * @throws ApplicationCreateException
     */
    public static function create($data = null, $status = 200, $headers = []): JsonResponse
    {
        $headers = static::setAllowOrigin($headers);

        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return parent::create($data, $status, $headers);
    }

    /**
     * @param array $headers
     *
     * @return array
     * @throws ApplicationCreateException
     */
    protected static function setAllowOrigin(array $headers): array
    {
        if ($request = Application::getInstance()->getContainer()->get('request_stack')->getCurrentRequest()) {
            $origin = $request->headers->get('Origin');

            if (mb_strpos($origin, $request->server->get('SERVER_NAME')) !== false) {
                $headers['Access-Control-Allow-Origin'] = $origin;
                $headers['Access-Control-Allow-Credentials'] = 'true';
                $headers['Vary'] = 'Origin';
            }
        }

        return $headers;
    }


    public function extendData(array $data)
    {
        $currentData = json_decode($this->getContent(), true);
        $currentData['data'] = array_merge($currentData['data'], $data);
        $this->setContent(json_encode($currentData));

        return $this;
    }
}
