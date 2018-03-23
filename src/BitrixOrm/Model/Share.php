<?php

namespace FourPaws\BitrixOrm\Model;

use FourPaws\App\Application;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\BitrixOrm\Collection\HlbReferenceItemCollection;
use FourPaws\BitrixOrm\Utils\ReferenceUtils;
use FourPaws\Catalog\Collection\OfferCollection;
use FourPaws\Catalog\Query\OfferQuery;
use JMS\Serializer\Annotation\Type;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;


/**
 * Class Share
 *
 * @package FourPaws\BitrixOrm\Model
 */
class Share extends IblockElement
{
    /**
     * @var string
     * @Type("string")
     */
    protected $PROPERTY_LABEL = '';

    /**
     * @var string
     * @Type("string")
     */
    protected $PROPERTY_SHARE_TYPE = '';

    /**
     * @var HlbReferenceItemCollection
     */
    protected $shareType;

    /**
     * @var string[]
     * @Type("array")
     */
    protected $PROPERTY_TYPE = [];

    /**
     * @var HlbReferenceItemCollection
     */
    protected $type;

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
     * @var OfferCollection
     */
    protected $products;

    /**
     * @return string
     */
    public function getPropertyLabel(): string
    {
        return $this->PROPERTY_LABEL;
    }

    /**
     * @param string $propertyLabel
     */
    public function setPropertyLabel(string $propertyLabel): void
    {
        $this->PROPERTY_LABEL = $propertyLabel;
    }

    /**
     * @return string
     */
    public function getPropertyShareType(): string
    {
        return $this->PROPERTY_SHARE_TYPE;
    }

    /**
     * @param string $propertyShareType
     */
    public function setPropertyShareType(string $propertyShareType): void
    {
        $this->PROPERTY_SHARE_TYPE = $propertyShareType;
    }

    /**
     * @return HlbReferenceItem
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     * @throws \RuntimeException
     * @throws ApplicationCreateException
     */
    public function getShareType(): HlbReferenceItem
    {
        if (null === $this->shareType) {
            $this->shareType = ReferenceUtils::getReference(
                Application::getHlBlockDataManager('bx.hlblock.sharetype'),
                $this->getPropertyShareType()
            );
        }
        return $this->shareType;
    }

    /**
     * @return string[]
     */
    public function getPropertyType(): array
    {
        return $this->PROPERTY_TYPE;
    }

    /**
     * @param string[] $propertyType
     */
    public function setPropertyType(array $propertyType): void
    {
        $this->PROPERTY_TYPE = $propertyType;
    }

    /**
     * @return HlbReferenceItemCollection
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     * @throws \RuntimeException
     * @throws ApplicationCreateException
     */
    public function getType(): HlbReferenceItemCollection
    {
        if (null === $this->type) {
            $this->type = ReferenceUtils::getReferenceMulti(
                Application::getHlBlockDataManager('bx.hlblock.publicationtype'),
                $this->getPropertyType()
            );
        }
        return $this->type;
    }

    /**
     * @return string
     */
    public function getPropertyOnlyMp(): string
    {
        return $this->PROPERTY_ONLY_MP;
    }

    /**
     * @param string $propertyOnlyMp
     */
    public function setPropertyOnlyMp(string $propertyOnlyMp): void
    {
        $this->PROPERTY_ONLY_MP = $propertyOnlyMp;
    }

    /**
     * @return bool
     */
    public function isOnlyMobile(): bool
    {
        return $this->getPropertyOnlyMp() === 'Y';
    }

    /**
     * @return string
     */
    public function getPropertyShortUrl(): string
    {
        return $this->PROPERTY_SHORT_URL;
    }

    /**
     * @param string $propertyShortUrl
     */
    public function setPropertyShortUrl(string $propertyShortUrl): void
    {
        $this->PROPERTY_SHORT_URL = $propertyShortUrl;
    }

    /**
     * @return string
     */
    public function getPropertyOldUrl(): string
    {
        return $this->PROPERTY_OLD_URL;
    }

    /**
     * @param string $propertyOldUrl
     */
    public function setPropertyOldUrl(string $propertyOldUrl): void
    {
        $this->PROPERTY_OLD_URL = $propertyOldUrl;
    }

    /**
     * @return string[]
     */
    public function getPropertyProducts(): array
    {
        return $this->PROPERTY_PRODUCTS;
    }

    /**
     * @param string[] $propertyProducts
     */
    public function setPropertyProducts(array $propertyProducts): void
    {
        $this->PROPERTY_PRODUCTS = $propertyProducts;
    }

    /**
     * @return OfferCollection
     */
    public function getProducts(): OfferCollection
    {
        if (null === $this->products) {
            $this->products = (new OfferQuery())->withFilter(['=XML_ID'=>$this->getPropertyProducts()])->exec();
        }
        return $this->products;
    }
}
