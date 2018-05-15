<?php
/**
 * Created by PhpStorm.
 * Date: 27.03.2018
 * Time: 17:39
 * @author      Makeev Ilya
 * @copyright   ADV/web-engineering co.
 */

namespace FourPaws\SapBundle\Repository;

use Bitrix\Main\Entity\AddResult;
use Bitrix\Main\Entity\DeleteResult;
use Bitrix\Main\Entity\UpdateResult;
use Bitrix\Main\Error;
use Bitrix\Sale\Internals\DiscountGroupTable;
use Bitrix\Sale\Internals\DiscountTable;
use FourPaws\SapBundle\Exception\BitrixEntityProxyException;
use FourPaws\SapBundle\Exception\InvalidArgumentException;
use FourPaws\SapBundle\Model\BasketRule;
use JMS\Serializer\Serializer;

/**
 * Class DiscountRulesRepository
 * @package FourPaws\SapBundle\Repository
 */
class BasketRulesRepository
{
    /** @var Serializer */
    protected $serializer;

    protected const DEFAULT_SELECT = [
        'ID',
        'LID',
        'NAME',
        'ACTIVE_FROM',
        'ACTIVE_TO',
        'ACTIVE',
        'SORT',
        'PRIORITY',
        'LAST_DISCOUNT',
        'LAST_LEVEL_DISCOUNT',
        'XML_ID',
        'CONDITIONS',
        'ACTIONS',
    ];

    /**
     * DiscountRulesRepository constructor.
     *
     * @param Serializer $serializer
     */
    public function __construct(Serializer $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     *
     *
     * @param BasketRule $basketRule
     *
     * @throws \FourPaws\SapBundle\Exception\BitrixEntityProxyException
     */
    public function create(BasketRule $basketRule): void
    {
        $arFields = $this->serializer->toArray($basketRule);
        if ($id = \CSaleDiscount::Add($arFields)) {
            $basketRule->setId($id);
        } else {
            throw new BitrixEntityProxyException(
                (new AddResult())->addError(new Error('неизвестная ошибка CSaleDiscount::Add'))
            );
        }
    }

    /**
     *
     *
     * @param BasketRule $basketRule
     *
     * @throws \FourPaws\SapBundle\Exception\InvalidArgumentException
     * @throws \FourPaws\SapBundle\Exception\BitrixEntityProxyException
     */
    public function update(BasketRule $basketRule): void
    {
        if (!$id = $basketRule->getId()) {
            throw new InvalidArgumentException('Ошибка обновления: в сущности отсуствует ID');
        }
        $arFields = $this->serializer->toArray($basketRule);
        if (!\CSaleDiscount::Update($id, $arFields)) {
            throw new BitrixEntityProxyException(
                (new UpdateResult())->addError(new Error('неизвестная ошибка CSaleDiscount::Update'))
            );
        }
    }

    /**
     *
     *
     * @param BasketRule $basketRule
     *
     * @throws \FourPaws\SapBundle\Exception\BitrixEntityProxyException
     * @throws \FourPaws\SapBundle\Exception\InvalidArgumentException
     */
    public function delete(BasketRule $basketRule): void
    {
        if (!$id = $basketRule->getId()) {
            throw new InvalidArgumentException('Ошибка удаления: в сущности отсуствует ID');
        }
        try {
            $result = DiscountTable::delete($id);
        } catch (\Exception $e) {
            throw new BitrixEntityProxyException(
                (new DeleteResult())->addError(new Error('неизвестная ошибка DiscountTable::delete : ' . $e->getMessage()))
            );
        }
        if (!$result->isSuccess()) {
            throw new BitrixEntityProxyException($result);
        }
    }

    /**
     *
     * @param string $xmlId
     *
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\SystemException
     *
     * @return BasketRule|null
     */
    public function findOneByXmlId(string $xmlId): ? BasketRule
    {
        $result = null;
        $res = DiscountTable::getList([
            'filter' => ['=XML_ID' => $xmlId],
            'select' => self::DEFAULT_SELECT,
        ]);

        if ($elem = $res->fetch()) {
            $discountGroupRes = DiscountGroupTable::getList([
                'filter' => ['=DISCOUNT_ID' => $elem['ID']],
                'select' => ['GROUP_ID']
            ]);
            while ($discountGroup = $discountGroupRes->fetch()) {
                $elem['USER_GROUPS'][] = $discountGroup['GROUP_ID'];
            }
            /** @noinspection UnserializeExploitsInspection */
            $elem['CONDITIONS'] = unserialize($elem['CONDITIONS']);
            /** @noinspection UnserializeExploitsInspection */
            $elem['ACTIONS'] = unserialize($elem['ACTIONS']);
            $result = $this->serializer->fromArray($elem, BasketRule::class);
        }
        return $result;
    }
}