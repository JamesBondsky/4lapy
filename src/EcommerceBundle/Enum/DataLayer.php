<?php

namespace FourPaws\EcommerceBundle\Enum;

/**
 * Class DataLayer
 *
 * @package FourPaws\EcommerceBundle\Enum
 */
class DataLayer
{
    public const AUTH_TYPE_LOGIN     = 'Войти';
    public const AUTH_TYPE_SOCIAL_VK = 'VK';
    public const AUTH_TYPE_SOCIAL_FB = 'FB';
    public const AUTH_TYPE_SOCIAL_OK = 'OK';
    
    public const REGISTER_TYPE_LOGIN     = 'телефон';
    public const REGISTER_TYPE_SOCIAL_VK = 'VK';
    public const REGISTER_TYPE_SOCIAL_FB = 'FB';
    public const REGISTER_TYPE_SOCIAL_OK = 'OK';
}
