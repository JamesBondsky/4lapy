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
    
    /** именительный падеж */
    const SHORT_NOMINATIVE = 'ShortNominative';
    
    /** родительный падеж */
    const SHORT_GENITIVE = 'ShortGenitive';
    
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
    
    /** кратские месяца в именительном падеже  */
    private static $monthShortNominative = [
        '#1#'  => 'янв',
        '#2#'  => 'фев',
        '#3#'  => 'мар',
        '#4#'  => 'апр',
        '#5#'  => 'май',
        '#6#'  => 'июн',
        '#7#'  => 'июл',
        '#8#'  => 'авг',
        '#9#'  => 'сен',
        '#10#' => 'окт',
        '#11#' => 'ноя',
        '#12#' => 'дек',
    ];
    
    /**кратские месяца в родительном падеже*/
    private static $monthShortGenitive = [
        '#1#'  => 'янв',
        '#2#'  => 'фев',
        '#3#'  => 'мар',
        '#4#'  => 'апр',
        '#5#'  => 'мая',
        '#6#'  => 'июн',
        '#7#'  => 'июл',
        '#8#'  => 'авг',
        '#9#'  => 'сен',
        '#10#' => 'окт',
        '#11#' => 'ноя',
        '#12#' => 'дек',
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
