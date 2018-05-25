<?php

/**
 * Created by PhpStorm.
 * Date: 30.01.2018
 * Time: 14:17
 * @author      Makeev Ilya
 * @copyright   ADV/web-engineering co.
 */

namespace FourPaws\SaleBundle\Discount\Utils\Gift;

use FourPaws\SaleBundle\Discount\Gift;
use FourPaws\SaleBundle\Discount\Utils\BaseDiscountPostHandler;
use FourPaws\SaleBundle\Discount\Utils\CleanerInterface;
use FourPaws\SaleBundle\Discount\Utils\Manager;
use FourPaws\SaleBundle\Exception\NotFoundException;

/**
 * Class Cleaner
 * @package FourPaws\SaleBundle\Discount\Utils
 */
class Cleaner extends BaseDiscountPostHandler implements CleanerInterface
{
    /**
     *
     *
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \RuntimeException
     * @throws \FourPaws\SaleBundle\Exception\NotFoundException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \FourPaws\SaleBundle\Exception\InvalidArgumentException
     * @throws \Bitrix\Main\ObjectNotFoundException
     * @throws \Exception
     * @throws \FourPaws\SaleBundle\Exception\BitrixProxyException
     */
    public function processOrder(): void
    {
        $possibleGiftGroups = Gift::getPossibleGiftGroups($this->order);
        $existGifts = Manager::getExistGifts($this->order);
        /**
         * Найти лишние подарки, удалить.
         * 1. Для каждого подарка проверяем есть ли к нему акция и есть ли в этой акции он.
         */
        foreach ($existGifts as $k => $gift) {
            $notFound = false;
            if (isset($possibleGiftGroups[$gift['discountId']])) {
                $group = $possibleGiftGroups[$gift['discountId']];
                if (\count($group) !== 1) {
                    throw new \RuntimeException('TODO');
                }
                $group = current($group);
                if (!\in_array($gift['offerId'], $group['list'], true)) {
                    $notFound = true;
                }
            } else {
                $notFound = true;
            }
            if ($notFound) {
                $this->basketService->deleteOfferFromBasket(
                    $gift['basketId']
                );
                unset($existGifts[$k]);
            }
        }
        /**
         * 2. Для каждой акции проверяем количества и уменьшаем, если необходимо.
         */
        foreach ($possibleGiftGroups as $group) {
            if (\count($group) !== 1) {
                throw new \RuntimeException('TODO');
            }
            $group = current($group);
            $sumCount = 0;
            $availCount = $group['count'];
            foreach ($existGifts as $k => $gift) {
                if ($gift['discountId'] !== $group['discountId']) {
                    continue;
                }
                $sumCount += $gift['quantity'];
                if ($sumCount > $group['count']) {
                    if ($availCount > 0) {
                        $this->basketService->updateBasketQuantity(
                            $gift['basketId'],
                            $availCount
                        );
                        $availCount = 0;
                    } else {
                        try {
                            $this->basketService->deleteOfferFromBasket(
                                $gift['basketId']
                            );
                            unset($existGifts[$k]);
                        } /** @noinspection BadExceptionsProcessingInspection */ catch (NotFoundException $e) {
                            // пох
                        }
                    }
                } else {
                    $availCount -= $gift['quantity'];
                    if ($availCount < 0) {
                        $availCount = 0;
                    }
                }
            }
        }
    }
}
