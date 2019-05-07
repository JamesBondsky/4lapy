<?php
/**
 * Created by PhpStorm.
 * Date: 02.04.2018
 * Time: 14:19
 * @author      Makeev Ilya
 * @copyright   ADV/web-engineering co.
 */

namespace FourPaws\Enum;


/**
 * Class UserGroup
 * @package FourPaws\Enum
 */
class UserGroup
{
    /*
     * Администраторы
     */
    public const ADMIN = 1;
    /*
     * Все пользователи (в том числе неавторизованные)
     */
    public const ALL_USERS = 2;
    public const ALL_USERS_CODE = 'ALL_USERS';
    /*
     * Зарегистрированные пользователи
     */
    public const REGISTERED_USERS = 6;
    /*
     * Пользователи панели управления
     */

    public const CONTROL_PANEL_USERS = 7;
    /** id лучше не юзать */
    public const OPT_ID = 32;
    public const OPT_CODE = 'VIP';
    public const NOT_AUTH_CODE = 'NOT_AUTH'; // "Неавторизованные"

    public const BASKET_RULES = 'BASKET_RULES'; // "Применяются правила работы с корзиной"
}