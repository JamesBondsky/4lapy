<?php
namespace Ipolh\DPD\DB;

use \Bitrix\Main\SystemException;
use \Ipolh\DPD\Utils;

/**
 * Абстрактный класс модели таблицы
 * Каждый экземпляр класса - одна строка из таблицы
 *
 * К значениям полей можно обратиться двумя способами
 *
 * - как к св-ву объекта, в этом случае перед чтением/записи св-ва
 *   будет произведен поиск метода setPopertyName/getPropertyName 
 *   и если они есть они будут вызваны и возвращен результат этого вызова
 *
 * - как к массиву, в этом случае данные будут записаны/возвращены как есть
 */
abstract class AbstractModel implements \ArrayAccess
{
	/**
	 * Поля записи
	 * @var array
	 */
	protected $fields = false;

	/**
	 * Должен возвращать класс Entity\DataManager 
	 */
	abstract public static function DataManager();

	/**
	 * Вызывает метод у DataManager
	 */
	public static function CallDataManager()
	{
		$args = func_get_args();
		$method = array_shift($args);
		$callback = array(static::DataManager(), $method);

		return call_user_func_array($callback, $args);
	}

	public static function GetFields()
	{
		$ret = array();
		$fields = static::CallDataManager('getMap');
		foreach($fields as $field) {
			$ret[$field->getColumnName()] = $field->getDefaultValue();
		}

		return $ret;
	}

	/**
	 * Конструктор класса
	 * 
	 * @param mixed $id ID или массив полей сущности
	 */
	public function __construct($id = false)
	{
		$this->fields = static::GetFields();
		$this->load($id);
	}

	/**
	 * Получает поля сущности из БД
	 * 
	 * @param  mixed $id ID или массив полей сущности
	 * @return bool
	 */
	public function load($id)
	{
		if (!$id) {
			return false;
		}

		if (is_array($id)) {
			$data = $id;
		} else {
			$data = static::CallDataManager('getList', array(
				'filter' => array('=ID' => $id)
			))->Fetch();
		}

		if (!$data) {
			return false;
		}

		$this->fields = $data;
		$this->afterLoad();

		return true;
	}

	/**
	 * Вызывается после получения полей сущности из БД
	 * @return void
	 */
	public function afterLoad()
	{}

	/**
	 * Добавляет запись в таблицу
	 * 
	 * @return bool
	 */
	public function insert()
	{
		if ($this->id) {
			throw new SystemException('Record is exists');
		}

		$ret = static::CallDataManager('add', $this->fields);
		
		if ($ret->isSuccess()) {
			$this->fields = $ret->getData();
			$this->id = $ret->getId();
		}
		
		return $ret;
	}

	/**
	 * Обновляет запись в таблице
	 * 
	 * @return bool
	 */
	public function update()
	{
		if (!$this->id) {
			throw new SystemException('Record is not exists');
		}

		$ret = static::CallDataManager('update', $this->id, $this->fields);

		if ($ret->isSuccess()) {
			$this->fields = $ret->getData();
		}

		return $ret;
	}

	/**
	 * Сохраняет запись вне зависимости от ее состояния
	 * 
	 * @return bool
	 */
	public function save()
	{
		if ($this->id) {
			return $this->update();
		}

		return $this->insert();
	}

	/**
	 * Удаляет запись из таблицы
	 * 
	 * @return bool
	 */
	public function delete()
	{
		if (!$this->id) {
			throw new SystemException('Record is not exists');
		}

		$ret = static::CallDataManager('delete', $this->id);

		if ($ret->isSuccess()) {
			$this->id = null;
		}

		return $ret;
	}

	/**
	 * Возвращает представление записи в виде массива
	 * 
	 * @return array
	 */
	public function getArrayCopy()
	{
		return $this->fields;
	}

	/**
	 * Проверяет существование св-ва
	 * 
	 * @param  string  $prop
	 * @return boolean
	 */
	public function __isset($prop)
	{
		$prop = Utils::camelCaseToUnderScore($prop);

		return array_key_exists($prop, $this->fields);
	}

	/**
	 * Удаляет св-во сущности
	 * 
	 * @param string $prop
	 */
	public function __unset($prop)
	{
		throw new SystemException("Can\'t be removed property {$prop}");
	}

	/**
	 * Получает значение св-ва сущности
	 * 
	 * @param  string $prop
	 * @return mixed
	 */
	public function __get($prop)
	{
		$method = 'get'. Utils::UnderScoreToCamelCase($prop, true);
		if (method_exists($this, $method)) {
			return $this->$method();
		}

		$prop = Utils::camelCaseToUnderScore($prop);
		if (!$this->__isset($prop)) {
			throw new SystemException("Missing property {$prop}");
		}

		return $this->fields[$prop];
	}

	/**
	 * Задает значение св-ва сущности
	 * 
	 * @param string $prop
	 * @param mixed $value
	 */
	public function __set($prop, $value)
	{
		$method = 'set'. Utils::UnderScoreToCamelCase($prop, true);
		if (method_exists($this, $method)) {
			return $this->$method($value);
		}

		$prop = Utils::camelCaseToUnderScore($prop);
		if (!$this->__isset($prop)) {
			throw new SystemException("Missing property {$prop}");
		}

		$this->fields[$prop] = $value;
	}

	public function offsetExists($prop)
	{
		return $this->__isset($prop);
	}

	public function offsetUnset($prop)
	{
		throw new SystemException("Can\'t be removed property {$prop}");
	}

	public function offsetGet($prop)
	{
		if (!$this->offsetExists($prop)) {
			throw new SystemException("Missing property {$prop}");
		}

		return $this->fields[$prop];
	}

	public function offsetSet($prop, $value)
	{
		if (!$this->offsetExists($prop)) {
			throw new SystemException("Missing property {$prop}");
		}

		$this->fields[$prop] = $value;
	}
}