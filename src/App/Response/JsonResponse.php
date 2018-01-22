<?php

namespace FourPaws\App\Response;

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
}
