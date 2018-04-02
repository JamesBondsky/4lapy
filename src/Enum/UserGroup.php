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
    /*
     * Зарегистрированные пользователи
     */
    public const REGISTERED_USERS = 6;
    /*
     * Пользователи панели управления
     */
    public const CONTROL_PANEL_USERS = 7;

}