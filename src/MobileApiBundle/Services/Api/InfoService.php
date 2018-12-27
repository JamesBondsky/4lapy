<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\MobileApiBundle\Services\Api;

use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use Bitrix\Main\Type\Date;
use CIBlockElement;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use FourPaws\BitrixOrm\Collection\ImageCollection;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;
use FourPaws\MobileApiBundle\Dto\Object\Info;
use FourPaws\MobileApiBundle\Enum\InfoEnum;
use Psr\Log\LoggerAwareInterface;

class InfoService implements LoggerAwareInterface
{
    use LazyLoggerAwareTrait;

    /**
     * @var ImageProcessor
     */
    private $imageProcessor;

    /**
     * @var string
     */
    private $bitrixPhpDateTimeFormat;

    public function __construct(ImageProcessor $imageProcessor)
    {
        $this->imageProcessor = $imageProcessor;
        $this->bitrixPhpDateTimeFormat = Date::convertFormatToPhp(\FORMAT_DATETIME) ?: '';
    }

    public function getInfo(string $type, string $id, array $select = [])
    {
        try {
            switch ($type) {
                case InfoEnum::ACTION:
                case InfoEnum::NEWS:
                case InfoEnum::LETTERS:
                    $return = $this->getInfoCollection($type, $id, $select)->getValues();
                    break;
                case InfoEnum::REGISTER_TERMS:
                case InfoEnum::BONUS_CARD_INFO:
                case InfoEnum::OBTAIN_BONUS_CARD:
                case InfoEnum::CONTACTS:
                case InfoEnum::ABOUT:
                    $return = $this->getInfoItem($type, $id, $select);
                    break;

                default:
                    throw new \RuntimeException(sprintf('No such method to get %s type', $type));
            }
        } catch (\Exception $exception) {
            $return = new ArrayCollection();
            $this->log()->error($exception->getMessage());
        }
        return $return;
    }

    protected function getInfoCollection(string $type, string $id, array $select = []): Collection
    {
        try {
            switch ($type) {
                case InfoEnum::ACTION:
                    $collection = $this->getActions($id, $select);
                    break;
                case InfoEnum::NEWS:
                    $collection = $this->getNews($id, $select);
                    break;
                case InfoEnum::LETTERS:
                    $collection = $this->getArticles($id, $select);
                    break;
                default:
                    throw new \RuntimeException(sprintf('No such method to get %s type', $type));
            }
        } catch (\Exception $exception) {
            $collection = new ArrayCollection();
            $this->log()->error($exception->getMessage());
        }
        return $collection->map(function (Info $info) use ($type) {
            $info->setType($type);
            return $info;
        });
    }

    protected function getInfoItem(string $type, string $id, array $select = []): Info
    {
        $info = new Info();
        try {
            switch ($type) {
                case InfoEnum::BONUS_CARD_INFO:
                    // toDo move to iblocks
                    $info
                        ->setId(1920618)
                        ->setType('bonus_card_info')
                        ->setName('Информация по бонусной карте')
                        ->setDetailText("&lt;p style=&quot;text-align: center;&quot;&gt;\r\n &lt;b&gt;&lt;span style=&quot;color: #000000;&quot;&gt;Бонусная программа Четыре Лапы - просто и понятно!&lt;/span&gt;&lt;/b&gt;\r\n&lt;/p&gt;\r\n&lt;p style=&quot;text-align: center;&quot;&gt;\r\n&lt;/p&gt;\r\n&lt;p style=&quot;text-align: center;&quot;&gt;\r\n\t 1 бонус = 1 рубль\r\n&lt;/p&gt;\r\n&lt;p style=&quot;text-align: left;&quot;&gt;\r\n\t Размер начисления бонусов зависит от суммы накоплений на карте:\r\n&lt;/p&gt;\r\n От 500 рублей - 3% от стоимости покупки&lt;br&gt;\r\n От 9000 рублей - 4% от стоимости покупки&lt;br&gt;\r\n От 19000 рублей - 5% от стоимости покупки&lt;br&gt;\r\n От 39000 рублей - 6% от стоимости покупки&lt;br&gt;\r\n От 59000 рублей - 7% от стоимости покупки&lt;br&gt;&lt;br&gt;&lt;p&gt;\r\n&lt;/p&gt;\r\n&lt;p style=&quot;text-align: left;&quot;&gt;\r\n\t * Бонусы можно тратить на любые товары, включая акционные. \r\n&lt;/p&gt;\r\n&lt;p style=&quot;text-align: left;&quot;&gt;\r\n&lt;/p&gt;\r\n&lt;p style=&quot;text-align: left;&quot;&gt;\r\n\t ** Бонусами можно оплатить до 90% покупки. \r\n&lt;/p&gt;\r\n&lt;p style=&quot;text-align: left;&quot;&gt;\r\n&lt;/p&gt;\r\n&lt;p style=&quot;text-align: left;&quot;&gt;\r\n\t *** Персональные бонусы и скидки для участников программы.\r\n&lt;/p&gt;\r\n&lt;p style=&quot;text-align: center;&quot;&gt;\r\n &lt;br&gt;&lt;/p&gt;\r\n&lt;p&gt;\r\n&lt;/p&gt;\n");
                    break;

                case InfoEnum::OBTAIN_BONUS_CARD:
                    // toDo move to iblocks
                    $info
                        ->setId(1942211)
                        ->setType('obtain_bonus_card')
                        ->setName('Как получить карту')
                        ->setDetailText("&lt;div&gt;\r\n&lt;/div&gt;\r\n &lt;br&gt;&lt;p style=&quot;text-align: center;&quot;&gt;\r\n &lt;b&gt;&lt;span style=&quot;color: #000000;&quot;&gt;Больше покупок - больше бонусов!&lt;/span&gt;&lt;/b&gt;\r\n&lt;/p&gt;\r\n&lt;p style=&quot;text-align: center;&quot;&gt;\r\n&lt;/p&gt;\r\n&lt;p style=&quot;text-align: center;&quot;&gt;\r\n &lt;b&gt;&lt;span style=&quot;color: #f16522;&quot;&gt;1 бонус = 1 рубль&lt;/span&gt;&lt;/b&gt;\r\n&lt;/p&gt;\r\n&lt;p style=&quot;text-align: left;&quot;&gt;\r\n&lt;/p&gt;\r\n&lt;p style=&quot;text-align: justify;&quot;&gt;\r\n &lt;span style=&quot;font-size: 24pt;&quot;&gt;Воспользуйтесь всеми преимуществами нашей бонусной программы. Для того, чтобы начать копить бонусы, достаточно совершить единовременную покупку в Четыре Лапы на сумму от &lt;b&gt;&lt;span style=&quot;color: #f16522;&quot;&gt;500 рублей&lt;/span&gt;&lt;/b&gt; и зарегистрировать карту в магазине или на нашем сайте.&lt;/span&gt;\r\n&lt;/p&gt;\r\n&lt;p style=&quot;text-align: justify;&quot;&gt;\r\n &lt;span style=&quot;font-size: 24pt;&quot;&gt;Карта дает право на получение бонусов с любой покупки, за исключением акционных товаров. &lt;/span&gt;\r\n&lt;/p&gt;\r\n&lt;p style=&quot;text-align: justify;&quot;&gt;\r\n &lt;span style=&quot;font-size: 24pt;&quot;&gt;Делайте заказы, копите бонусы и используйте их для оплаты до 90% следующих покупок.&lt;/span&gt;\r\n&lt;/p&gt;\r\n&lt;div&gt;\r\n &lt;br&gt;&lt;br&gt;&lt;br&gt;&lt;/div&gt;\r\n&lt;p style=&quot;text-align: left;&quot;&gt;\r\n&lt;/p&gt;\r\n&lt;p style=&quot;text-align: center;&quot;&gt;\r\n &lt;br&gt;&lt;/p&gt;\r\n&lt;p&gt;\r\n&lt;/p&gt;\n");
                    break;

                case InfoEnum::CONTACTS:
                    // toDo move to iblocks
                    $info
                        ->setId(1919979)
                        ->setType('contacts')
                        ->setName('Контакты')
                        ->setDetailText("&lt;h3&gt;Маркетинг и реклама:&lt;/h3&gt;\r\n  &lt;p&gt;   E-mail: &lt;a href=&quot;mailto:marketing@4lapy.ru&quot;&gt;marketing@4lapy.ru &lt;/a&gt;  &lt;/p&gt;\r\n  &lt;br&gt;&lt;h3&gt;Поставщикам:&lt;/h3&gt;\r\n  &lt;p&gt; \tE-mail: &lt;a href=&quot;mailto:SPastukhov@4lapy.ru&quot;&gt;SPastukhov@4lapy.ru &lt;/a&gt;  &lt;/p&gt;\r\n  &lt;br&gt;&lt;h3&gt;Арендодателям:&lt;/h3&gt;\r\n  &lt;p&gt;E-mail: &lt;a href=&quot;mailto:rent@4lapy.ru&quot;&gt;rent@4lapy.ru&lt;/a&gt;&lt;/p&gt;\r\n  &lt;br&gt;&lt;h3&gt;ТЕЛЕФОН ЦЕНТРАЛЬНОГО ОФИСА:&lt;/h3&gt; \r\n  &lt;p&gt;+7 (495) 221-72-25&lt;/p&gt;\r\n  &lt;br&gt;&lt;h3&gt;ИНФОРМАЦИЯ О ЮР.ЛИЦЕ&lt;/h3&gt;\r\n  &lt;p&gt;ООО &quot;ЗУМ+&quot;  &lt;/p&gt;\r\n  &lt;p&gt;ОГРН: 1095040007370  &lt;/p&gt;\r\n  &lt;p&gt;Юридический адрес: 140180, Московская область, г. Жуковский, ул. Мичурина д.9 &lt;/p&gt;\n");
                    break;

                case InfoEnum::ABOUT:
                    // toDo move to iblocks
                    $info
                        ->setId(1919980)
                        ->setType('about')
                        ->setName('О компании')
                        ->setDetailText("&lt;h1&gt;О компании&lt;/h1&gt;\r\n&lt;p style=&quot;text-align: justify;&quot;&gt;\r\n\tКомпания &quot;Четыре Лапы&quot; более 20 лет заботится о домашних питомцах. Сегодня мы занимаем одно из лидирующих положений на рынке, создавая лучшее предложение для питомцев и их хозяев, в более чем 150 магазинах сети в 15 регионах России. Наша миссия - заботиться о полноценной жизни питомцев. Наш долг - поддерживать Вас и Ваших домашних любимцев, чтобы их жизнь была здоровой, долгой, радостной и гармоничной.\r\n&lt;/p&gt;\r\n&lt;p style=&quot;text-align: justify;&quot;&gt;\r\n &lt;br&gt;&lt;/p&gt;\r\n&lt;p style=&quot;text-align: justify;&quot;&gt;\r\n\t Мы сотрудничаем с поставщиками, контролируем качество товаров, чтобы наше предложение всегда поддерживалось на неизменно высоком уровне.\r\n&lt;/p&gt;\r\n&lt;p style=&quot;text-align: justify;&quot;&gt;\r\n &lt;br&gt;&lt;/p&gt;\r\n&lt;p style=&quot;text-align: justify;&quot;&gt;\r\n\t В магазинах &quot;Четыре Лапы&quot; работают профессионалы, любящие и знающие свое дело, которые всегда готовы помочь советом.\r\n&lt;/p&gt;\r\n&lt;p style=&quot;text-align: justify;&quot;&gt;\r\n &lt;br&gt;&lt;/p&gt;\r\n&lt;p style=&quot;text-align: justify;&quot;&gt;\r\n\t Приходите и задайте свой вопрос нашему ветеринарному врачу, получите консультацию по кормлению и профилактике здоровья питомца. Наши специалисты в магазине имеют высокую квалификацию, Вы всегда можете рассчитывать на их помощь в подборе того, что нужно Вашему любимцу. С нами каждый человек обретает уверенность, что его домашний питомец будет обеспечен всем необходимым для счастливой и здоровой жизни.\r\n&lt;/p&gt;\r\n&lt;p style=&quot;text-align: justify;&quot;&gt;\r\n &lt;br&gt;&lt;/p&gt;\r\n&lt;p style=&quot;text-align: justify;&quot;&gt;\r\n\t С нами удобно! Вы можете заказать товар удобным для Вас способом: в магазине, по телефону или через Интернет; получить его в магазине или с доставкой на дом. Для наших постоянных покупателей в магазинах действует бонусная программа, позволяющая совершать покупки более выгодно. Каждый месяц мы проводим акции: дарим скидки, бонусы, подарки.\r\n&lt;/p&gt;\r\n&lt;p style=&quot;text-align: justify;&quot;&gt;\r\n &lt;br&gt;&lt;/p&gt;\r\n&lt;p style=&quot;text-align: justify;&quot;&gt;\r\n\t Мы гордимся тем, что делаем. Наша работа помогает сделать этот мир лучше, потому что общение с домашними питомцами приносит радостные эмоции и ощущение полноты жизни.\r\n&lt;/p&gt;\r\n&lt;p style=&quot;text-align: justify;&quot;&gt;\r\n &lt;br&gt;&lt;/p&gt;\r\n&lt;p style=&quot;text-align: justify;&quot;&gt;\r\n\t Выберите свой зоомагазин «Четыре Лапы». Мы всегда рады видеть Вас!\r\n&lt;/p&gt;\r\n&lt;p style=&quot;text-align: justify;&quot;&gt;\r\n&lt;/p&gt;\n");
                    break;

                default:
                    throw new \RuntimeException(sprintf('No such method to get %s type', $type));
            }
        } catch (\Exception $exception) {
            $this->log()->error($exception->getMessage());
        }
        return $info;
    }

    /**
     * @param string $id
     *
     * @param array  $select
     *
     * @throws \Adv\Bitrixtools\Exception\IblockNotFoundException
     * @return Collection|Info[]
     */
    protected function getNews(string $id = '', array $select = []): Collection
    {
        $criteria = [
            'ACTIVE'    => 'Y',
            'IBLOCK_ID' => IblockUtils::getIblockId(IblockType::PUBLICATION, IblockCode::NEWS),
        ];

        if ($id) {
            $criteria['ID'] = $id;
        }

        $order = [
            'ACTIVE_FROM' => 'DESC',
            'SORT'        => 'ASC',
        ];

        $select = $select ?: [
            'ID',
            'NAME',
            'DATE_ACTIVE_FROM',
            'PREVIEW_TEXT',
            'PREVIEW_PICTURE',
            'CODE',
            'CANONICAL_PAGE_URL',
            'DETAIL_TEXT',
        ];

        return $this->find($criteria, $order, $select, $id ? 1 : 50);
    }

    /**
     * @param string $id
     *
     * @param array  $select
     *
     * @throws \Adv\Bitrixtools\Exception\IblockNotFoundException
     * @return Collection|Info[]
     */
    protected function getArticles(string $id = '', array $select = []): Collection
    {
        $criteria = [
            'ACTIVE'    => 'Y',
            'IBLOCK_ID' => IblockUtils::getIblockId(IblockType::PUBLICATION, IblockCode::ARTICLES),
        ];

        if ($id) {
            $criteria['ID'] = $id;
        }

        $order = [
            'ACTIVE_FROM' => 'DESC',
            'SORT'        => 'ASC',
        ];

        $select = $select ?: [
            'ID',
            'NAME',
            'DATE_ACTIVE_FROM',
            'PREVIEW_TEXT',
            'PREVIEW_PICTURE',
            'CODE',
            'CANONICAL_PAGE_URL',
            'DETAIL_TEXT',
        ];

        return $this->find($criteria, $order, $select, $id ? 1 : 50);
    }

    /**
     * @param string $id
     *
     * @param array  $select
     *
     * @throws \Adv\Bitrixtools\Exception\IblockNotFoundException
     * @return ArrayCollection|Collection
     */
    protected function getActions(string $id, array $select = []): Collection
    {
        $criteria = [
            'ACTIVE'    => 'Y',
            'IBLOCK_ID' => IblockUtils::getIblockId(IblockType::PUBLICATION, IblockCode::SHARES),
        ];

        if ($id) {
            $criteria['ID'] = $id;
        }

        $order = [
            'ID' => 'DESC',
        ];

        $select = $select ?: [
            'ID',
            'DATE_ACTIVE_FROM',
            'DATE_ACTIVE_TO',
            'NAME',
            'PREVIEW_TEXT',
            'PREVIEW_PICTURE',
            'DETAIL_TEXT',
            'CANONICAL_PAGE_URL',
            'SUB_ITEMS',
        ];

        return $this->find($criteria, $order, $select, $id ? 1 : 50);
    }

    protected function find(
        array $criteria = [],
        array $orderBy = [],
        array $select = [],
        int $limit = 50
    ) {
        $items = [];
        $dbResult = CIBlockElement::GetList($orderBy, $criteria, false, ['nTopCount' => $limit], $select);
        while ($dbItem = $dbResult->GetNext()) {
            $items[$dbItem['ID']] = $dbItem;
        }

        $imagesIds = [];
        if (\in_array('PREVIEW_PICTURE', $select, true)) {
            $imagesIds = array_map(function ($item) {
                return $item['PREVIEW_PICTURE'] ?? '';
            }, $items);
            $imagesIds = array_filter($imagesIds);
        }
        $imageCollection = ImageCollection::createFromIds($imagesIds);

        $infoItems = (new ArrayCollection($items))
            ->map(function ($item) use ($imageCollection) {
                $apiView = new Info();
                if ($item['ID'] ?? null) {
                    $apiView->setId((string)$item['ID']);
                }

                if ($item['NAME'] ?? null) {
                    $apiView->setName((string)$item['NAME']);
                }

                if ($item['PREVIEW_TEXT'] ?? null) {
                    $apiView->setPreviewText((string)$item['PREVIEW_TEXT']);
                }

                if ($item['DETAIL_TEXT'] ?? null) {
                    $apiView->setDetailText((string)$item['DETAIL_TEXT']);
                }

                if ($item['CANONICAL_PAGE_URL'] ?? null) {
                    $apiView->setUrl((string)$item['CANONICAL_PAGE_URL']);
                }

                if ($item['PREVIEW_PICTURE'] ?? null) {
                    $apiView->setIcon($this->imageProcessor->findImage($item['PREVIEW_PICTURE'], $imageCollection));
                }

                if ($item['DATE_ACTIVE_FROM'] ?? null) {
                    $dateTime = \DateTime::createFromFormat(
                        $this->bitrixPhpDateTimeFormat,
                        $item['DATE_ACTIVE_FROM']
                    );
                    $apiView->setDateFrom($dateTime ?: null);
                }

                if ($item['DATE_ACTIVE_TO'] ?? null) {
                    $dateTime = \DateTime::createFromFormat(
                        $this->bitrixPhpDateTimeFormat,
                        $item['DATE_ACTIVE_TO']
                    );
                    $apiView->setDateTo($dateTime ?: null);
                }

                return $apiView;
            });
        return $infoItems;
    }
}
