<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use FourPaws\App\Application as App;
use FourPaws\AppBundle\Bitrix\FourPawsComponent;
use FourPaws\External\Manzana\Exception\ExecuteErrorException;
use FourPaws\PersonalBundle\Service\StampService;
use FourPaws\UserBundle\Exception\NotAuthorizedException;
use FourPaws\UserBundle\Service\CurrentUserProviderInterface;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

/** @noinspection AutoloadingIssuesInspection */
class FourPawsPersonalCabinetStampsComponent extends FourPawsComponent
{
    /** @var array */
    protected $discountLevels;

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
     * @param $params
     * @return array
     */
    public function onPrepareComponentParams($params): array
    {
        $params['MARK_RATE'] = $this->stampService::MARK_RATE;
        $params['MARKS_PER_RATE'] = $this->stampService::MARKS_PER_RATE;


        return parent::onPrepareComponentParams($params);
    }

    /**
     * @throws Exception
     */
    public function prepareResult(): void
    {
        try {
            $userId = $this->currentUserProvider->getCurrentUserId();

            try {
                $this->arResult['ACTIVE_STAMPS_COUNT'] = $this->stampService->getActiveStampsCount(); //TODO переделать(?) на вывод значения, сохраненного в профиле пользователя (для этого нужно его заранее асинхронно обновлять)
            } catch (Exception $e) {
                $this->arResult['ACTIVE_STAMPS_COUNT'] = 0;
            }
        } catch (NotAuthorizedException $e) {
            define('NEED_AUTH', true);

            return;
        }

        $this->arResult['STAMP_LEVELS'] = [];

        $this->arResult['MAX_STAMPS_COUNT'] = 0;
        $this->arResult['MAX_DISCOUNT'] = 0;

        $this->arResult['CURRENT_DISCOUNT'] = 0;
        $this->arResult['NEXT_DISCOUNT'] = false;
        $this->arResult['NEXT_DISCOUNT_STAMPS_NEED'] = 0;

        foreach ($this->stampService->getStampLevels() as $stampLevel) {
            if ($stampLevel['stamps'] >  $this->arResult['MAX_STAMPS_COUNT']) {
                $this->arResult['MAX_STAMPS_COUNT'] = $stampLevel['stamps'];
                $this->arResult['MAX_DISCOUNT'] = $stampLevel['discount'];
            }

            if ($this->arResult['ACTIVE_STAMPS_COUNT'] > $stampLevel['stamps']) {
                $this->arResult['CURRENT_DISCOUNT'] = $stampLevel['discount'];
            }

            if (!$this->arResult['NEXT_DISCOUNT'] && ($stampLevel['stamps'] > $this->arResult['ACTIVE_STAMPS_COUNT'])) {
                $this->arResult['NEXT_DISCOUNT'] = $stampLevel['discount'];
                $this->arResult['NEXT_DISCOUNT_STAMPS_NEED'] = $stampLevel['stamps'] - $this->arResult['ACTIVE_STAMPS_COUNT'];
            }

            $this->arResult['STAMP_LEVELS'][$stampLevel['stamps']] = $stampLevel['discount'];
        }
    }
}
