<?php
$MESS["BXMOD_AUTH_LOGIN_ERROR_EP"] = "Проверьте правильность заполнения поля. Введите ваш E-mail или номер телефона";
$MESS["BXMOD_AUTH_LOGIN_ERROR_E"] = "Проверьте правильность ввода адреса электронной почты";
$MESS["BXMOD_AUTH_LOGIN_ERROR_P"] = "Проверьте правильность ввода номера телефона";
$MESS["BXMOD_AUTH_LOGIN_ERROR_CAPTCHA"] = "Пожалуйста, введите символы с картинки";
$MESS["BXMOD_AUTH_LOGIN_ERROR_UNACTIVE"] = "Вход не возможен, обратитесь к администратору сайта";
$MESS["BXMOD_AUTH_LOGIN_ERROR_PASSWORD"] = "Неверно указан логин или пароль. Если у вас не получается войти, воспользуйтесь формой восстановления пароля. Нажмите ссылку &laquo;Забыли пароль?&raquo;";

$MESS["BXMOD_AUTH_CONFIRM_ERROR_CODE"] = "Вы ввели неверный проверочный код";

$MESS["BXMOD_AUTH_INITRESTORE_ERROR_EP"] = "Проверьте правильность заполнения поля. Введите ваш E-mail или номер телефона";
$MESS["BXMOD_AUTH_INITRESTORE_ERROR_E"] = "Проверьте правильность ввода адреса электронной почты";
$MESS["BXMOD_AUTH_INITRESTORE_ERROR_P"] = "Проверьте правильность ввода номера телефона";
$MESS["BXMOD_AUTH_INITRESTORE_ERROR_CAPTCHA"] = "Код с картинки введен неверно. Пожалуйста, введите заново";
$MESS["BXMOD_AUTH_INITRESTORE_SMS_LIMIT"] = "На данный номер телефона отправка SMS с информацией по восстановлению доступа, возможна через: <span class='h'>#HOUR#</span>:<span class='m'>#MIN#</span>:<span class='s'>#SEC#</span>";

$MESS["BXMOD_AUTH_RESTORE_ERROR_CODE"] = "Неверный код подтверждения";

$MESS["BXMOD_AUTH_UNKNOWN_ERROR"] = "Произошла неизвестная ошибка. Обновите страницу и попробуйте снова";

$MESS["BXMOD_AUTH_ERROR_SHORT_PASSWORD"] = "Пароль слишком короткий. Минимальное количество символов в пароле: ";
$MESS["BXMOD_AUTH_ERROR_LONG_PASSWORD"] = "Пароль слишком длинный. Максимальное количество символов в пароле: ";
$MESS["BXMOD_AUTH_ERROR_REPASSWORD"] = "Введеный пароль и его подтверждение не совпадают";

$MESS["BXMOD_AUTH_PARAM_USE_EMAIL"] = "Использовать вход по E-mail";
$MESS["BXMOD_AUTH_PARAM_USE_PHONE"] = "Использовать вход по номеру телефона";
$MESS["BXMOD_AUTH_PARAM_USE_SOCIAL"] = "Использовать вход через соц. сервисы";

$MESS["BXMOD_AUTH_MESSAGE_ALL_HELP"] = "Полное описание всех настроек модуля вы всегда можете найти по адресу: <a href='http://bxmod.ru/mods/avtorizatsiya-3-0/nastroyka-modulya-avtorizatsiya-3-0.html' title='Настройка модуля Авторизация  3.0' target='_blank'>http://bxmod.ru/mods/avtorizatsiya-3-0/nastroyka-modulya-avtorizatsiya-3-0.html</a><br>Для полноценной работы авторизации и регистрации по номеру телефона необходима <a href='http://bxmod.ru/mods/avtorizatsiya-3-0/otpravka-sms.html' title='Описание настройки отправки SMS модулем Авторизация 3.0' target='_blank'>настройка отправки SMS сообщений</a>!";

$MESS["BXMOD_AUTH_PARAM_EMAIL_CAPTCHA"] = "Выводить CAPTCHA при авторизации";
$MESS["BXMOD_AUTH_PARAM_AUTH_HEADING"] = "Общие настройки";
$MESS["BXMOD_AUTH_PARAM_EMAIL_CAPTCHA_COUNT"] = "Через сколько попыток неверной авторизации выводить CAPTCHA";
$MESS["BXMOD_AUTH_PARAM_EMAIL_RESTORE_CAPTCHA"] = "Выводить CAPTCHA при восстановлении пароля";
$MESS["BXMOD_AUTH_PARAM_EMAIL_REMIND"] = "Выводить галочку &laquo;запомнить меня&raquo;";
$MESS["BXMOD_AUTH_PARAM_PASSWORD_LENGTH_MIN"] = "Минимальная длинна пароля пользователя";
$MESS["BXMOD_AUTH_PARAM_PASSWORD_LENGTH_MAX"] = "Максимальная длинна пароля пользователя";

$MESS["BXMOD_AUTH_PARAM_GET_RESTORE_CHECKWORD"] = "Название параметра передачи USER_CHECKWORD при восстановлении доступа";
$MESS["BXMOD_AUTH_PARAM_GET_RESTORE_USER_ID"] = "Название параметра передачи USER_ID при восстановлении доступа";
$MESS["BXMOD_AUTH_PARAM_GET_CONFIRM_USER_ID"] = "Название параметра передачи USER_ID при подтверждении регистрации";
$MESS["BXMOD_AUTH_PARAM_GET_CONFIRM_CODE"] = "Название параметра передачи CONFIRM_CODE при подтверждении регистрации";

$MESS["BXMOD_AUTH_PARAM_PHONE_HEADING"] = "Настройки входа по номеру телефона";
$MESS["BXMOD_AUTH_PARAM_PHONE_MAX_SMS"] = "Максимальное количество SMS на один номер телефона в час";
$MESS["BXMOD_AUTH_PARAM_PHONE_SMS_CONFIRM_MSG"] = "Текст SMS сообщения при подтверждении регистрации";
$MESS["BXMOD_AUTH_PARAM_PHONE_SMS_CONFIRM_DEFAULT"] = "Ваш код активации: #CODE#";
$MESS["BXMOD_AUTH_PARAM_PHONE_SMS_RESTORE_MSG"] = "Текст SMS сообщения при восстановлении пароля";
$MESS["BXMOD_AUTH_PARAM_PHONE_SMS_RESTORE_DEFAULT"] = "Код восстановления пароля: #CODE#";
$MESS["BXMOD_AUTH_PARAM_PHONE_SMSRU_ID"] = "Ваш ID в системе SMS.ru";
$MESS["BXMOD_AUTH_PARAM_PHONE_SMSRU_FROM"] = "Имя отправителя в SMS сообщениях";

$MESS["BXMOD_AUTH_PARAM_SOC_FB"] = "Использовать вход через Facebook";
$MESS["BXMOD_AUTH_PARAM_SOC_FB_HEADING"] = "&laquo;Facebook&raquo;";
$MESS["BXMOD_AUTH_PARAM_SOC_FB_ID"] = "Идентификатор приложения (App ID)";
$MESS["BXMOD_AUTH_PARAM_SOC_FB_KEY"] = "Секретный код приложения (App Secret)";
$MESS["BXMOD_AUTH_PARAM_SOC_FB_MESS"] = "Посмотрите <a href='http://bxmod.ru/mods/avtorizatsiya-3-0/nastroyka-avtorizatsii-cherez-sots-servisy.html#FB' target='_blank' title='полные инструкции по подключению'>полные инструкции по подключению</a> на сайте <a href='http://bxmod.ru/mods/avtorizatsiya-3-0/' target='_blank' title='Модуль Авторизация 3.0 для 1С-Битрикс'>bxmod.ru</a>";

$MESS["BXMOD_AUTH_PARAM_SOC_GG"] = "Использовать вход через Google";
$MESS["BXMOD_AUTH_PARAM_SOC_GG_HEADING"] = "&laquo;Google&raquo;";
$MESS["BXMOD_AUTH_PARAM_SOC_GG_ID"] = "Идентификатор (Client ID)";
$MESS["BXMOD_AUTH_PARAM_SOC_GG_KEY"] = "Секретный код (Client secret)";
$MESS["BXMOD_AUTH_PARAM_SOC_GG_MESS"] = "Посмотрите <a href='http://bxmod.ru/mods/avtorizatsiya-3-0/nastroyka-avtorizatsii-cherez-sots-servisy.html#GG' target='_blank' title='полные инструкции по подключению'>полные инструкции по подключению</a> на сайте <a href='http://bxmod.ru/mods/avtorizatsiya-3-0/' target='_blank' title='Модуль Авторизация 3.0 для 1С-Битрикс'>bxmod.ru</a>";

$MESS["BXMOD_AUTH_PARAM_SOC_MR"] = "Использовать вход через Mail.ru";
$MESS["BXMOD_AUTH_PARAM_SOC_MR_HEADING"] = "&laquo;Mail.ru&raquo;";
$MESS["BXMOD_AUTH_PARAM_SOC_MR_ID"] = "ID сайта";
$MESS["BXMOD_AUTH_PARAM_SOC_MR_KEY"] = "Приватный ключ";
$MESS["BXMOD_AUTH_PARAM_SOC_MR_SECRET"] = "Секретный ключ";
$MESS["BXMOD_AUTH_PARAM_SOC_MR_MESS"] = "Посмотрите <a href='http://bxmod.ru/mods/avtorizatsiya-3-0/nastroyka-avtorizatsii-cherez-sots-servisy.html#MR' target='_blank' title='полные инструкции по подключению'>полные инструкции по подключению</a> на сайте <a href='http://bxmod.ru/mods/avtorizatsiya-3-0/' target='_blank' title='Модуль Авторизация 3.0 для 1С-Битрикс'>bxmod.ru</a>";

$MESS["BXMOD_AUTH_PARAM_SOC_OK"] = "Использовать вход через Одноклассники";
$MESS["BXMOD_AUTH_PARAM_SOC_OK_HEADING"] = "&laquo;Одноклассники&raquo;";
$MESS["BXMOD_AUTH_PARAM_SOC_OK_ID"] = "Application ID";
$MESS["BXMOD_AUTH_PARAM_SOC_OK_KEY"] = "Публичный ключ приложения";
$MESS["BXMOD_AUTH_PARAM_SOC_OK_SECRET"] = "Секретный ключ приложения";
$MESS["BXMOD_AUTH_PARAM_SOC_OK_MESS"] = "Посмотрите <a href='http://bxmod.ru/mods/avtorizatsiya-3-0/nastroyka-avtorizatsii-cherez-sots-servisy.html#OK' target='_blank' title='полные инструкции по подключению'>полные инструкции по подключению</a> на сайте <a href='http://bxmod.ru/mods/avtorizatsiya-3-0/' target='_blank' title='Модуль Авторизация 3.0 для 1С-Битрикс'>bxmod.ru</a>";

$MESS["BXMOD_AUTH_PARAM_SOC_VK"] = "Использовать вход через ВКонтакте";
$MESS["BXMOD_AUTH_PARAM_SOC_VK_HEADING"] = "&laquo;ВКонтакте&raquo;";
$MESS["BXMOD_AUTH_PARAM_SOC_VK_ID"] = "ID приложения";
$MESS["BXMOD_AUTH_PARAM_SOC_VK_KEY"] = "Защищенный ключ";
$MESS["BXMOD_AUTH_PARAM_SOC_VK_MESS"] = "Посмотрите <a href='http://bxmod.ru/mods/avtorizatsiya-3-0/nastroyka-avtorizatsii-cherez-sots-servisy.html#VK' target='_blank' title='полные инструкции по подключению'>полные инструкции по подключению</a> на сайте <a href='http://bxmod.ru/mods/avtorizatsiya-3-0/' target='_blank' title='Модуль Авторизация 3.0 для 1С-Битрикс'>bxmod.ru</a>";

$MESS["BXMOD_AUTH_PARAM_SOC_YA"] = "Использовать вход через Яндекс";
$MESS["BXMOD_AUTH_PARAM_SOC_YA_HEADING"] = "&laquo;Яндекс&raquo;";
$MESS["BXMOD_AUTH_PARAM_SOC_YA_ID"] = "ID приложения";
$MESS["BXMOD_AUTH_PARAM_SOC_YA_KEY"] = "Пароль приложения";
$MESS["BXMOD_AUTH_PARAM_SOC_YA_MESS"] = "Посмотрите <a href='http://bxmod.ru/mods/avtorizatsiya-3-0/nastroyka-avtorizatsii-cherez-sots-servisy.html#YA' target='_blank' title='полные инструкции по подключению'>полные инструкции по подключению</a> на сайте <a href='http://bxmod.ru/mods/avtorizatsiya-3-0/' target='_blank' title='Модуль Авторизация 3.0 для 1С-Битрикс'>bxmod.ru</a>";

$MESS["BXMOD_AUTH_PARAM_SOC_TW"] = "Использовать вход через Twitter";
$MESS["BXMOD_AUTH_PARAM_SOC_TW_HEADING"] = "&laquo;Twitter&raquo;";
$MESS["BXMOD_AUTH_PARAM_SOC_TW_ID"] = "API key";
$MESS["BXMOD_AUTH_PARAM_SOC_TW_KEY"] = "API secret";
$MESS["BXMOD_AUTH_PARAM_SOC_TW_MESS"] = "Посмотрите <a href='http://bxmod.ru/mods/avtorizatsiya-3-0/nastroyka-avtorizatsii-cherez-sots-servisy.html#TW' target='_blank' title='полные инструкции по подключению'>полные инструкции по подключению</a> на сайте <a href='http://bxmod.ru/mods/avtorizatsiya-3-0/' target='_blank' title='Модуль Авторизация 3.0 для 1С-Битрикс'>bxmod.ru</a>";

$MESS["BXMOD_AUTH_PARAM_SOC_FAIL"] = "Страница при неуспешной авторизации";
$MESS["BXMOD_AUTH_PARAM_SOC_ADDIT_HEADING"] = "Дополнительные настройки";

$MESS["BXMOD_AUTH_PARAM_SOC_ORDER"] = "Сортировка";
?>