<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 03.06.18
 * Time: 12:52
 */

namespace FourPaws\SaleBundle\AgentController;


use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Bitrix\Main\Type\DateTime;
use Bitrix\Sale\BasketItem;
use Bitrix\Sale\Internals\BasketTable;
use FourPaws\App\Application;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\External\Exception\ExpertsenderBasketEmptyException;
use FourPaws\External\Exception\ExpertsenderEmptyEmailException;
use FourPaws\External\Exception\ExpertsenderServiceException;
use FourPaws\External\Exception\ExpertsenderUserNotFoundException;
use FourPaws\External\ExpertsenderService;
use FourPaws\SaleBundle\Service\BasketService;

class ForgotBasketController
{
    /**
     * переодический агент - изначально раз в час
     * @return string
     */
    public function sendEmailByOldBasketAfter3Days(): string
    {
        /** получаем неизмененные итемы не привязанные к заказу и с пользователем
         * искдючаем неавторизованные корзины
         */
        $date = DateTime::createFromTimestamp(time());
        $date->add('- 3 days'); //-3 дня
        $logger = LoggerFactory::create('forgot_basket_agent_3days');
        $returnString = '\\' . __METHOD__ . '();';
        try {
            $res = BasketTable::query()
                ->where('DATE_UPDATE', '<=', $date)
                ->where('USER.ID', '>', 0)
                ->whereNull('ORDER_ID')
                ->setSelect(['FUSER_ID'])
                ->exec();
        } catch (ObjectPropertyException|ArgumentException|SystemException|\Exception $e) {
            $logger->error('Ошибка при получении корзины ' . $e->getMessage(), $e->getTrace());
            return $returnString;
        }
        $fUserIds = [];
        while ($basketItem = $res->fetch()) {
            $fUserIds[] = $basketItem['FUSER_ID'];
        }
        if(empty($fUserIds)){
            /** Обновлять нечего */
            return $returnString;
        }
        $fUserIds = array_unique($fUserIds);
        /** ищем среди найденых корзин обновленные элементы */
        $updatedFUserIds = [];
        try {
            $res = BasketTable::query()
                ->where('DATE_UPDATE', '>', $date)
                ->whereIn('FUSER_ID', $fUserIds)
                ->whereNull('ORDER_ID')
                ->setSelect(['FUSER_ID'])
                ->exec();
        } catch (ObjectPropertyException|ArgumentException|SystemException|\Exception $e) {
            $logger->error('Ошибка при получении корзины ' . $e->getMessage(), $e->getTrace());
            return $returnString;
        }
        while ($basketItem = $res->fetch()) {
            $updatedFUserIds[] = $basketItem['FUSER_ID'];
        }
        $updatedFUserIds = array_unique($updatedFUserIds);
        /** находим расхождение массивов - это и есть корзины для отправки писем */
        $sendFuserIds = array_diff($fUserIds, $updatedFUserIds);
        if (!empty($sendFuserIds)) {
            /** @var BasketService $basketService */
            try {
                $container = Application::getInstance()->getContainer();
            } catch (ApplicationCreateException $e) {
                $logger->error('Ошибка при получении контейнера ', $e);
                return $returnString;
            }
            $basketService = $container->get(BasketService::class);
            /** @var ExpertsenderService $expertSenderService */
            $expertSenderService = $container->get('expertsender.service');
            $curDate = DateTime::createFromTimestamp(time());
            foreach ($sendFuserIds as $sendFuserId) {
                $userBasket = $basketService->getBasket(true, $sendFuserId);
                if($userBasket->count() > 0 && \count($userBasket->getBasketItems()) > 0) {
                    try {
                        $res = $expertSenderService->sendForgotBasket($userBasket,
                            ExpertsenderService::FORGOT_BASKET_AFTER_TIME);
                        if ($res) {
                            /** @var BasketItem $basketItem */
                            foreach ($userBasket->getBasketItems() as $basketItem) {
                                try {
                                    /** апдейтим дату корзины - чтобы отсчет шел как по тз после отправки первого письма */
                                    BasketTable::update($basketItem->getId(), ['DATE_UPDATE' => $curDate]);
                                } catch (\Exception $e) {
                                    $logger->error('Ошибка при обновлении дат элементов корзины ' . $e->getMessage(),
                                        $e->getTrace());
                                }
                            }
                        }
                    } catch (ArgumentException $e) {
                        $logger->error('Ошибка при получении юзера ' . $e->getMessage(), $e->getTrace());
                    } catch (ApplicationCreateException $e) {
                        $logger->error('Ошибка при получении контейнера ' . $e->getMessage(), $e->getTrace());
                        return $returnString;
                    } catch (ExpertsenderUserNotFoundException $e) {
                        $logger->info('Не найден пользователь ' . $e->getMessage(), $e->getTrace());
                    } catch (ExpertsenderEmptyEmailException|ExpertsenderBasketEmptyException $e) {
                        /** при пустой корзине или пустом email логирвоание не нужно */
                    } catch (ExpertsenderServiceException $e) {
                        $logger->error('Ошибка при отправке сообщения ' . $e->getMessage(), $e->getTrace());
                    }
                }
            }
        }
        return $returnString;
    }

    /**
     * непереодический агент, создаеся при аяксе на закрытие сайта если корзина не пустая
     *
     * @param int|string $fuserId
     */
    public function sendEmailByOldBasketAfter3Hours($fuserId): void
    {
        $fuserId = (int)$fuserId;
        if($fuserId === 0){
            return;
        }
        /** получаем неизмененные итемы не привязанные к заказу и с пользователем
         * искдючаем неавторизованные корзины
         */
        $date = DateTime::createFromTimestamp(time());
        $date->add('- 3 hours'); //-3 часа
        /** получаем измененные итемы */
        $logger = LoggerFactory::create('forgot_basket_agent_3hours');
        try {
            $res = BasketTable::query()
                ->where('DATE_UPDATE', '>', $date)
                ->where('FUSER_ID', $fuserId)
                ->whereNull('ORDER_ID')
                ->setSelect(['FUSER_ID'])
                ->setLimit(1)
                ->exec();
        } catch (ObjectPropertyException|ArgumentException|SystemException $e) {
            $logger->error('Ошибка при получении корзины ' . $e->getMessage(), $e->getTrace());
            return;
        }
        /** если нет измененных итемов посылаем письмо */
        if ($res->getSelectedRowsCount() === 0) {
            /** @var BasketService $basketService */
            try {
                $container = Application::getInstance()->getContainer();
            } catch (ApplicationCreateException $e) {
                $logger->error('Ошибка при получении контейнера ', $e);
                return;
            }
            $basketService = $container->get(BasketService::class);
            /** @var ExpertsenderService $expertSenderService */
            $expertSenderService = $container->get('expertsender.service');
            $userBasket = $basketService->getBasket(true, $fuserId);
            if($userBasket->count() > 0 && \count($userBasket->getBasketItems()) > 0) {
                try {
                    $res = $expertSenderService->sendForgotBasket($userBasket,
                        ExpertsenderService::FORGOT_BASKET_TO_CLOSE_SITE);
                    if ($res) {
                        $curDate = DateTime::createFromTimestamp(time());
                        /** @var BasketItem $basketItem */
                        foreach ($userBasket->getBasketItems() as $basketItem) {
                            try {
                                /** апдейтим дату корзины чтобы не циклился обработчик */
                                BasketTable::update($basketItem->getId(), ['DATE_UPDATE' => $curDate]);
                            } catch (\Exception $e) {
                                $logger->error('Ошибка при обновлении дат элементов корзины ' . $e->getMessage(), $e->getTrace());
                            }
                        }
                    }
                } catch (ArgumentException $e) {
                    $logger->error('Ошибка при получении юзера ' . $e->getMessage(), $e->getTrace());
                    return;
                } catch (ApplicationCreateException $e) {
                    $logger->error('Ошибка при получении контейнера ' . $e->getMessage(), $e->getTrace());
                    return;
                } catch (ExpertsenderUserNotFoundException $e) {
                    $logger->info('Не найден пользователь ' . $e->getMessage(), $e->getTrace());
                } catch (ExpertsenderEmptyEmailException|ExpertsenderBasketEmptyException $e) {
                    /** при пустой корзине или пустом email логирвоание не нужно */
                } catch (ExpertsenderServiceException $e) {
                    $logger->error('Ошибка прио отправке сообщения ' . $e->getMessage(), $e->getTrace());
                    return;
                }
            }
        }
    }
}