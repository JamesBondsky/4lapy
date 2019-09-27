<?php

use FourPaws\App\Application as App;
use FourPaws\AppBundle\Bitrix\FourPawsComponent;
use FourPaws\PersonalBundle\Service\StampService;
use FourPaws\UserBundle\Exception\NotAuthorizedException;
use FourPaws\UserBundle\Service\CurrentUserProviderInterface;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

/** @noinspection AutoloadingIssuesInspection */

class CStampsProgressBar extends FourPawsComponent
{
    /** @var object CurrentUserProviderInterface */
    protected $currentUserProvider;
    /** @var StampService */
    protected $stampService;


    /**
     * @param null|\CBitrixComponent $component
     *
     * @throws \RuntimeException
     * @throws ServiceNotFoundException
     */
    public function __construct(CBitrixComponent $component = null)
    {
        parent::__construct($component);
        $container = App::getInstance()->getContainer();
        $this->currentUserProvider = $container->get(CurrentUserProviderInterface::class);
        $this->stampService = $container->get(StampService::class);
    }

    /**
     * @throws Exception
     */
    public function prepareResult(): void
    {
        $this->arResult['ACTIVE_STAMPS_COUNT'] = 0;

        try {
            $userId = $this->currentUserProvider->getCurrentUserId();

            try {
                $this->arResult['ACTIVE_STAMPS_COUNT'] = $this->stampService->getActiveStampsCount(); //TODO переделать(?) на вывод значения, сохраненного в профиле пользователя (для этого нужно его заранее асинхронно обновлять)ddd
            } catch (Exception $e) {
            }
        } catch (NotAuthorizedException $e) {
            //define('NEED_AUTH', true);
            //return;
        }

        $maxStampsCount = 0;

        $this->arResult['STAMP_LEVELS'] = [];

        $this->arResult['MAX_DISCOUNT'] = 0;

        $this->arResult['CURRENT_DISCOUNT'] = $this->stampService->getCurrentDiscount();
        $this->arResult['NEXT_DISCOUNT'] = $this->stampService->getNextDiscount();
        $this->arResult['NEXT_DISCOUNT_STAMPS_NEED'] = $this->stampService->getNextDiscountStampsNeed();


        foreach ($this->stampService->getStampLevels() as $stampLevel) {
            if ($stampLevel['stamps'] > $maxStampsCount) {
                $maxStampsCount = $stampLevel['stamps'];
                $this->arResult['MAX_DISCOUNT'] = $stampLevel['discount'];
            }

            $this->arResult['STAMP_LEVELS'][$stampLevel['stamps']] = $stampLevel['discount'];
        }

        $this->arResult['PROGRESS_BAR'] = [];


        for ($i = 1; $i <= $maxStampsCount; $i++) {
            $this->arResult['PROGRESS_BAR'][] = [
                'BONUS' => $this->arResult['STAMP_LEVELS'][$i] ?? false,
                'AVAILABLE' => ($this->arResult['ACTIVE_STAMPS_COUNT'] >= $i),
            ];
        }
    }
}
