<?php

namespace FourPaws\AppBundle\Bitrix;


use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use Psr\Log\LoggerAwareInterface;
use RuntimeException;

/**
 * Class FourPawsComponent
 *
 * Default component for current project
 *
 * @package FourPaws\AppBundle\Bitrix
 */
abstract class FourPawsComponent extends \CBitrixComponent implements LoggerAwareInterface
{
    use LazyLoggerAwareTrait;

    /**
     * @var string
     */
    private $templatePage = '';

    /**
     * @param $params
     *
     * @return array
     */
    public function onPrepareComponentParams($params): array
    {
        $this->withLogName(
            \sprintf(
                'component_%s',
                static::class
            )
        );

        $params['return_result'] = $params['return_result'] === true || $params['return_result'] === 'Y';

        return parent::onPrepareComponentParams($params);
    }

    /**
     * {@inheritdoc}
     *
     * @throws RuntimeException
     * @return null|array
     */
    public function executeComponent(): ?array
    {
        if ($eee = $this->startResultCache()) {

            try {
                parent::executeComponent();

                $this->prepareResult();

                $this->includeComponentTemplate($this->templatePage);
            } catch (\Exception $e) {
                $this->log()->error(sprintf('%s: %s', \get_class($e), $e->getMessage()), [
                    'trace' => $e->getTrace()
                ]);
                $this->abortResultCache();
            }

            $this->setResultCacheKeys($this->getResultCacheKeys());
        }

        if ($this->arParams['return_result']) {
            return $this->arResult;
        }

        return null;
    }

    /**
     * Prepare component result
     */
    abstract public function prepareResult(): void;

    /**
     * @return array
     */
    public function getResultCacheKeys(): array
    {
        return [];
    }

    /**
     * @param string $page
     */
    protected function setTemplatePage(string $page) {
        $this->templatePage = $page;
    }
}
