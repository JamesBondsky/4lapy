<?php

use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\Result;
use Bitrix\Main\Type\DateTime;
use FourPaws\App\Application;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\PersonalBundle\Service\PersonalOffersService;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

class FourPawsFrontOfficeFestRegComponent extends \FourPaws\FrontOffice\Bitrix\Component\SubmitForm
{
    /** операции, к одной из которых у пользователя должен быть доступ (по умолчанию) */
    const CAN_ACCESS_USER_OPERATIONS_DEFAULT = [
        'view_subordinate_users',
        'view_all_users',
        'edit_subordinate_users',
    ];

    /** @var array $checkSubordinateOperations */
    protected $checkSubordinateOperations = [
        'view_all_users',
        'edit_all_users'
    ];

    /**
     * FourPawsFrontOfficeFestRegComponent constructor.
     *
     * @param null|\CBitrixComponent $component
     */
    public function __construct($component = null)
    {
        parent::__construct($component);

        $this->withLogName(__CLASS__);
    }

    /**
     * @param array $params
     * @return array
     */
    public function onPrepareComponentParams($params)
    {
        $params = parent::onPrepareComponentParams($params);

        return $params;
    }

    /**
     * @return string
     */
    protected function prepareAction()
    {
        $action = 'initialLoad';

        if ($this->request->get('formName') === 'festUserReg' && $this->request->get('action') === 'userReg') {
            $action = 'userReg';
        }

        return $action;
    }

    /**
     * @throws ApplicationCreateException
     */
    protected function initialLoadAction()
    {
        $this->loadData();
    }

    /**
     * @throws ApplicationCreateException
     */
    protected function userRegAction()
    {
        $this->initPostFields();

        //if ($this->canEnvUserAccess()) {
            $this->processRegFormFields();
            $this->regUser();
        //}

        $this->loadData();
    }

    /**
     * @throws ApplicationCreateException
     */
    protected function loadData()
    {
        $this->arResult['IS_AUTHORIZED'] = $GLOBALS['USER']->isAuthorized() ? 'Y' : 'N';
        $this->arResult['CAN_ACCESS'] = $this->canEnvUserAccess() ? 'Y' : 'N';
        $this->arResult['ACTION'] = $this->getAction();
        //$this->arResult['IS_AVATAR_AUTHORIZED'] = $this->getUserService()->isAvatarAuthorized() ? 'Y' : 'N';

        $this->includeComponentTemplate();
    }

    /**
     * processRegFormFields
     */
    protected function processRegFormFields()
    {
        $fieldName = 'firstName';
        $value = $this->trimValue($this->getFormFieldValue($fieldName));
        if ($value == '') {
            $this->setFieldError($fieldName, 'Не введено имя', 'not_valid');
        }

        $fieldName = 'email';
        $value = $this->trimValue($this->getFormFieldValue($fieldName));
        $email = trim($value);
        if ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->setFieldError($fieldName, 'Email задан некорректно', 'not_valid');
        }

        $fieldName = 'phone';
        $value = $this->trimValue($this->getFormFieldValue($fieldName));
        if ($value == '') {
            $this->setFieldError($fieldName, 'Номер телефона не задан', 'not_valid');
        } else {
            $phone = $this->cleanPhoneNumberValue($value);
            if ($phone === '') {
                $this->setFieldError($fieldName, 'Номер телефона задан некорректно', 'not_valid');
            }
        }
    }

    /**
     * Заполнение arResult списком пользователей по поисковому запросу
     */
    protected function regUser()
    {
        $searchResult = null;
        if (empty($this->arResult['ERROR']['FIELD']) && empty($this->arResult['ERROR']['EXEC'])) {
            /** @var PersonalOffersService $personalOffersService */
            $personalOffersService = Application::getInstance()->getContainer()->get('personal_offers.service');

            $values = $this->arResult["FIELD_VALUES"];
            /** @var DataManager $festivalUsersDataManager */
            $festivalUsersDataManager = Application::getInstance()->getContainer()->get('bx.hlblock.festivalusersdata');

            $checkingFilter = [
                'LOGIC' => 'OR',
            ];
            if ($values['phone']) {
                $checkingFilter['UF_PHONE'] = $values['phone'];
            }
            if ($values['email']) {
                $checkingFilter['UF_EMAIL'] = $values['email'];
            }
            if ($values['passport']) {
                $checkingFilter['UF_PASSPORT'] = $values['passport'];
            }
            $registeredUser = $festivalUsersDataManager::query()
                ->setFilter($checkingFilter)
                ->setSelect([
                    'UF_FESTIVAL_USER_ID',
                    'UF_PASSPORT'
                ])
                ->setLimit(3) // зарегистрированных юзеров может два, если у одного совпадает телефон, у другого email, у третьего - паспорт
                ->fetchAll();
            if ($registeredUser) {
                $alreadyRegisteredText = [];
                foreach ($registeredUser as $user)
                {
                    if ($user['UF_FESTIVAL_USER_ID']) {
                        $text = 'Номер участника: ' . $user['UF_FESTIVAL_USER_ID'];
                        if ($user['UF_PASSPORT']) {
                            $text .= ', номер паспорта: ' . $user['UF_PASSPORT'];
                        } else {
                            $text .= ', <a href="/fest-reg/search/?promoId=' . $user['UF_FESTIVAL_USER_ID'] . '">найти по номеру участника</a>';
                        }
                        $alreadyRegisteredText[] = $text;
                        unset($text);
                    }
                }
                $alreadyRegisteredText = implode('<br>', $alreadyRegisteredText);
                $this->setExecError('', 'Пользователь с таким email/телефоном/номером паспорта уже зарегистрирован.<br>' . $alreadyRegisteredText, 'alreadyRegistered');
                return;
            }

            $festivalUserId = $personalOffersService->generateFestivalUserId();

            $festivalUserAddResult = $festivalUsersDataManager::add([
                'UF_SURNAME' => $values['lastName'],
                'UF_NAME' => $values['firstName'],
                'UF_PHONE' => $values['phone'],
                'UF_EMAIL' => $values['email'],
                'UF_FESTIVAL_USER_ID' => $festivalUserId,
                'UF_DATE_CREATED' => new DateTime(),
                'UF_PASSPORT' => $values['passport'],
            ]);

            if (!$festivalUserAddResult->isSuccess()) {
                $this->setExecError('', implode(', ', $festivalUserAddResult->getErrorMessages()));
                return;
            }

            $festivalUserId = $festivalUsersDataManager::getById($festivalUserAddResult->getId())->fetch()['UF_FESTIVAL_USER_ID'];

            $this->arResult['IS_REGISTERED'] = 'Y';
            $this->arResult['PARTICIPANT_ID'] = $festivalUserId;
        }
        if ($searchResult) {
            $this->arResult['USERS_LIST'] = $searchResult->getData()['list'];
            if (!$searchResult->isSuccess()) {
                foreach ($searchResult->getErrors() as $error) {
                    $this->setExecError($error->getCode(), $error->getMessage());
                }
            }
        }
    }
}
