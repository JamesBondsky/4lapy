<?php

namespace FourPaws\StoreBundle\Entity;

use FourPaws\App\Application;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\Catalog\Model\Offer;
use FourPaws\StoreBundle\Exception\NotFoundException;
use FourPaws\StoreBundle\Service\StoreService;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

class Stock extends Base
{
    /**
     * @var int
     * @Serializer\Type("integer")
     * @Serializer\SerializedName("ID")
     * @Serializer\Groups(groups={"read","update","delete"})
     * @Assert\NotBlank(groups={"read","update","delete"})
     * @Assert\GreaterThanOrEqual(value="1",groups={"read","update","delete"})
     * @Assert\Blank(groups={"create"})
     */
    protected $id = 0;

    /**
     * @var int
     * @Serializer\Type("integer")
     * @Serializer\SerializedName("PRODUCT_ID")
     * @Serializer\Groups(groups={"create", "read","update","delete"})
     * @Assert\NotBlank(groups={"create", "read","update","delete"})
     * @Assert\GreaterThanOrEqual(value="1",groups={"create", "read","update","delete"})
     * @Assert\Blank(groups={"create"})
     */
    protected $productId = 0;

    /**
     * @var int
     * @Serializer\Type("float")
     * @Serializer\SerializedName("AMOUNT")
     * @Serializer\Groups(groups={"create", "read","update","delete"})
     * @Assert\NotBlank(groups={"create", "read","update","delete"})
     * @Assert\Blank(groups={"create"})
     */
    protected $amount = 0;

    /**
     * @var int
     * @Serializer\Type("integer")
     * @Serializer\SerializedName("STORE_ID")
     * @Serializer\Groups(groups={"create", "read","update","delete"})
     * @Assert\NotBlank(groups={"create", "read","update","delete"})
     * @Assert\GreaterThanOrEqual(value="1",groups={"create", "read","update","delete"})
     * @Assert\Blank(groups={"create"})
     */
    protected $storeId = 0;

    /** @var Store */
    protected $store;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return Stock
     */
    public function setId(int $id): Stock
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return int
     */
    public function getProductId(): int
    {
        return (int)$this->productId;
    }

    /**
     * @param int $productId
     * @return Stock
     */
    public function setProductId(int $productId): Stock
    {
        $this->productId = $productId;

        return $this;
    }

    /**
     * @return float
     */
    public function getAmount(): float
    {
        return (float)$this->amount;
    }

    /**
     * @param float $amount
     * @return Stock
     */
    public function setAmount(float $amount): Stock
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * @return int
     */
    public function getStoreId(): int
    {
        return (int)$this->storeId;
    }

    /**
     * @param int $storeId
     * @return Stock
     */
    public function setStoreId(int $storeId): Stock
    {
        $this->storeId = $storeId;

        return $this;
    }

    /**
     * @return Store
     * @throws ApplicationCreateException
     * @throws NotFoundException
     */
    public function getStore(): Store
    {
        if (null === $this->store) {
            /** @var StoreService $storeService */
            $storeService = Application::getInstance()->getContainer()->get('store.service');
            $this->store = $storeService->getById($this->getId());
        }

        return $this->store;
    }

    /**
     * @param Store $store
     * @return Stock
     */
    public function setStore(Store $store): Stock
    {
        $this->store = $store;
        return $this;
    }
}
