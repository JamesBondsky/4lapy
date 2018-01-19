<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Adv\Bitrixtools\Tools\Log\LoggerFactory;

/** @noinspection AutoloadingIssuesInspection */
class FourPawsOrderComponent extends \CBitrixComponent
{

    protected $defaultUrlTemplates404 = [
        'auth'     => 'index.php',
        'delivery' => 'delivery/',
        'payment'  => 'payment/',
        'complete' => 'complete/',
    ];

    /** {@inheritdoc} */
    public function onPrepareComponentParams($params): array
    {
        return parent::onPrepareComponentParams($params);
    }

    /** {@inheritdoc} */
    public function executeComponent()
    {
        global $APPLICATION;
        try {
            if ($this->startResultCache()) {
                $variables = [];
                $componentPage = CComponentEngine::ParseComponentPath(
                    $this->arParams['SEF_FOLDER'],
                    $this->defaultUrlTemplates404,
                    $variables
                );

                if (!$componentPage) {
                    LocalRedirect($this->arParams['SEF_FOLDER']);
                }

                if ($this->arParams['SET_TITLE'] === 'Y') {
                    $APPLICATION->SetTitle('Оформление заказа');
                }

                $this->prepareResult();

                $this->includeComponentTemplate($componentPage);
            }
        } catch (\Exception $e) {
            try {
                $logger = LoggerFactory::create('component');
                $logger->error(sprintf('Component execute error: %s', $e->getMessage()));
            } catch (\RuntimeException $e) {
            }
        }
    }

    /**
     * @return $this
     */
    protected function prepareResult()
    {


        return $this;
    }
}
