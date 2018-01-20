<?php

namespace FourPaws\App;

use Circle\RestClientBundle\CircleRestClientBundle;
use Cocur\Slugify\Bridge\Symfony\CocurSlugifyBundle;
use FOS\RestBundle\FOSRestBundle;
use FourPaws\PersonalBundle\FourPawsPersonalBundle;
use FourPaws\AppBundle\FourPawsAppBundle;
use FourPaws\CatalogBundle\FourPawsCatalogBundle;
use FourPaws\DeliveryBundle\FourPawsDeliveryBundle;
use FourPaws\SaleBundle\FourPawsSaleBundle;
use FourPaws\SapBundle\FourPawsSapBundle;
use FourPaws\StoreBundle\FourPawsStoreBundle;
use FourPaws\UserBundle\FourPawsUserBundle;
use JMS\SerializerBundle\JMSSerializerBundle;
use Misd\PhoneNumberBundle\MisdPhoneNumberBundle;
use Nelmio\ApiDocBundle\NelmioApiDocBundle;
use OldSound\RabbitMqBundle\OldSoundRabbitMqBundle;
use Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle;
use Symfony\Bundle\DebugBundle\DebugBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\TwigBundle\TwigBundle;
use Symfony\Bundle\WebProfilerBundle\WebProfilerBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\HttpKernel\Kernel;

class AppKernel extends Kernel
{
    /**
     * Папка с конфигами сайта
     */
    const CONFIG_DIR = '/app/config';

    /**
     * Папка с кешем symfony
     */
    const CACHE_DIR = '/var/cache';

    /**
     * @var string
     */
    protected static $documentRoot;

    /**
     * @return string
     */
    public static function getDocumentRoot(): string
    {
        if (null === static::$documentRoot) {
            static::$documentRoot = \dirname(__DIR__, 2) . '/web';
        }

        return static::$documentRoot;
    }

    /**
     * Returns an array of bundles to register.
     *
     * @return BundleInterface[] An array of bundle instances
     */
    public function registerBundles(): array
    {
        $bundles = [
            /** Symfony bundles */
            new FrameworkBundle(),
            new TwigBundle(),
            new SensioFrameworkExtraBundle(),

            /** External bundles */
            new CircleRestClientBundle(),
            new OldSoundRabbitMqBundle(),
            new FOSRestBundle(),
            new JMSSerializerBundle(),
            new NelmioApiDocBundle(),
            new MisdPhoneNumberBundle(),
            new CocurSlugifyBundle(),

            /** Internal bundles */
            new FourPawsAppBundle(),
            new FourPawsUserBundle(),
            new FourPawsCatalogBundle(),
            new FourPawsDeliveryBundle(),
            new FourPawsSaleBundle(),
            new FourPawsStoreBundle(),
            new FourPawsSapBundle(),
            new FourPawsPersonalBundle(),
        ];

        if (\in_array($this->getEnvironment(), ['dev', 'test'], true)) {
            $bundles[] = new DebugBundle();
            $bundles[] = new WebProfilerBundle();
        }
        return $bundles;
    }

    /**
     * Loads the container configuration.
     *
     * @param LoaderInterface $loader A LoaderInterface instance
     *
     * @throws \Exception
     */
    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load($this->getRootDir() . static::CONFIG_DIR . '/config_' . $this->getEnvironment() . '.yml');
    }

    /**
     * {@inheritdoc}
     */
    public function getRootDir()
    {
        return \dirname(static::getDocumentRoot());
    }

    /**
     * {@inheritdoc}
     */
    public function getCacheDir()
    {
        /**
         * Ввиду использования вагранта симфони не может очистить директорию, которая используется по умолчанию
         */
        if ($this->getEnvironment() === 'dev') {
            return '/tmp/sfcache/' . $this->getEnvironment();
        }
        return $this->getRootDir() . static::CACHE_DIR . '/' . $this->getEnvironment();
    }

    /**
     * {@inheritdoc}
     */
    public function getLogDir()
    {
        return getenv('WWW_LOG_DIR') ?: $this->getRootDir() . '/var/logs/';
    }
}
