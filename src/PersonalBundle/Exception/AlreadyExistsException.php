<?php

namespace FourPaws\PersonalBundle\Exception;

class AlreadyExistsException extends BaseException
{
    public const ERRORS = [
        1 => 'Персональное предложение уже существует',
    ];
}
