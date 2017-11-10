<?php

namespace FourPaws\App;

use Adv\Bitrixtools\Tools\EnvType;
use FourPaws\App\MarkupBuild\JsonFileLoader;
use FourPaws\App\MarkupBuild\MarkupBuild;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpFoundation\Request;

class Application extends AppKernel
{
    /**
     * Папка для кеширования
     */
    const BITRIX_CACHE_DIR = '/local/cache';

    /**
     * Папка с включаемыми областями
     */
    const INCLUDES_DIR = '/includes';

    /**
     * @var MarkupBuild
     */
    private static $markupBuild;

    /**
     * @var \FourPaws\App\Application
     */
    private static $instance;

    /**
     * @throws \Psr\Cache\InvalidArgumentException
     *
     * TODO Изменить под 4 лапы
     * @return MarkupBuild
     */
    public static function markup(): MarkupBuild
    {
        if (null === self::$markupBuild) {
            //TODO Позже эту строчку вынести в отдельный метод, возвращающий настроенный пул файлового кеша
            $cache = new FilesystemAdapter('4lapy', 86400, self::getDocumentRoot() . self::BITRIX_CACHE_DIR);

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
                    $jsonFileLoader =
                        new JsonFileLoader($markupBuild, new FileLocator(self::getDocumentRoot() . '/static'));
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
     * Handle current request
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     * @throws \Exception
     */
    public static function handleRequest(Request $request)
    {
        $instance = static::getInstance();
        $response = $instance->handle($request);
        $response->send();
        $instance->terminate($request, $response);
    }

    /**
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     * @return \FourPaws\App\Application
     *
     */
    public static function getInstance(): Application
    {
        /**
         * Можем себе позволить, в общем случае объект иммутабелен.
         */
        if (!self::$instance) {
            self::$instance = new self(EnvType::getServerType(), EnvType::isDev());
        }

        if (!self::$instance->booted) {
            self::$instance->boot();
        }

        return self::$instance;
    }

    /**
     * @param string $path
     *
     * @return string
     */
    public static function getAbsolutePath(string $path): string
    {
        return self::getDocumentRoot() . $path;
    }
}
