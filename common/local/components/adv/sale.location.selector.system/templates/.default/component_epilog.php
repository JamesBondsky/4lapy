<?php
/**
 * Created by PhpStorm.
 * User: Vampi
 * Date: 20.11.2017
 * Time: 17:31
 */
CJSCore::Init();
//$this->getTemplate()->addExternalJs('/bitrix/js/sale/core_ui_widget.js');
//$this->getTemplate()->addExternalJs('/bitrix/js/sale/core_ui_etc.js');
//$this->getTemplate()->addExternalJs('/bitrix/js/sale/core_ui_autocomplete.js');
//$this->getTemplate()->addExternalJs('/bitrix/js/sale/core_ui_itemtree.js');
//$this->getTemplate()->addExternalJs('/local/templates/.default/components/bitrix/system.field.edit/sale_location/_script.js');

// to be able to launch this outside the admin section
$this->getTemplate()->addExternalCss('/bitrix/panel/main/adminstyles_fixed.css');
$this->getTemplate()->addExternalCss('/bitrix/panel/main/admin.css');
$this->getTemplate()->addExternalCss('/bitrix/panel/main/admin-public.css');
//$this->getTemplate()->addExternalCss('/local/templates/.default/components/bitrix/system.field.edit/sale_location/_style.css');
//$this->getTemplate()->addExternalCss($this->getTemplate()->GetFolder().'/_style.css');
