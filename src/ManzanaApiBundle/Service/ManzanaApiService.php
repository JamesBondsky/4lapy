<?php

namespace FourPaws\ManzanaApiBundle\Service;

use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use Bitrix\Main\Type\DateTime;
use FourPaws\App\Application as App;
use FourPaws\Helpers\PhoneHelper;
use FourPaws\ManzanaApiBundle\Dto\Object\Coupon;
use FourPaws\ManzanaApiBundle\Dto\Object\CouponIssue;
use FourPaws\ManzanaApiBundle\Dto\Object\Message;
use FourPaws\ManzanaApiBundle\Dto\Response\CouponsResponse;
use FourPaws\ManzanaApiBundle\Exception\InvalidArgumentException;
use FourPaws\PersonalBundle\Exception\AlreadyExistsException;
use FourPaws\PersonalBundle\Exception\InvalidArgumentException as PersonalBundleInvalidArgumentException;
use FourPaws\UserBundle\Exception\NotFoundException;
use FourPaws\UserBundle\Service\UserSearchInterface;
use FourPaws\UserBundle\Service\UserService;
use Throwable;

/**
 * Class ManzanaApiService
 *
 * @package FourPaws\ManzanaApiBundle\Service
 * @bxnolanginspection
 */
class ManzanaApiService
{
    use LazyLoggerAwareTrait;

    public const RESPONSE_STATUSES = [
        'success'        => [
            'code'    => 200,
            'message' => 'Успешно'
        ],
        'success_empty'  => [
            'code'    => 204,
            'message' => 'Успешно, но тело ответа пустое'
        ],
        'syntax_error'   => [
            'code'    => 400,
            'message' => 'В запросе синтаксическая ошибка'
        ],
        'unauthorized'   => [
            'code'    => 401,
            'message' => 'Для доступа к запрашиваемому ресурсу требуется аутентификация'
        ],
        'internal_error' => [
            'code'    => 500,
            'message' => 'Внутренняя ошибка сервера. Обратитесь к администратору сайта'
        ]
    ];

    /**
     * @param CouponIssue[] $couponIssues
     * @return CouponsResponse
     */
    public function addOrUpdateCouponIssue(array $couponIssues): CouponsResponse
    {
        if (!$couponIssues) {
            throw new InvalidArgumentException(InvalidArgumentException::ERRORS[2], 2);
        }

        $personalOffersService = App::getInstance()->getContainer()->get('personal_offers.service');

        $result = [];
        foreach ($couponIssues as $issue) {
            try {
                $personalOffersService->addPersonalOffer($issue->getRuleCode(), $issue->getDescription());

                $result[] = (new Message())
                    ->setMessageId($issue->getMessageId())
                    ->setMessageStatus('ok')
                ;
            } catch (AlreadyExistsException $e) {
                $message = $e->getMessage();
                if ($e->getCode() === 1) {
                    $message = 'Выпуск купонов уже создан ранее';
                }
                $result[] = (new Message())
                    ->setMessageId($issue->getMessageId())
                    ->setMessageStatus('error')
                    ->setMessageText($message)
                ;
            } catch (Throwable $e) {
                $result[] = (new Message())
                    ->setMessageId($issue->getMessageId())
                    ->setMessageStatus('error')
                    ->setMessageText('Что-то пошло не так')
                ;
                $this->log()->critical(__METHOD__ . ' exception: ' . $e->getMessage(), [$e->getTrace()]);
            }
        }

        return (new CouponsResponse())->setMessages($result);
    }

    /**
     * @param Coupon[] $coupons
     * @return CouponsResponse
     */
    public function addCoupons(array $coupons): CouponsResponse
    {
        if (!$coupons) {
            throw new InvalidArgumentException(InvalidArgumentException::ERRORS[1], 1);
        }

        $personalOffersService = App::getInstance()->getContainer()->get('personal_offers.service');

        $ruleCodes = array_filter(array_unique(array_map(static function(Coupon $coupon) {
            try {
                return $coupon->getRuleCode();
            } catch (Throwable $e) {
                $result[] = (new Message())
                    ->setMessageId($coupon->getMessageId())
                    ->setMessageStatus('error')
                    ->setMessageText('Неверный ruleCode')
                ;
                return '';
            }
        }, $coupons)));

        $offers = [];
        if ($ruleCodes) {
            $offersCollection = $personalOffersService->getActiveOffers(['NAME' => $ruleCodes]);
            if (!$offersCollection->isEmpty()) {
                $offers = array_column($offersCollection->getValues(), 'ID', 'NAME');
            }
        }

        $result = [];
        foreach ($coupons as $coupon) {
            try {
                try {
                    $coupon->getRuleCode();
                    $coupon->getPromoCode();
                    $coupon->getStartDate();
                    $coupon->getEndDate();
                    $coupon->getPhone();
                    $coupon->getCouponId();
                } catch (Throwable $e) {
                    $result[] = (new Message())
                        ->setMessageId($coupon->getMessageId())
                        ->setMessageStatus('error')
                        ->setMessageText('Заполнены не все необходимые поля')
                    ;
                    continue;
                }

                if (!$offerId = $offers[$coupon->getRuleCode()]) {
                    $result[] = (new Message())
                        ->setMessageId($coupon->getMessageId())
                        ->setMessageStatus('error')
                        ->setMessageText('Не найден выпуск купонов с указанным ruleCode')
                    ;
                    continue;
                }

                /** @var UserService $userService */
                $userService = App::getInstance()->getContainer()->get(UserSearchInterface::class);
                try {
                    $normalizedPhone = PhoneHelper::normalizePhone($coupon->getPhone());
                    $userId = $userService->findOneByPhone($normalizedPhone)->getId();
                } catch (NotFoundException $e) {
                    $result[] = (new Message())
                        ->setMessageId($coupon->getMessageId())
                        ->setMessageStatus('error')
                        ->setMessageText('Не найден пользователь с указанным номером телефона')
                    ;
                    continue;
                }


                $couponsArray = [
                    $coupon->getPromoCode() => [
                        'users' => [
                            $userId
                        ],
                        'coupon' => [
                            //'discountValue' => ,
                            'dateTimeActiveFrom' => DateTime::createFromTimestamp($coupon->getStartDate()->getTimestamp()),
                            'dateTimeActiveTo'   => DateTime::createFromTimestamp($coupon->getEndDate()->getTimestamp()),
                            'manzanaId'   => $coupon->getCouponId(),
                        ],
                    ],
                ];
                $personalOffersService->importOffers($offerId, $couponsArray, false);

                $result[] = (new Message())
                    ->setMessageId($coupon->getMessageId())
                    ->setMessageStatus('ok')
                ;
            } catch (Throwable $e) {
                $result[] = (new Message())
                    ->setMessageId($coupon->getMessageId())
                    ->setMessageStatus('error')
                    ->setMessageText('Что-то пошло не так')
                ;
                $this->log()->critical(__METHOD__ . ' exception: ' . $e->getMessage(), [$e->getTrace()]);
            }
        }

        return (new CouponsResponse())->setMessages($result);
    }

    /**
     * @param Coupon[] $coupons
     * @return CouponsResponse
     * @throws InvalidArgumentException
     */
    public function setCouponsUsed(array $coupons): CouponsResponse
    {
        if (!$coupons) {
            throw new InvalidArgumentException(InvalidArgumentException::ERRORS[1], 1);
        }

        $personalOffersService = App::getInstance()->getContainer()->get('personal_offers.service');

        $result = [];
        foreach ($coupons as $coupon) {
            try {
                try {
                    $personalOffersService->setUsedStatusByManzanaId($coupon->getCouponId());

                    $result[] = (new Message())
                        ->setMessageId($coupon->getMessageId())
                        ->setMessageStatus('ok')
                    ;
                } catch (PersonalBundleInvalidArgumentException $e) {
                    if ($e->getCode() === 1) {
                        $result[] = (new Message())
                            ->setMessageId($coupon->getMessageId())
                            ->setMessageStatus('error')
                            ->setMessageText('Не указан ID купона')
                        ;
                    } else {
                        throw new PersonalBundleInvalidArgumentException($e->getMessage(), $e->getCode());
                    }
                }
            } catch (Throwable $e) {
                $result[] = (new Message())
                    ->setMessageId($coupon->getMessageId())
                    ->setMessageStatus('error')
                    ->setMessageText('Что-то пошло не так')
                ;
                $this->log()->critical(__METHOD__ . ' exception: ' . $e->getMessage(), [$e->getTrace()]);
            }
        }

        return (new CouponsResponse())->setMessages($result);
    }
}
