<?

namespace FourPaws\AppBundle\Service;

use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use Bitrix\Main\Data\Cache;
use Exception;
use Psr\Log\LoggerInterface;

/**
 * Используется для блокировки генерирования кэша с ожиданием.
 * Реализовано не через семафоры, чтобы избежать перманентных локов
 *
 * Class CacheGeneratingLocker
 * @package FourPaws\AppBundle\Service
 */
class CacheGeneratingLocker
{
    /** @var bool */
    protected $isDebugMode = false;

    /** @var int */
    protected $randomizedCode = 0;

    /** @var int */
    protected $cacheGeneratingTtl = 30; // seconds

    /** @var string */
    protected $cacheGeneratingInitDir = '/';

    /** @var int */
    protected $cacheGeneratingCheckRate = 300;

    /** @var LoggerInterface */
    protected $tempLogger; // Частота проверки, сгенерировался ли кэш, ms

    /** @var string */
    protected $logPrefix = '';


    /**
     * @param string $checkCacheId
     */
    public function lock(string $checkCacheId): void
    {
        $cache = Cache::createInstance();
        $cache->forceRewriting(true);
        if ($cache->startDataCache($this->cacheGeneratingTtl, $checkCacheId, $this->cacheGeneratingInitDir)) {
            $cache->endDataCache([1]); // Установка флага, что кэш начал генерироваться
        }
        if ($this->isDebugMode()) {
            $tempLogger = LoggerFactory::create('getRegionalStores', 'getRegionalStores');
            $tempLogger->info($this->getLogPrefix() . ' -- ' . $this->getRandomizedCode() . ' ------ Генерируется кэш');
        }
        if ($this->isDebugMode()) {
            sleep(10); // только для отладки
        }
    }

    /**
     * @param string $checkCacheId
     */
    public function unlock(string $checkCacheId): void
    {
        if ($this->isDebugMode()) {
            $this->tempLogger->info($this->getLogPrefix() . ' -- ' . $this->getRandomizedCode() . ' --- отдан результат');
        }

        // кэш закончил генерироваться, флаг снимается
        $cache = Cache::createInstance();
        $cache->clean($checkCacheId, $this->getCacheGeneratingInitDir()); // Снятие флага означает, что кэш закончил генерироваться
    }

    /**
     * @param string $checkCacheId
     */
    public function waitForNewCache(string $checkCacheId): void
    {
        if ($this->isDebugMode()) {
            $this->tempLogger->info($this->getLogPrefix() . ' -- ' . $this->getRandomizedCode());

            $this->tempLogger->info($this->getLogPrefix() . ' -- ' . $this->getRandomizedCode() . ' --- до проверки генерирования кэша');
        }


        $cache = Cache::createInstance();
        while ($cache->initCache($this->getCacheGeneratingTtl(), $checkCacheId, $this->getCacheGeneratingInitDir())) // Если кэш catalog:store уже начал генерироваться в другом процессе
        {
            if ($this->isDebugMode()) {
                $this->tempLogger = LoggerFactory::create('getRegionalStores', 'getRegionalStores');
                $this->tempLogger->info($this->getLogPrefix() . ' -- ' . $this->getRandomizedCode() . ' --- ждем');
            }
            usleep(1000 * $this->getCacheGeneratingCheckRate()); // Каждые $cacheGeneratingCheckRate мс проверка, не закончил ли кэш catalog:store генерироваться
        } // когда закончил - продолжаем выполнение


        if ($this->isDebugMode()) {
            $this->tempLogger->info($this->getLogPrefix() . ' -- ' . $this->getRandomizedCode() . ' --- начинаем получать из кеша');
        }
    }

    public function cacheGeneratedLog(): void
    {
        if ($this->isDebugMode()) {
            $this->tempLogger->info($this->getLogPrefix() . ' -- ' . $this->getRandomizedCode() . ' ------ кэш сгенерирован');
        }
    }

    /**
     * @return int
     */
    public function getRandomizedCode(): int
    {
        return $this->randomizedCode;
    }

    /**
     * @param int $randomizedCode
     */
    public function setRandomizedCode(int $randomizedCode): void
    {
        $this->randomizedCode = $randomizedCode;
    }


    /**
     * @return bool
     */
    public function isDebugMode(): bool
    {
        return $this->isDebugMode;
    }

    /**
     * НЕ использовать на бое! Ставит задержку выполнения для отладки
     *
     * @param bool $isDebugMode
     * @return CacheGeneratingLocker
     */
    public function setIsDebugMode(bool $isDebugMode): CacheGeneratingLocker
    {
        $this->isDebugMode = $isDebugMode;

        try {
            $this->setRandomizedCode(random_int(0, 1000));
            $this->tempLogger = LoggerFactory::create('getRegionalStores', 'getRegionalStores');
        } catch (Exception $e) {
        }

        return $this;
    }


    /**
     * @return int
     */
    public function getCacheGeneratingTtl(): int
    {
        return $this->cacheGeneratingTtl;
    }

    /**
     * @param int $cacheGeneratingTtl
     * @return CacheGeneratingLocker
     */
    public function setCacheGeneratingTtl(int $cacheGeneratingTtl): CacheGeneratingLocker
    {
        $this->cacheGeneratingTtl = $cacheGeneratingTtl;
        return $this;
    }


    /**
     * @return string
     */
    public function getCacheGeneratingInitDir(): string
    {
        return $this->cacheGeneratingInitDir;
    }

    /**
     * @param string $cacheGeneratingInitDir
     * @return CacheGeneratingLocker
     */
    public function setCacheGeneratingInitDir(string $cacheGeneratingInitDir): CacheGeneratingLocker
    {
        $this->cacheGeneratingInitDir = $cacheGeneratingInitDir;
        return $this;
    }


    /**
     * @return int
     */
    public function getCacheGeneratingCheckRate(): int
    {
        return $this->cacheGeneratingCheckRate;
    }

    /**
     * @param int $cacheGeneratingCheckRate
     * @return CacheGeneratingLocker
     */
    public function setCacheGeneratingCheckRate(int $cacheGeneratingCheckRate): CacheGeneratingLocker
    {
        $this->cacheGeneratingCheckRate = $cacheGeneratingCheckRate;
        return $this;
    }


    /**
     * @return string
     */
    public function getLogPrefix(): string
    {
        return $this->logPrefix;
    }

    /**
     * @param string $logPrefix
     * @return CacheGeneratingLocker
     */
    public function setLogPrefix(string $logPrefix): CacheGeneratingLocker
    {
        $this->logPrefix = $logPrefix;
        return $this;
    }
}