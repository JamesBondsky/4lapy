<?php

namespace FourPaws\App;

use Adv\Bitrixtools\Tools\EnvType;
use Exception;
use FourPaws\App\MarkupBuild\JsonFileLoader;
use FourPaws\App\MarkupBuild\MarkupBuild;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Config\ConfigCache;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Dumper\PhpDumper;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader as DIYamlFileLoader;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Controller\ControllerResolver;
use Symfony\Component\HttpKernel\EventListener\RouterListener;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\Routing\Loader\YamlFileLoader as RoutingYamlFileLoader;
use Symfony\Component\Routing\Router;

class App
{
    /**
     * Папка с конфигами сайта
     */
    const CONFIG_DIR = '/local/php_interface/config';

    /**
     * Папка для кеширования
     */
    const BITRIX_CACHE_DIR = '/local/cache';

    /**
     * Папка с включаемыми областями
     */
    const INCLUDES_DIR = '/includes';

    /**
     * @var string
     */
    protected static $documentRoot;

    /**
     * @var Container
     */
    private static $container;

    /**
     * @var MarkupBuild
     */
    private static $markupBuild;

    /**
     * @return Container
     * @throws Exception
     */
    public static function container()
    {
        if (null === self::$container) {
            $cachedContainerClass = 'CachedContainer';
            $сacheFilePath = self::getDocumentRoot() . self::BITRIX_CACHE_DIR . '/container.php';
            $configCache = new ConfigCache($сacheFilePath, EnvType::isDev());

            if (!$configCache->isFresh()) {
                $fileLocator = new FileLocator(self::getDocumentRoot() . self::CONFIG_DIR);
                $container = new ContainerBuilder();
                $yamlFileLoader = new DIYamlFileLoader($container, $fileLocator);
                $yamlFileLoader->load('config.yml');

                $container->compile();
                $configCache->write(
                    (new PhpDumper($container))->dump(['class' => $cachedContainerClass]),
                    $container->getResources()
                );
            }

            /** @noinspection PhpIncludeInspection */
            require_once $сacheFilePath;

            self::$container = new $cachedContainerClass;
        }

        return self::$container;
    }

    /**
     * @return MarkupBuild
     * @throws \Psr\Cache\InvalidArgumentException
     *
     * TODO Изменить под 4 лапы
     */
    public static function markup()
    {
        if (null === self::$markupBuild) {
            //TODO Позже эту строчку вынести в отдельный метод, возвращающий настроенный пул файлового кеша
            $cache = new FilesystemAdapter(
                '4lapy',
                86400,
                self::getDocumentRoot() . self::BITRIX_CACHE_DIR
            );

            $markupBuildItem = $cache->getItem('markup_build');

            /** @noinspection PhpUndefinedMethodInspection */
            if (!$markupBuildItem->isHit() || EnvType::isDev()) {
                $markupBuild = new MarkupBuild();

                /**
                 * Ускорение отладки для front-end на реальном коде сайта
                 */
                //Если dev окружение И существует JS из dev-режима TARS,
                if (EnvType::isDev() && is_file(self::getDocumentRoot() . MarkupBuild::STATIC_DEV_JS)) {
                    //подключить результаты сборки к реальному сайту
                    $markupBuild->withJsFile(MarkupBuild::STATIC_DEV_JS)
                                ->withCssFile(MarkupBuild::STATIC_DEV_CSS)
                                ->withSvgFile(MarkupBuild::STATIC_DEV_SVG);
                } else {
                    $jsonFileLoader = new JsonFileLoader(
                        $markupBuild,
                        new FileLocator(self::getDocumentRoot() . '/static')
                    );
                    $jsonFileLoader->load('versions.json');
                }

                /** @noinspection PhpUndefinedMethodInspection */
                $markupBuildItem->set($markupBuild);
                $cache->save($markupBuildItem);
            }

            /** @noinspection PhpUndefinedMethodInspection */
            self::$markupBuild = $markupBuildItem->get();
        }

        return self::$markupBuild;
    }

    /**
     * @param Request $request
     */
    public static function handleRequest(Request $request)
    {
        $kernel = App::getHttpKernel();
        $response = $kernel->handle($request);
        $response->send();
        $kernel->terminate($request, $response);
    }

    /**
     * @return HttpKernel
     */
    public static function getHttpKernel()
    {
        $locator = new FileLocator(self::getDocumentRoot() . self::CONFIG_DIR . '/routes');
        $router = new Router(
            new RoutingYamlFileLoader($locator),
            'routes.yml',
            [
                'cache_dir' => self::getDocumentRoot() . self::BITRIX_CACHE_DIR,
                'debug'     => EnvType::isDev(),
            ]
        );

        $dispatcher = new EventDispatcher();
        $dispatcher->addSubscriber(new RouterListener($router->getMatcher(), new RequestStack()));
        $dispatcher->addSubscriber(new ErrorHandler());

        return new HttpKernel($dispatcher, new ControllerResolver());
    }

    /**
     * @return string
     */
    public static function getDocumentRoot()
    {
        if (is_null(self::$documentRoot)) {
            self::$documentRoot = realpath(__DIR__ . '/../../../..');
        }

        return self::$documentRoot;
    }

}
