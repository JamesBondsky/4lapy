<?php

namespace FourPaws\App;

use Bitrix\Main\Entity\DataManager;
use Exception;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\App\MarkupBuild\JsonFileLoader;
use FourPaws\App\MarkupBuild\MarkupBuild;
use Psr\Cache\InvalidArgumentException;
use RuntimeException;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class Application
 *
 * @package FourPaws\App
 */
class Application extends AppKernel
{
    /**
     * @var MarkupBuild
     */
    private static $markupBuild;
    /**
     * @var Application
     */
    private static $instance;

    /**
     * @todo отрефакторить
     *
     * @throws ApplicationCreateException
     * @throws InvalidArgumentException
     *
     * @return MarkupBuild
     */
    public static function markup(): MarkupBuild
    {
        if (null === self::$markupBuild) {
            $cache = new FilesystemAdapter('4lapy', 86400, self::getInstance()->getCacheDir());

            $markupBuildItem = $cache->getItem('markup_build');

            /** @noinspection PhpUn\definedMethodInspection */
            if (!$markupBuildItem->isHit() || !Env::isProd()) {
                $markupBuild = new MarkupBuild();

                /** @noinspection NotOptimalIfConditionsInspection
                 *
                 * Ускорение отладки для front-end на реальном коде сайта
                 *
                 * Если dev окружение И существует JS из dev-режима TARS
                 */
                if (!Env::isProd() && is_file(self::getDocumentRoot() . MarkupBuild::STATIC_DEV_JS)) {
                    //подключить результаты сборки к реальному сайту
                    $markupBuild->withJsFile(MarkupBuild::STATIC_DEV_JS)
                        ->withCssFile(MarkupBuild::STATIC_DEV_CSS)
                        ->withSvgFile(MarkupBuild::STATIC_DEV_SVG);
                } else {
                    $jsonFileLoader =
                        new JsonFileLoader($markupBuild, new FileLocator(self::getDocumentRoot() . '/static'));
                    $jsonFileLoader->load('versions.json');
                }

                /** @noinspection PhpUn\definedMethodInspection */
                $markupBuildItem->set($markupBuild);
                $cache->save($markupBuildItem);
            }

            /** @noinspection PhpUn\definedMethodInspection */
            self::$markupBuild = $markupBuildItem->get();
        }

        return self::$markupBuild;
    }

    /**
     * Handle current request
     *
     * @param Request $request
     *
     * @throws ApplicationCreateException
     * @throws Exception
     */
    public static function handleRequest(Request $request): void
    {
        $instance = static::getInstance();
        $response = $instance->handle($request);
        $response->send();
        $instance->terminate($request, $response);
    }

    /**
     * @throws ApplicationCreateException
     * @return Application
     *
     */
    public static function getInstance(): Application
    {
        /**
         * Можем себе позволить, в общем случае объект иммутабелен.
         */
        if (!self::$instance) {
            self::$instance = new self(Env::getServerType(), !Env::isProd());
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

    /**
     * Включаем битрикс
     */
    public static function includeBitrix(): void
    {
        \defined('NO_KEEP_STATISTIC') || \define('NO_KEEP_STATISTIC', 'Y');
        \defined('NOT_CHECK_PERMISSIONS') || \define('NOT_CHECK_PERMISSIONS', true);
        \defined('PUBLIC_AJAX_MODE') || \define('PUBLIC_AJAX_MODE', true);

        if (empty($_SERVER['DOCUMENT_ROOT'])) {
            $_SERVER['DOCUMENT_ROOT'] = self::getDocumentRoot();
        }

        $GLOBALS['DOCUMENT_ROOT'] = $_SERVER['DOCUMENT_ROOT'];

        /** @noinspection PhpIncludeInspection */
        require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';
    }

    /**
     * Возвращает объект DataManager для управления соответствующим hl-блоком.
     *
     * @param string $hlblockServiceName
     *
     * @return DataManager
     * @throws ServiceNotFoundException
     * @throws RuntimeException
     * @throws ApplicationCreateException
     * @throws ServiceCircularReferenceException
     */
    public static function getHlBlockDataManager(string $hlblockServiceName): DataManager
    {
        $dataManager = self::getInstance()->getContainer()->get($hlblockServiceName);

        /** Если это метод для HL-сущностей, то правильней проверять все же \Bitrix\Highloadblock\DataManager */
        if (!($dataManager instanceof DataManager)) {
            throw new RuntimeException(sprintf('Сервис %s не является %s',
                $hlblockServiceName,
                DataManager::class));
        }

        return $dataManager;
    }
}
