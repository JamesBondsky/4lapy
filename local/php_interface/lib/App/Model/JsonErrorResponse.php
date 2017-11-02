<?php

namespace FourPaws\App\Model;

use FourPaws\App\Model\ResponseContent\JsonContent;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class JsonErrorResponse
 *
 * @package FourPaws\App\Model
 */
class JsonErrorResponse extends JsonResponse
{
    /**
     * Создаётся JsonResponse с предустановленным JsonContent и success = false
     *
     * @inheritdoc
     */
    public static function create($data = null, $status = 200, $headers = [])
    {
        return parent::create(new JsonContent($data, false), $status, $headers);
    }
    
    /**
     * Создаётся JsonResponse с предустановленным JsonContent, data и success = false
     *
     * @param string $message
     * @param array  $data
     * @param int    $status
     *
     * @return JsonResponse
     */
    public static function createWithData(string $message = '', array $data = [], int $status = 200) : JsonResponse
    {
        return parent::create(new JsonContent($message, false, $data), $status);
    }
}
