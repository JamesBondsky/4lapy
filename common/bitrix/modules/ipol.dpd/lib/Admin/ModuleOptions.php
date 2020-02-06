<?php
namespace Ipolh\DPD\Admin;

use \Bitrix\Main\Config\Option;
use \Ipolh\DPD\Admin\Form\AbstractForm;

class ModuleOptions extends AbstractForm
{
	protected $formName = 'IPOLH_DPD_OPTIONS';

	/**
	 * Конструктор класса
	 * 
	 * @param string $moduleId
	 * @param array  $fields
	 */
	public function __construct($moduleId, array $fields)
	{
		$this->moduleId  = $moduleId;
		$this->actionUrl = $GLOBALS['APPLICATION']->GetCurPageParam("mid={$this->moduleId}");
		$this->fields    = $fields;
		
		$this->loadItem();
	}

	/**
	 * Загружает редактируемую запись из БД
	 * 
	 * @return void
	 */
	protected function loadItem()
	{
		$this->editItem = new \ArrayObject($this->loadValues($this->fields));
	}

	/**
	 * Сохраняет редактируемую запись в БД
	 * 
	 * @return void
	 */
	protected function saveItem()
	{
		foreach ($this->editItem as $field => $value) {
			$value = is_array($value) ? \serialize($value) : $value;
			Option::set($this->moduleId, $field, $value);
		}

		if (\Ipolh\DPD\API\User::isActiveAccount()
			&& Option::get($this->moduleId, 'LOAD_EXTERNAL_DATA', 'N') != 'Y'
		) {
			LocalRedirect('/bitrix/admin/ipolh_dpd_load_external_data.php');
			exit;
		}
	}

	/**
	 * Получает значение всех опций
	 * 
	 * @param  array  $aTabs
	 * @return array
	 */
	protected function loadValues($aTabs)
	{	
		$values = array();

		foreach ($aTabs as $aTab) {
			$controls = is_callable($aTab['CONTROLS']) ? call_user_func($aTab['CONTROLS']) : $aTab['CONTROLS'];
			foreach ($controls as $controlName => $controlData) {
				if ($controlData['TYPE'] == 'TABS') {
					$items  = is_callable($controlData['ITEMS']) ? call_user_func($controlData['ITEMS'], array()) : $controlData['ITEMS'];
					$values = array_merge($values, $this->loadValues($items));
				} else {
					$value = Option::get($this->moduleId, $controlName, $controlData['DEFAULT'] ?: '');

					if ($controlData['MULTIPLE']) {
						$value = unserialize($value) ?: array();
					}

					$values[$controlName] = $value;
				}
			}
		}

		return $values;
	}
}