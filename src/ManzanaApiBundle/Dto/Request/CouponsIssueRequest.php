<?php

namespace FourPaws\ManzanaApiBundle\Dto\Request;

use FourPaws\ManzanaApiBundle\Dto\Object\CouponIssue;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

class CouponsIssueRequest extends Request
{
    /**
     * @Assert\NotBlank()
     * @Serializer\Type("array<FourPaws\ManzanaApiBundle\Dto\Object\CouponIssue>")
     * @Serializer\SerializedName("messages")
     * @var CouponIssue[]
     */
    protected $couponsIssues = [];


    /**
     * @param array $couponsIssues
     * @return CouponsIssueRequest
     */
    public function setCouponsIssues(array $couponsIssues): CouponsIssueRequest
    {
        $this->couponsIssues = $couponsIssues;
        return $this;
    }

    /**
     * @return CouponIssue[]
     */
    public function getCouponsIssues(): array
    {
        return $this->couponsIssues;
    }
}