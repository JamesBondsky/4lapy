<?php

namespace FourPaws\AppBundle\Exception;


use FourPaws\App\Response\JsonResponse;

class JsonResponseException extends \Exception
{

    /** @var JsonResponse */
    private $jsonResponse;

    /**
     * AjaxMessException constructor.
     * @param JsonResponse $jsonResponse
     */
    public function __construct(JsonResponse $jsonResponse)
    {
        $this->setJsonResponse($jsonResponse);
        parent::__construct();
    }

    /**
     * @return JsonResponse
     */
    public function getJsonResponse(): JsonResponse
    {
        return $this->jsonResponse;
    }

    /**
     * @param JsonResponse $jsonResponse
     * @return $this
     */
    public function setJsonResponse(JsonResponse $jsonResponse)
    {
        $this->jsonResponse = $jsonResponse;
        return $this;
    }

}