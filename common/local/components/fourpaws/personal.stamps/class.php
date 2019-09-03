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
        $this->stampService = $container->get('stamp.service');
    }

	/**
	 * @param $params
	 * @return array
	 */
    public function onPrepareComponentParams($params): array
    {
        $params['MARK_RATE'] = $this->stampService::MARK_RATE;
        $params['MARKS_PER_RATE'] = $this->stampService::MARKS_PER_RATE;
        $params['PRODUCTS_XML_ID'] = array_keys($this->stampService::EXCHANGE_RULES); //TODO do $params['EXCHANGE_RULES'] (to show them on page) instead of $params['PRODUCTS_XML_ID']

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
    }
}
