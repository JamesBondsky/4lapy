<?php

namespace FourPaws\DeliveryBundle\Collection;

use Doctrine\Common\Collections\ArrayCollection;
use FourPaws\DeliveryBundle\Entity\Base;

class IntervalRuleCollection extends ArrayCollection
{
    public function getByType(string $type) {
        return $this->filter(
            function (Base $rule) use ($type) {
                return $rule->getType() === $type;
            }
        );
    }
}
