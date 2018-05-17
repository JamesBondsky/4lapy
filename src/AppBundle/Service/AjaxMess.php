<?php

namespace FourPaws\AppBundle\Service;

use FourPaws\App\Response\JsonErrorResponse;
use FourPaws\App\Response\JsonResponse;

/**
 * Class AjaxMess
 *
 * @package FourPaws\AppBundle\Service
 */
class AjaxMess
{
    /**
     * @param string $text
     *
     * @return JsonResponse
     */
    public function getOrderCreateError(string $text = ''): JsonResponse
    {
        $mess = 'Ошибка при создании заказа';
        if(!empty($text)){
            $mess.=' - '.$text;
        }
        return $this->getJsonError('orderCreateError', $mess);
    }

    /**
     * @return JsonResponse
     */
    public function getWrongPhoneNumberException(): JsonResponse
    {
        return $this->getJsonError('wrongPhone', 'Некорректный номер телефона');
    }

    /**
     * @return JsonResponse
     */
    public function getNeedAuthError(): JsonResponse
    {
        return $this->getJsonError('needAuth', 'Необходимо авторизоваться');
    }

    /**
     * @return JsonResponse
     */
    public function getSecurityError(): JsonResponse
    {
        return $this->getJsonError('securityError', 'Ошибка безопасности');
    }

    /**
     * @param string $mess
     *
     * @return JsonResponse
     */
    public function getNotIdError(string $mess = ''): JsonResponse
    {
        return $this->getJsonError('notIdError', 'Не указан ID'.$mess);
    }

    /** error block */

    /**
     * @return JsonResponse
     */
    public function getSmsSendErrorException(): JsonResponse
    {
        return $this->getJsonError('errorSmsSend', 'Ошибка отправки смс, попробуйте позднее');
    }

    /**
     * @return JsonResponse
     */
    public function getSystemError(): JsonResponse
    {
        return $this->getJsonError('systemError',
            'Извините! Произошла непредвиденная ошибка. Мы уже работаем над её решением.');
    }

    /**
     * @param string $login
     *
     * @return JsonResponse
     */
    public function getUsernameNotFoundException(string $login = ''): JsonResponse
    {
        $loginText = !empty($login) ? ' с данным логином ' . $login : '';
        $mes = 'Не найдено пользователей' . $loginText;
        return $this->getJsonError('noUser', $mes);
    }

    /**
     * @param string $hotLinePhone
     *
     * @param string $login
     *
     * @return JsonResponse
     */
    public function getTooManyUserFoundException(string $hotLinePhone = '', string $login = ''): JsonResponse
    {
        $hotLineText = $hotLinePhone ? ' по телефону ' . $hotLinePhone : '';
        $loginText = !empty($login) ? ' с данным логином ' . $login : '';
        $mes = 'Найдено больше одного пользователя' . $loginText . ', обратитесь на горячую линию' . $hotLineText;
        return $this->getJsonError('moreOneUser', $mes);
    }

    /**
     * @param array $additionalData
     *
     * @return JsonResponse
     */
    public function getNotFoundConfirmedCodeException(array $additionalData = []): JsonResponse
    {
        return $this->getJsonError('notFoundConfirmCode', 'Код подтверждения не найден', $additionalData);
    }

    /**
     * @param array $additionalData
     *
     * @return JsonResponse
     */
    public function getExpiredConfirmCodeException(array $additionalData = []): JsonResponse
    {
        return $this->getJsonError('expiredConfirmCode', 'Срок действия кода подтверждения истек', $additionalData);
    }

    /**
     * @param array $additionalData
     *
     * @return JsonResponse
     */
    public function getWrongConfirmCode(array $additionalData = []): JsonResponse
    {
        return $this->getJsonError('wrongConfirmCode', 'Код подтверждения не соответствует', $additionalData);
    }

    /**
     * @return JsonResponse
     */
    public function getHaveEmailError(): JsonResponse
    {
        return $this->getJsonError('haveEmail', 'Такой email уже существует');
    }

    /**
     * @return JsonResponse
     */
    public function getHavePhoneError(): JsonResponse
    {
        return $this->getJsonError('havePhone', 'Такой телефон уже существует');
    }

    /**
     * @return JsonResponse
     */
    public function getHaveLoginError(): JsonResponse
    {
        return $this->getJsonError('haveLogin', 'Такой логин уже существует');
    }

    /**
     * @param string $additionalMes
     *
     * @return JsonResponse
     */
    public function getRegisterError(string $additionalMes = ''): JsonResponse
    {
        $additionalMes = !empty($additionalMes) ? ' - ' . $additionalMes : '';
        $mes = 'При регистрации произошла ошибка' . $additionalMes;
        return $this->getJsonError('registerError', $mes);
    }

    /**
     * @return JsonResponse
     */
    public function getFailCaptchaCheckError(): JsonResponse
    {
        return $this->getJsonError('wrongCaptcha', 'Проверка капчи не пройдена');
    }

    /**
     * @return JsonResponse
     */
    public function getNotAuthorizedException(): JsonResponse
    {
        return $this->getJsonError('userNotAuthorized',
            'Вы не авторизованы, необходимо авторизоваться для продолжения');
    }

    /**
     * @return JsonResponse
     */
    public function getEmptyDataError(): JsonResponse
    {
        return $this->getJsonError('emptyData', 'Должны быть заполнены все обязательные поля');
    }

    /**
     * @return JsonResponse
     */
    public function getNotActiveUserError(): JsonResponse
    {
        return $this->getJsonError('notActiveUser',
            'Учетная запись есть на сайте, но она не активна, пожалуйста, обратитесь к администрации сайта');
    }

    /**
     * @param string $length
     *
     * @return JsonResponse
     */
    public function getPasswordLengthError(string $length): JsonResponse
    {
        return $this->getJsonError('errorValidMinLengthPassword',
            'Пароль должен содержать минимум ' . $length . ' символов');
    }

    /**
     * @return JsonResponse
     */
    public function getNotEqualPasswordError(): JsonResponse
    {
        return $this->getJsonError('notEqualPasswords', 'Пароли не соответсвуют');
    }

    /**
     * @return JsonResponse
     */
    public function getNotEqualOldPasswordError(): JsonResponse
    {
        return $this->getJsonError('notEqualOldPassword', 'Текущий пароль не соответствует введенному');
    }

    /**
     * @return JsonResponse
     */
    public function getEqualWithOldPasswordError(): JsonResponse
    {
        return $this->getJsonError('equalWithOldPassword', 'Пароль не может быть таким же, как и текущий');
    }

    /**
     * @param string $error
     *
     * @return JsonResponse
     */
    public function getUpdateError(string $error = ''): JsonResponse
    {
        $errorText = !empty($error) ? ' - ' . $error : '';
        return $this->getJsonError('errorUpdate', 'Произошла ошибка при обновлении' . $errorText);
    }

    /**
     * @param string $error
     *
     * @return JsonResponse
     */
    public function getDeleteError(string $error = ''): JsonResponse
    {
        $errorText = !empty($error) ? ' - ' . $error : '';
        return $this->getJsonError('errorUpdate', 'Произошла ошибка при удалении' . $errorText);
    }

    /**
     * @param string $error
     *
     * @return JsonResponse
     */
    public function getAddError(string $error = ''): JsonResponse
    {
        $errorText = !empty($error) ? ' - ' . $error : '';
        return $this->getJsonError('errorUpdate', 'Произошла ошибка при добавлении' . $errorText);
    }

    /**
     * @return JsonResponse
     */
    public function getAuthError(): JsonResponse
    {
        return $this->getJsonError('errorAuth', 'Произошла ошибка при авторизации');
    }

    /**
     * @return JsonResponse
     */
    public function getNoActionError(): JsonResponse
    {
        return $this->getJsonError('noAction', 'Не найдено действие для выполнения');
    }

    /**
     * @return JsonResponse
     */
    public function getEmailSendError(): JsonResponse
    {
        return $this->getJsonError('errorEmailSend', 'Отправка письма не удалась, пожалуйста, попробуйте позднее');
    }

    /**
     * @return JsonResponse
     */
    public function getWrongEmailError(): JsonResponse
    {
        return $this->getJsonError('wrongEmail', 'Введен неверный email');
    }

    /**
     * @return JsonResponse
     */
    public function getEmptyPasswordError(): JsonResponse
    {
        return $this->getJsonError('emptyPassword', 'Не указан пароль');
    }

    /**
     * @param array $additionalData
     *
     * @return JsonResponse
     */
    public function getWrongPasswordError(array $additionalData = []): JsonResponse
    {
        return $this->getJsonError('wrongPassword', 'Неверный логин или пароль',$additionalData);
    }

    /**
     * @return JsonResponse
     */
    public function getNotOldPhoneError(): JsonResponse
    {
        return $this->getJsonError('notOldPhone', 'Текущий телефон не задан');
    }

    /**
     * @return JsonResponse
     */
    public function getVerificationError(): JsonResponse
    {
        return $this->getJsonError('verificationError', 'Ошибка верификации');
    }

    /**
     * @return JsonResponse
     */
    public function getEmptyCardNumber(): JsonResponse
    {
        return $this->getJsonError('emptyCardNumber', 'Не указан номер карты');
    }

    /**
     * @return JsonResponse
     */
    public function getWrongCardNumber(): JsonResponse
    {
        return $this->getJsonError('wrongCardNumber', 'Номер карты неверный');
    }

    /**
     * @return JsonResponse
     */
    public function getCardNotValidError(): JsonResponse
    {
        return $this->getJsonError('cardNotValid', 'Карта не привязывается');
    }

    /**
     * @return JsonResponse
     */
    public function getCardNotFoundError(): JsonResponse
    {
        return $this->getJsonError('cardNotFound', 'Карта не найдена');
    }

    /**
     * @return JsonResponse
     */
    public function getNotAllowedEASendError(): JsonResponse
    {
        return $this->getJsonError('notAllowedEASend', 'Отправка писем недоступна - необходимо подтвердить почту');
    }

    /**
     * @param int $size
     *
     * @return JsonResponse
     */
    public function getFileSizeError(int $size): JsonResponse
    {
        return $this->getJsonError('fileSizeError', 'Превышен максимально допустимый размер файла в '.$size.'Мб');
    }

    /**
     * @param array $valid_types
     *
     * @return JsonResponse
     */
    public function getFileTypeError(array $valid_types): JsonResponse
    {
        return $this->getJsonError('fileTypeError', 'Неверный формат файла, допусимые форматы: ' . implode(', ', $valid_types));
    }

    /**
     * @param string $code
     * @param string $mes
     *
     * @param array  $additionalData
     *
     * @return JsonResponse
     */
    private function getJsonError(string $code, string $mes, array $additionalData = []): JsonResponse
    {
        return JsonErrorResponse::createWithData(
            $mes,
            array_merge(['errors' => [$code => $mes]], $additionalData)
        );
    }
}
