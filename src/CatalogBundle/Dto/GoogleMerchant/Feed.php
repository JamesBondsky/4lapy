<?php

namespace FourPaws\CatalogBundle\Dto\GoogleMerchant;

use Doctrine\Common\Annotations\Annotation\Required;
use JMS\Serializer\Annotation as Serializer;

/**
 * Class Feed
 *
 * @package FourPaws\CatalogBundle\Dto\GoogleMerchant
 *
 * @Serializer\XmlRoot("rss")
 * @Serializer\XmlNamespace(uri="http://base.google.com/ns/1.0", prefix="g")
 */
class Feed
{
    /**
     * @Serializer\XmlAttribute()
     * @Serializer\Type("string")
     *
     * @var string
     */
    protected $version = '2.0';

    /**
     * @Required()
     * @Serializer\Type("FourPaws\CatalogBundle\Dto\GoogleMerchant\Channel")
     *
     * @var Channel
     */
    protected $channel;

    /**
     * @return Channel
     */
    public function getChannel(): Channel
    {
        return $this->channel;
    }

    /**
     * @param Channel $channel
     *
     * @return Feed
     */
    public function setChannel(Channel $channel): Feed
    {
        $this->channel = $channel;

        return $this;
    }
}
