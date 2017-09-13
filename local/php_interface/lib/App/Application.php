<?

namespace FourPaws\App;

use Adv\Bitrixtools\Tools\EnvType;
use Circle\RestClientBundle\CircleRestClientBundle;
use FOS\RestBundle\FOSRestBundle;
use FourPaws\App\MarkupBuild\JsonFileLoader;
use FourPaws\App\MarkupBuild\MarkupBuild;
use Nelmio\ApiDocBundle\NelmioApiDocBundle;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;

class Application extends Kernel
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
     * @var MarkupBuild
     */
    private static $markupBuild;

    /**
     * @var \FourPaws\App\Application
     */
    private static $instance;

    /**
     * @return BundleInterface[] An array of bundle instances
     */
    public function registerBundles() {
        $bundles = [
            new CircleRestClientBundle(),
            new NelmioApiDocBundle(),
            new FOSRestBundle(),
        ];
        
        return $bundles;
    }
    
    /**
     * Load main config
     *
     * @param \Symfony\Component\Config\Loader\LoaderInterface $loader
     */
    public function registerContainerConfiguration(LoaderInterface $loader) {
        $loader->load(__DIR__ . '/../../../config.yml');
    }
    
    /**
     * @return MarkupBuild
     * @throws \Psr\Cache\InvalidArgumentException
     *
     * TODO Изменить под 4 лапы
     */
    public static function markup() {
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
     * @return string
     */
    public static function getDocumentRoot() {
        if (is_null(self::$documentRoot)) {
            self::$documentRoot = realpath(__DIR__ . '/../../../..');
        }
        
        return self::$documentRoot;
    }
    
    /**
     * Handle current request
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    public static function HandleRequest(Request $request) {
        $instance = new self(EnvType::getServerType(), EnvType::isDev());
        $response = $instance->handle($request);
        $response->send();
        $instance->terminate($request, $response);
    }
    
    /**
     * @return \FourPaws\App\Application
     */
    public static function getInstance() : Application {
        /**
         * Можем себе позволить, в общем случае объект иммутабелен.
         */
        if (!static::$instance) {
            static::$instance = new Application(EnvType::getServerType(), EnvType::isDev());
        }

        return static::$instance;
    }
}