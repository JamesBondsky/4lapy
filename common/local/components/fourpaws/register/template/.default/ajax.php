<?php
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

$request = \Bitrix\Main\Application::getInstance()->getContext()->getRequest();
$action  = $request->get('action');

\CBitrixComponent::includeComponentClass('FourPawsRegisterComponent');

switch ($action) {
    case 'checkUserByPhone':
        $phone  = $request->get('phone');
        $params = [
            'select' => [
                'ID',
                'PERSONAL_PHONE',
            ],
            'filter' => ['PERSOAL_PHONE' => $phone],
        ];
        $res    = \Bitrix\Main\UserTable::getList($params);
        switch ($res->getSelectedRowsCount()) {
            case 0:
                $result = [
                    'code' => 'userNotFound',
                    'msg'  => ' пользователь не найден',
                ];
                break;
            case 1:
                $result = [
                    'code' => 'userFound',
                    'msg'  => ' пользователь найден',
                ];
                break;
            default:
                $result = [
                    'code' => 'userFoundMore1',
                    'msg'  => ' найдено больше 1 пользователя',
                ];
                break;
        }
        break;
    case 'checkManzanaPhone':
        $phone      = $request->get('phone');
        $manzana = new FourPaws\External\ManzanaService();
        $arUserInfo = $manzana->getUserDataByPhone($phone);
        if (!empty($arUserInfo)) {
            switch ($arUserInfo['COUNT_USERS']) {
                case 0:
                    $result = [
                        'code' => 'notHavePhoneInManzaza',
                        'msg'  => 'Не найдено соответсвий номера телефона',
                    ];
                    break;
                case 1:
                    $result = [
                        'code' => 'havePhoneInManzaza',
                        'msg'  => 'Есть телефон в системе Manzaza',
                    ];
                    break;
                default:
                    $result = [
                        'code' => 'havePhoneInManzaza',
                        'msg'  => 'нет данных',
                    ];
                    break;
            }
        } else {
            $result = [
                'code' => 'havePhoneInManzaza',
                'msg'  => 'Есть телефон в системе Manzaza',
            ];
        }
        break;
    case 'sendSmsToValidatePhone':
        $phone = $request->get('phone');
        $res = FourPawsRegisterComponent::sendConfirmSms($phone);
        if($res){
            $result = [
                'code' => 'sendSucces',
                'msg'  => 'Сообщеине отправлено',
            ];
        }
        else{
            $result = [
                'code' => 'sendError',
                'msg'  => 'Введен неверный номер телефон - телефон должен быть в формате 89201612427',
            ];
        }
        break;
    case 'checkPhone':
        $phone            = $request->get('phone');
        $confirmPhoneCode = $request->get('confirmCode');
        if (!empty($phone) && !empty($confirmPhoneCode)) {
            $res = FourPawsRegisterComponent::checkConfirmSms($phone, $confirmPhoneCode);
            $result = [
                'code' => 'errorVerification',
                'msg'  => 'ошибка верификации телефона',
            ];
            if ($res) {
                $result = [
                    'code' => 'successVerification',
                    'msg'  => 'успешная верификация телефона',
                ];
            }
        }
        break;
    case 'updateManzazaData':
        break;
    case 'saveUserData':
        $arPostData = $request->getPostList()->toArray();
        TrimArr($arPostData);
        if (!empty($arPostData)) {
            if (!empty($arPostData['USER_ID'])) {
                $userID = $arPostData['USER_ID'];
                unset($arPostData['USER_ID']);
                $res = CUser::Update($userID, $arPostData);
                $result = [
                    'code' => 'errorUpdateData',
                    'msg'  => 'ошибка при обновлении данных',
                ];
                if ((int)$res > 0) {
                    $result = [
                        'code' => 'successUpdateData',
                        'msg'  => 'данные успешно обновлены',
                    ];
                }
            } else {
                $res = CUser::Add($arPostData);
                $result = [
                    'code' => 'errorAddData',
                    'msg'  => 'ошибка при удалении данных',
                ];
                if ((int)$res > 0) {
                    $result = [
                        'code' => 'successAddData',
                        'msg'  => 'данные успешно добалены',
                    ];
                }
            }
        }
        break;
}

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_after.php");