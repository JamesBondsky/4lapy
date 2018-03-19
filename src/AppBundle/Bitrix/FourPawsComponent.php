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
     * @param $params
     *
     * @return array
     */
    public function onPrepareComponentParams($params): array
    {
        $this->withLogName(
            \sprintf(
                'component:%s',
                static::class
            )
        );

        return parent::onPrepareComponentParams($params);
    }

    /**
     * {@inheritdoc}
     *
     * @throws RuntimeException
     */
    public function executeComponent()
    {
        if ($this->startResultCache()) {

            try {
                parent::executeComponent();

                $this->prepareResult();
                $this->doAction();

                $this->includeComponentTemplate();
            } catch (\Exception $e) {
                $this->log()->error($e->getMessage());
                $this->abortResultCache();
            }
        }
    }

    /**
     * Prepare component result
     */
    abstract public function prepareResult(): void;

    /**
     * Do something actions
     */
    abstract public function doAction(): void;
}
