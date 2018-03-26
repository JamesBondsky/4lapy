<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\MobileApiBundle\Dto\Response;

use Doctrine\Common\Collections\Collection;
use FourPaws\MobileApiBundle\Dto\Object\Location\MetroLine;
use JMS\Serializer\Annotation as Serializer;

class MetroStationsResponse
{
    /**
     * @Serializer\SerializedName("metro")
     * @Serializer\Type("ArrayCollection<FourPaws\MobileApiBundle\Dto\Object\Location\MetroLine>")
     * @var Collection|MetroLine[]
     */
    private $metroLines;

    public function __construct(Collection $metroLines)
    {
        $this->metroLines = $metroLines;
    }
}
