<?php
namespace Ipolh\DPD\Admin\Form;

use \Bitrix\Main\Result;
use \Bitrix\Main\Error;
use \Ipolh\DPD\Utils;

abstract class AbstractForm
{
	/**
	 * action url формы
	 * @var string
	 */
	protected $actionUrl;

	/**
	 * Имя формы
	 * @var string
	 */
	protected $formName;

	/**
	 * Массив полей формы
	 * 
	 * @var array
	 */
	protected $fields = array();

	/**
	 * Массив валидаторов, для простой формы
	 * @var array
	 */
	protected $validators = false;

	/**
	 * @var mixed
	 */
	protected $editItem;

	/**
	 * Отрисовщик формы
	 * 
	 * @var \Ipolh\DPD\Admin\Form\Renderer;
	 */
	protected $renderer;

	/**
	 * Возвращает action url формы
	 * 
	 * @return string
	 */
	public function getActionUrl()
	{
		return $this->renderer()->getActionUrl();
	}

	/**
	 * Возвращает action url формы
	 * 
	 * @return this
	 */
	public function setActionUrl($url)
	{
		$this->renderer()->setActionUrl($url);

		return $this;
	}

	/**
	 * Возвращает набор полей формы
	 * 
	 * @return array
	 */
	public function getFields()
	{
		return $this->fields;
	}

	/**
	 * Возвращает массив валидаторов, применить которые нужно перед сохранением самостоятельно
	 * 
	 * @param  boolean $items [description]
	 * @return [type]         [description]
	 */
	public function getValidators($items = false)
	{
		if ($this->validators === false || $items !== false) {
			$ret   = array();
			$items = $items ?: $this->fields;

			foreach ($items as $tab) {
				$controls = is_callable($tab['CONTROLS']) ? call_user_func($tab['CONTROLS']) : $tab['CONTROLS'];

				foreach ($controls as $name => $control) {
					if ($control['TYPE'] == 'TABS') {
						$items = is_callable($control['ITEMS']) ? call_user_func($control['ITEMS'], array()) : $control['ITEMS'];
						$ret = array_merge($ret, $this->getValidators($items));
					} elseif ($control['VALIDATORS']) {
						$ret[$name] = $control['VALIDATORS'];
					}
				}
			}

			$this->validators = $ret;
		}

		return $this->validators;
	}

	/**
	 * Возвращает имена опций
	 * 
	 * @return array
	 */
	public function getFieldNames()
	{
		return array_keys($this->getEditItem()->getArrayCopy());
	}

	/**
	 * Загружает редактируемую запись из БД
	 * 
	 * @return void
	 */
	abstract protected function loadItem();

	/**
	 * Сохраняет редактируемую запись в БД
	 * 
	 * @return void
	 */
	abstract protected function saveItem();

	/**
	 * Возвращает ссылку на редактируемую запись
	 * 
	 * @return \ArrayAccess
	 */
	public function getEditItem()
	{
		return $this->editItem;
	}

	/**
	 * Устанавливает ссылку на редактируемую запись
	 * 
	 * @param \ArrayAccess $item
	 */
	public function setEditItem($item)
	{
		$this->editItem = $item;

		return $this;
	}

	public function getRenderer()
	{
		if (is_null($this->renderer)) {
			$this->renderer = new Renderer($this->formName, $this->actionUrl, $this->getFields(), $this->getEditItem(), $this->getValidators());
		}

		return $this->renderer;
	}

	/**
	 * Обрабатывает форму и отрисовывает форму
	 * 
	 * @return void
	 */
	public function processAndShow()
	{
		if (($result = $this->processRequest())) {
			print $this->render($result);
		} else {
			print $this->render();
		}
	}

	/**
	 * Обрабатывает форму
	 * 
	 * @return mixed возвращает
	 *               - false если текущий запрос не нуждается в обработке
	 *               - array массив ошибок
	 *               - true  если текущий запрос обработан удачно
	 */
	public function processRequest()
	{
		if ($_SERVER['REQUEST_METHOD'] != 'POST') {
			return null;
		}

		if (!check_bitrix_sessid()) {
			return null;
		}

		if (!empty($_SERVER['HTTP_BX_AJAX']) && $_SERVER['HTTP_BX_AJAX']) {
			$data = Utils::convertEncoding($_REQUEST[$this->formName], 'UTF-8', SITE_CHARSET);
		} else {
			$data = $_REQUEST[$this->formName];
		}

		$result = new Result; 
		foreach($this->getFieldNames() as $fieldName) {
			if (isset($data[$fieldName]) && isset($this->editItem[$fieldName])) {
				$this->editItem[$fieldName] = $value =  $data[$fieldName];

				if ($errors = $this->validate($fieldName, $value)) {
					$result->addErrors($errors);
				}
			}
		}

		return $result->isSuccess() ? $this->saveItem() : $result;
	}

	/**
	 * Отрисовывает форму
	 * 
	 * @param  mixed $errors массив ошибок или false
	 * @return string
	 */
	public function render(\Bitrix\Main\Result $result = null)
	{
		return $this->getRenderer()->render($result);
	}

	/**
	 * Проверяет поле
	 * 
	 * @param  string $fieldName
	 * @param  string $value
	 * @return array
	 */
	protected function validate($fieldName, $value)
	{
		$errors = array();
		
		$validators = $this->getValidators();
		if (!isset($validators[$fieldName])) {
			return $errors;
		}

		foreach ($validators[$fieldName] as $name => $callback) {
			if (is_callable($callback)) {
				$error = $callback($fieldName, $value, $this);
			} elseif ($name == 'required' && !trim($value)) {
				$error = $callback;
			}

			if ($error) {
				$errors[] = $error instanceof Error ? $error : new Error($error);
			}
		}

		return $errors;
	}
}