<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use Bitrix\Main\SystemException;
use FourPaws\App\Application as App;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\CatalogBundle\Service\OftenSeekService;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

/** @noinspection AutoloadingIssuesInspection */
class CFourPawsFoodSelectionComponent extends CBitrixComponent
{
    /** @var OftenSeekService $oftenSeekService */
    private $oftenSeekService;

    /**
     * CFourPawsFoodSelectionComponent constructor.
     *
     * @param \CBitrixComponent|null $component
     *
     * @throws ServiceNotFoundException
     * @throws SystemException
     * @throws \RuntimeException
     * @throws ServiceCircularReferenceException
     */
    public function __construct(\CBitrixComponent $component = null)
    {
        parent::__construct($component);
        try {
            $container = App::getInstance()->getContainer();
        } catch (ApplicationCreateException $e) {
            $logger = LoggerFactory::create('component');
            $logger->error(sprintf('Component execute error: %s', $e->getMessage()));
            /** @noinspection PhpUnhandledExceptionInspection */
            throw new SystemException($e->getMessage(), $e->getCode(), $e->getFile(), $e->getLine(), $e);
        }

        $this->oftenSeekService = $container->get('often_seek.service');
    }

    /**
     * {@inheritdoc}
     * @throws \RuntimeException
     */
    public function executeComponent()
    {
        $this->setFrameMode(true);

        if ($this->startResultCache()) {
            $this->arResult['ITEMS'] = $this->oftenSeekService->get;
            $this->includeComponentTemplate();
        }

        return true;
    }
}
