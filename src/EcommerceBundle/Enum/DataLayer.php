<?php

namespace FourPaws\EcommerceBundle\Enum;

/**
 * Class DataLayer
 *
 * @package FourPaws\EcommerceBundle\Enum
 */
class DataLayer
{
    public const SOCIAL_VK = 'VK';
    public const SOCIAL_FB = 'FB';
    public const SOCIAL_OK = 'OK';

    public const AUTH_TYPE_LOGIN = 'Войти';

    public const REGISTER_TYPE_LOGIN = 'телефон';

    public const SOCIAL_SERVICE_MAP = [
        'VKontakte'     => self::SOCIAL_VK,
        'Facebook'      => self::SOCIAL_FB,
        'Odnoklassniki' => self::SOCIAL_OK,
    ];

    public const SORT_TYPE_SHOPS   = 'Магазины';
    public const SORT_TYPE_CATALOG = 'Каталог';

    public const SORT_VALUE_SHOPS_ADDRESS = 'По адресу';
    public const SORT_VALUE_SHOPS_METRO   = 'По метро';
}
