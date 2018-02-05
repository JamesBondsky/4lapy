<?php

namespace FourPaws\App;

class Env
{
    const PROD   = 'prod';
    
    const DEV    = 'dev';
    
    const STAGE  = 'stage';
    
    const TEST   = 'test';
    
    const MOBILE = 'mobile';
    
    /**
     * @return string
     */
    public static function getServerType() : string
    {
        $env = $_SERVER['APP_ENV'] ?? $_SERVER['HTTP_APP_ENV'] ?? $_COOKIE['DEV'] ?? getenv('APP_ENV') ?? self::PROD;
        
        return \in_array($env, self::getEnvList(), true) ? $env : self::PROD;
    }
    
    /**
     * @return array
     */
    public static function getEnvList() : array
    {
        static $envList;
        
        if (!$envList) {
            try {
                $envList = (new \ReflectionClass(self::class))->getConstants();
            } catch (\ReflectionException $e) {
                $envList = ['undefined'];
            }
        }
        
        return $envList;
    }
    
    /**
     * @return bool
     */
    public static function isProd() : bool
    {
        return self::getServerType() === self::PROD;
    }
    
    /**
     * @return bool
     */
    public static function isDev() : bool
    {
        return self::getServerType() === self::DEV;
    }
    
    /**
     * @return bool
     */
    public static function isStage() : bool
    {
        return self::getServerType() === self::STAGE;
    }
    
    /**
     * @return bool
     */
    public static function isTest() : bool
    {
        return self::getServerType() === self::TEST;
    }
    
    /**
     * @return bool
     */
    public static function isMobile() : bool
    {
        return self::getServerType() === self::MOBILE;
    }
    
}
