<?php

namespace FourPaws\PersonalBundle\Exception;

class InvalidArgumentException extends BaseException
{
    public const ERRORS = [
        1 => 'Сan\'t set Used status to promocode. Got empty $manzanaId',
        2 => 'Указан неверный ID промокода',
        3 => 'Не указано название персонального предложения',
    ];
}
