<?php

namespace FourPaws\BitrixIblockORM\Model;

use Adv\Bitrixtools\Tools\BitrixUtils;

/**
 * Class IblockEntityBase
 * @package FourPaws\BitrixIblockORM\Model
 *
 * Хранит общие поля для элемента и раздела инфоблока, чтобы не повторять их.
 *
 */
abstract class IblockEntityBase extends BitrixArrayItemBase
{

    /**
     * @var string
     */
    protected $LIST_PAGE_URL = '';

    /**
     * @var bool
     */
    protected $active = true;

    public function __construct(array $fields = [])
    {
        parent::__construct($fields);
        if (isset($fields['ACTIVE'])) {
            $this->withActive(BitrixUtils::bitrixBool2bool($fields['ACTIVE']));
        }
    }

    /**
     * @return string
     */
    public function getListPageUrl(): string
    {
        return $this->LIST_PAGE_URL;
    }

    /**
     * @param string $url
     *
     * @return $this
     */
    public function withListPageUrl(string $url)
    {
        $this->LIST_PAGE_URL = $url;

        return $this;
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->active;
    }

    /**
     * @param bool $active
     *
     * @return $this
     */
    public function withActive(bool $active)
    {
        $this->active = $active;

        return $this;
    }

}
