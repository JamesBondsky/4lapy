<?
class error extends \message_base
{
	public function getMessages()
	{
		return array(
			'user_register_no_login' => 'Не указан логин',
			'user_register_no_password' => 'Не указан пароль',
			'user_register_no_token' => 'Не указан токен',
			'user_register_busy_login' => 'Пользователь с такими данными уже зарегистрирован',
			'user_register_error_register' => 'Ошибка регистрации',
		);
	}
}