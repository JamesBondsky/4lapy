<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\Helpers;

/**
 * Class DateHelper
 *
 * @package FourPaws\Helpers
 */
class DateHelper
{
    /** именительный падеж */
    const NOMINATIVE = 'Nominative';
    
    /** родительный падеж */
    const GENITIVE = 'Genitive';
    
    /**Месяца в родительном падеже*/
    private static $monthGenitive = [
        '#1#'  => 'Января',
        '#2#'  => 'Февраля',
        '#3#'  => 'Марта',
        '#4#'  => 'Апреля',
        '#5#'  => 'Мая',
        '#6#'  => 'Июня',
        '#7#'  => 'Июля',
        '#8#'  => 'Августа',
        '#9#'  => 'Сентября',
        '#10#' => 'Октября',
        '#11#' => 'Ноября',
        '#12#' => 'Декабря',
    ];
    
    /** Месяца в именительном падеже  */
    private static $monthNominative = [
        '#1#'  => 'Январь',
        '#2#'  => 'Февраль',
        '#3#'  => 'Март',
        '#4#'  => 'Апрель',
        '#5#'  => 'Май',
        '#6#'  => 'Июнь',
        '#7#'  => 'Июль',
        '#8#'  => 'Август',
        '#9#'  => 'Сентябрь',
        '#10#' => 'Октябрь',
        '#11#' => 'Ноябрь',
        '#12#' => 'Декабрь',
    ];
    
    /**
     * @param string $date
     *
     * @param string $case
     *
     * @return string
     */
    public static function replaceRuMonth(string $date, string $case = 'Nominative') : string
    {
        preg_match('|#\d{1,2}#|', $date, $matches);
        if (!empty($matches[0])) {
            $months = [];
            if (!empty($case)) {
                $months = static::${'month' . $case};
            }
            
            return str_replace($matches[0], $months[$matches[0]], $date);
        }
        
        return $date;
    }
}
