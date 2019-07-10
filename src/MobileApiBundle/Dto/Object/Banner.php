<?php

namespace FourPaws\MobileApiBundle\Dto\Object;

use Bitrix\Main\Application;
use Bitrix\Main\SystemException;
use FourPaws\Decorators\FullHrefDecorator;
use JMS\Serializer\Annotation as Serializer;

/**
 * Объект баннер
 * Class Banner
 * @package FourPaws\MobileApiBundle\Dto\Object
 */
class Banner
{
    /**
     * id баннера
     * @Serializer\Type("int")
     * @var int
     */
    protected $id;

    /**
     * Путь до изображения баннера
     * @Serializer\Type("string")
     * @var string
     */
    protected $picture = '';

    /**
     * Время показа баннера в секундах
     * @Serializer\Type("int")
     * @var int
     */
    protected $delay = 3;

    /**
     * Заголовок баннера
     * @Serializer\Type("string")
     * @var string
     */
    protected $title = '';

    /**
     * Код раздела баннера в инфоблоке
     * @Serializer\Type("string")
     * @var string
     */
    protected $type = '';

    /**
     * Ссылка с баннера
     * @Serializer\SerializedName("target_alt")
     * @Serializer\Type("string")
     * @var string
     */
    protected $link = '';

    /**
     * Ссылка с баннера, подготовленная для мобильного приложения
     * @Serializer\SerializedName("target")
     * @Serializer\Type("string")
     * @var string
     */
    protected $preparedLink = '';


    /**
     * ID города для ссылки с баннера
     * @Serializer\SerializedName("city_id")
     * @Serializer\Type("string")
     * @var string
     */
    protected $cityId = '';

    /**
     * Имеет или нет привязку к элементу/разделу
     * @Serializer\Exclude()
     * @var bool
     */
    protected $hasElementOrSectionLink = false;



    /**
     * @param int $id
     * @return Banner
     */
    public function setId($id): Banner {
        $this->id = $id;
        return $this;
    }

    /**
     * @return int
     */
    public function getId(): int {
        return $this->id;
    }

    /**
     * @param int $fileId
     * @return Banner
     */
    public function setPicture($fileId): Banner {
        $src = \CFile::getPath($fileId);
        $this->picture = (string) new FullHrefDecorator($src);
        return $this;
    }

    /**
     * @return string
     */
    public function getPicture(): string {
        return $this->picture;
    }

    /**
     * @param int $seconds
     * @return Banner
     */
    public function setDelay($seconds): Banner {
        $this->delay = $seconds;
        return $this;
    }

    /**
     * @return int
     */
    public function getDelay(): int {
        return $this->delay;
    }

    /**
     * @param string $title
     * @return Banner
     */
    public function setTitle($title): Banner {
        $this->title = $title;
        return $this;
    }

    /**
     * @return string
     */
    public function getTitle(): string {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getType(): string {
        return $this->type ? $this->type : $this->guessBannerType();
    }

    /**
     * @param string $link
     * @param string $cityId
     * @return Banner
     */
    public function setLink($link, $cityId = ''): Banner {
        if ($link === (int)$link) {
            $this->link = $link;
        } else {
            $this->link = (string) new FullHrefDecorator($link);
            $this->cityId = $cityId;
        }
        $this->preparedLink = $this->getPreparedLink();

        return $this;
    }

    /**
     * @return string
     */
    public function getLink(): string {
        return $this->link;
    }

    /**
     * @param bool $hasElementOrSectionLink
     * @return Banner
     */
    public function setHasElementOrSectionLink(bool $hasElementOrSectionLink): Banner {
        $this->hasElementOrSectionLink = $hasElementOrSectionLink;

        return $this;
    }

    /**
     * @return bool
     */
    public function getHasElementOrSectionLink(): bool {
        return $this->hasElementOrSectionLink;
    }



    /**
     * @return string
     */
    public function getPreparedLink(): string {
        $type = $this->getType();
        $methodName = $this->getMethodNameByType($type);
        $queryData = $this->getQueryDataByType($type);

        $preparedLink = $this->link;
        if ($methodName) {
            try {
                $host = Application::getInstance()->getContext()->getRequest()->getHttpHost();
                $preparedLink = 'https://' . $host . '/api/' . $methodName . '/?' . http_build_query($queryData);

            } catch (SystemException $e) {
                // do nothing
            }
        }
        return $preparedLink;
    }

    /**
     * @return string
     */
    protected function guessBannerType() {
        $link = $this->link;
        if ($this->getHasElementOrSectionLink()) {
            if (strpos($link, '/catalog/') !== false && strpos($link, '.html') !== false) {
                // ссылка на товар
                $type = 'goods';
            } else if (strpos($link, '/catalog/') !== false && strpos($link, '.html') === false) {
                // ссылка на раздел каталога
                $type = 'catalog';
            } /* else if (strpos($link, '/catalog/') !== false && strpos($link, '.html') === false) {
                // ссылка на список товаров
                $type = 'goods_list';
            }*/ else if (strpos($link, '/news/') !== false) {
                // ссылка на новость
                $type = 'news';
            } else if (strpos($link, '/articles/') !== false) {
                // ссылка на статью
                $type = 'articles';
            } else if (strpos($link, '/shares/') !== false) {
                // ссылка на акцию
                $type = 'action';
            } else {
                // ссылка
                $type = 'browser';
            }
        } else {
            // ссылка
            $type = 'browser';
        }
        $this->type = $type;
        return $type;
    }

    /**
     * @param $type
     * @return bool|string
     */
    protected function getMethodNameByType($type) {
        $methodName = false;
        switch ($type) {
            case 'goods':
                $methodName = 'goods_item';
                break;
            case 'goods_list':
                $methodName = 'goods_list';
                break;
            case 'catalog':
                $methodName = 'categories';
                break;
            case 'news':
                $methodName = 'news';
                break;
            case 'action':
                $methodName = 'action';
                break;
        }
        return $methodName;
    }

    /**
     * @param string $type
     * @return array
     */
    protected function getQueryDataByType($type) {
        $queryData = [];

        switch ($type) {
            case 'goods':
            case 'catalog':
                $queryData = [
                    'id' => $this->link
                ];
                break;
            case 'goods_list':
                $queryData = [
                    'category_id' => $this->link
                ];
                break;
            case 'news':
            case 'action':
                $queryData = [
                    'type' => $this->getType(),
                    'info_id' => $this->link,
                ];
                break;
        }

        return $queryData;
    }
}
