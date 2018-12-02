<?php

namespace FourPaws\Catalog\Model;

use DateTimeImmutable;
use FourPaws\BitrixOrm\Model\IblockElement;
use JMS\Serializer\Annotation\Accessor;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\Type;

class Banner extends IblockElement
{
    /**
     * @var bool
     * @Type("bool")
     */
    protected $active = true;

    /**
     * @var DateTimeImmutable
     * @Type("DateTimeImmutable")
     * @Accessor(getter="getDateActiveFrom")
     */
    protected $dateActiveFrom;

    /**
     * @var DateTimeImmutable
     * @Type("DateTimeImmutable")
     * @Accessor(getter="getDateActiveTo")
     */
    protected $dateActiveTo;

    /**
     * @var int
     * @Type("int")
     */
    protected $ID = 0;

    /**
     * @var string
     * @Type("string")
     */
    protected $CODE = '';

    /**
     * @var string
     * @Type("string")
     */
    protected $XML_ID = '';

    /**
     * @var string
     * @Type("string")
     */
    protected $NAME = '';

    /**
     * @var int
     * @Type("int")
     */
    protected $SORT = 500;

    /**
     * @var string
     * @Type("string")
     */
    protected $PREVIEW_TEXT = '';

    /**
     * @var string
     * @Type("string")
     */
    protected $PREVIEW_TEXT_TYPE = '';

    /**
     * @var string
     * @Type("string")
     */
    protected $DETAIL_TEXT = '';

    /**
     * @var string
     * @Type("string")
     */
    protected $DETAIL_TEXT_TYPE = '';

    /**
     * @var string
     * @Type("string")
     */
    protected $DETAIL_PAGE_URL = '';

    /**
     * @var int
     * @Type("int")
     */
    protected $PREVIEW_PICTURE = 0;

    /**
     * @var int
     * @Type("int")
     */
    protected $DETAIL_PICTURE = 0;

    /**
     * @var string
     * @Type("string")
     */
    protected $PROPERTY_LINK = '';

    public function getBannerType() {
        $link = $this->PROPERTY_LINK;
        $type = '';
        if (strpos($link, '/catalog/') !== false && strpos($link, '.html') !== false) {
            // ссылка на товар
            $type = 'goods';
        } else if (preg_match('\/catalog\/$', $link)) {
            // ссылка на главную каталога
            $type = 'catalog';
        } else if (strpos($link, '/catalog/') !== false && strpos($link, '.html') === false) {
            // ссылка на список товаров
            $type = 'goods_list';
        } else if (strpos($link, '/news/') !== false) {
            // ссылка на новость
            $type = 'news';
        } else if (strpos($link, '/articles/') !== false) {
            // ссылка на акцию
            $type = 'action';
        }
        return $type;
    }

}
