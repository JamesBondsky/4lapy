<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

use FourPaws\App\Application as App;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
/** @global \CDatabase $DB */
/** @global \CUser $USER */

/** @global \CMain $APPLICATION */

/** @noinspection AutoloadingIssuesInspection */
class CPersonalCabinetProfileComponent extends CBitrixComponent
{
    /**
     * {@inheritdoc}
     * @throws \Bitrix\Main\LoaderException
     */
    public function executeComponent()
    {
        $this->setFrameMode(true);
        
        /** @var \FourPaws\UserBundle\Service\UserService $userService */
        $userService =
            App::getInstance()->getContainer()->get(\FourPaws\UserBundle\Service\CurrentUserProviderInterface::class);
        if (!$userService->isAuthorized()) {
            return null;
        }
        
        $curUser                    = $userService->getCurrentUser();
        /** Русская локаль не помогла - может можно по другому? */
        $months                     =
            [
                '#1#' => 'Января',
                '#2#' => 'Февраля',
                '#3#' => 'Марта',
                '#4#' => 'Апреля',
                '#5#' => 'Мая',
                '#6#' => 'Июня',
                '#7#' => 'Июля',
                '#8#' => 'Августа',
                '#9#' => 'Сентября',
                '#10#' => 'Октября',
                '#11#' => 'Ноября',
                '#12#' => 'Декабря',
            ];
        $monthNumber = $curUser->getBirthday()->format('#n#');
        $this->arResult['CUR_USER'] = [
            'PERSONAL_PHONE' => $curUser->getPersonalPhone(),
            'EMAIL'          => $curUser->getEmail(),
            'FULL_NAME'      => $curUser->getFullName(),
            'LAST_NAME'      => $curUser->getLastName(),
            'NAME'           => $curUser->getName(),
            'SECOND_NAME'    => $curUser->getSecondName(),
            'GENDER'         => $curUser->getGender(),
            'GENDER_TEXT'    => $curUser->getGenderText(),
            'BIRTHDAY' => str_replace($monthNumber, $months[$monthNumber], $curUser->getBirthday()->format('j #n# Y')),
            'EMAIL_CONFIRMED' => $curUser->isEmailConfirmed(),
            'PHONE_CONFIRMED' => $curUser->isPhoneConfirmed(),
        ];
        
        $this->includeComponentTemplate();
        
        return true;
    }
}
