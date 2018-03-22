<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\Catalog\Model;

use Bitrix\Main\LoaderException;
use Bitrix\Main\NotSupportedException;
use Bitrix\Main\ObjectNotFoundException;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use FourPaws\App\Application;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\BitrixOrm\Collection\HlbReferenceItemCollection;
use FourPaws\BitrixOrm\Model\HlbReferenceItem;
use FourPaws\BitrixOrm\Model\IblockElement;
use FourPaws\BitrixOrm\Type\TextContent;
use FourPaws\BitrixOrm\Utils\ReferenceUtils;
use FourPaws\Catalog\Query\BrandQuery;
use FourPaws\Catalog\Query\OfferQuery;
use FourPaws\Search\Model\HitMetaInfoAwareInterface;
use FourPaws\Search\Model\HitMetaInfoAwareTrait;
use JMS\Serializer\Annotation\Accessor;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\Type;
use RuntimeException;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;

class Share extends IblockElement
{
    /**
     * @var string[]
     * @Type("array")
     */
    protected $PROPERTY_LABEL = [];

    /**
     * @var HlbReferenceItemCollection
     */
    protected $label;

    /**
     * @var string
     * @Type("string")
     */
    protected $PROPERTY_SHARE_TYPE = '';

    /**
     * @var string[]
     * @Type("array")
     */
    protected $PROPERTY_TYPE = [];

    /**
     * @var string
     * @Type("string")
     */
    protected $PROPERTY_ONLY_MP = 'N';

    /**
     * @var string
     * @Type("string")
     */
    protected $PROPERTY_SHORT_URL = '';
    /**
     * @var string
     * @Type("string")
     */
    protected $PROPERTY_OLD_URL = '';
    /**
     * @var string[]
     * @Type("array")
     */
    protected $PROPERTY_PRODUCTS = [];

    /**
     * @throws ApplicationCreateException
     * @throws RuntimeException
     * @throws ServiceCircularReferenceException
     * @return HlbReferenceItemCollection
     */
    public function getLabels(): HlbReferenceItemCollection
    {
        if (null === $this->label) {
            $this->label = ReferenceUtils::getReferenceMulti(
                Application::getHlBlockDataManager('bx.hlblock.label'),
                $this->getLabelsXmlId()
            );
        }

        return $this->label;
    }

    /**
     * @return array|string[]
     */
    public function getLabelsXmlId(): array
    {
        if(!empty($this->PROPERTY_LABEL) && !\is_array($this->PROPERTY_LABEL)){
            $this->PROPERTY_LABEL = array($this->PROPERTY_LABEL);
        }
        return $this->PROPERTY_LABEL ?? [];
    }

    /**
     * @param array $xmlIds
     *
     * @return $this
     */
    public function withLabelsXmlIds(array $xmlIds)
    {
        $this->label = null;
        $this->PROPERTY_LABEL = $xmlIds;
        return $this;
    }
}
