<?php

namespace FourPaws\App\Model;

use FourPaws\App\Model\ResponseContent\JsonContent;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class JsonSuccessResponse
 *
 * @package FourPaws\App\Model
 */
class JsonSuccessResponse extends JsonResponse
{
    /**
     * Создаётся JsonResponse с предустановленным JsonContent и success = true
     *
     * @inheritdoc
     */
    public static function create($message = null, $status = 200, $headers = [])
    {
        return parent::create(new JsonContent($message), $status, $headers);
    }
    
    /**
     * Создаётся JsonResponse с предустановленным JsonContent, data и success = true
     *
     * @param string $message
     * @param array  $data
     * @param int    $status
     *
     * @return JsonResponse
     */
    public static function createWithData(string $message = '', array $data = [], int $status = 200) : JsonResponse
    {
        return parent::create(new JsonContent($message, true, $data), $status);
    }
}
