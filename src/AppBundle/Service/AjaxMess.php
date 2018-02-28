<?php

namespace FourPaws\AppBundle\Service;

use FourPaws\App\Response\JsonErrorResponse;
use FourPaws\App\Response\JsonResponse;

class AjaxMess
{
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
            'Непредвиденная ошибка. Пожалуйста, обратитесь к администратору сайта');
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
     * @return JsonResponse
     */
    public function getNotFoundConfirmedCodeException(): JsonResponse
    {
        return $this->getJsonError('notFoundConfirmCode', 'Код подтверждения не найден');
    }

    /**
     * @return JsonResponse
     */
    public function getExpiredConfirmCodeException(): JsonResponse
    {
        return $this->getJsonError('expiredConfirmCode', 'Срок действия кода подтверждения истек');
    }

    /**
     * @return JsonResponse
     */
    public function getWrongConfirmCode(): JsonResponse
    {
        return $this->getJsonError('wrongConfirmCode', 'Код подтверждения не соответствует');
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
        return $this->getJsonError('emptyData', 'Должны быть заполнены все поля');
    }

    /**
     * @return JsonResponse
     */
    public function getNotActiveUserError(): JsonResponse
    {
        return $this->getJsonError('notActiveUser',
            'Учетная запись есть на сайте, но она не активна, пожалуйста обратитесь к администрации сайта');
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
        return $this->getJsonError('errorEmailSend', 'Отправка письма не удалась, пожалуйста попробуйте позднее');
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
     * @return JsonResponse
     */
    public function getWrongPasswordError(): JsonResponse
    {
        return $this->getJsonError('wrongPassword', 'Неверный логин или пароль');
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
     * @param string $code
     * @param string $mes
     *
     * @return JsonResponse
     */
    private function getJsonError(string $code, string $mes): JsonResponse
    {
        return JsonErrorResponse::createWithData(
            $mes,
            ['errors' => [$code => $mes]]
        );
    }
}
