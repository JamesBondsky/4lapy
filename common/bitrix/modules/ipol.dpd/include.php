<?php
define('IPOLH_DPD_MODULE', 'ipol.dpd');
define('IPOLH_DPD_CACHE_TIME', 86400);

\Bitrix\Main\Loader::includeModule('sale');
\Bitrix\Main\Loader::registerAutoLoadClasses(IPOLH_DPD_MODULE, array(
	'\\Ipolh\\DPD\\API\\User'                    => 'lib/API/User.php',
	'\\Ipolh\\DPD\\API\\Client\\ClientInterface' => 'lib/API/Client/ClientInterface.php',
	'\\Ipolh\\DPD\\API\\Client\\Soap'            => 'lib/API/Client/Soap.php',
	'\\Ipolh\\DPD\\API\\Client\\Factory'         => 'lib/API/Client/Factory.php',
	'\\Ipolh\\DPD\\API\\Service\\Geography'      => 'lib/API/Service/Geography.php',
	'\\Ipolh\\DPD\\API\\Service\\Calculator'     => 'lib/API/Service/Calculator.php',
	'\\Ipolh\\DPD\\API\\Service\\Order'          => 'lib/API/Service/Order.php',
	'\\Ipolh\\DPD\\API\\Service\\LabelPrint'     => 'lib/API/Service/LabelPrint.php',
	'\\Ipolh\\DPD\\API\\Service\\Tracking'       => 'lib/API/Service/Tracking.php',

	'\\Ipolh\\DPD\\Utils'                        => 'lib/Utils.php',
	'\\Ipolh\\DPD\\Shipment'                     => 'lib/Shipment.php',
	'\\Ipolh\\DPD\\Calculator'                   => 'lib/Calculator.php',

	'\\Ipolh\\DPD\\EventListener'                => 'lib/EventListener.php',
	'\\Ipolh\\DPD\\Agents'                       => 'lib/Agents.php',
	'\\Ipolh\\DPD\\Order'                        => 'lib/Order.php',

	'\\Ipolh\\DPD\\Admin\\Form\\Renderer'        => 'lib/Admin/Form/Renderer.php',
	'\\Ipolh\\DPD\\Admin\\Form\\AbstractForm'    => 'lib/Admin/Form/AbstractForm.php',
	'\\Ipolh\\DPD\\Admin\\ModuleOptions'         => 'lib/Admin/ModuleOptions.php',
	'\\Ipolh\\DPD\\Admin\\Order\\Edit'           => 'lib/Admin/Order/Edit.php',

	'\\Ipolh\\DPD\\DB\\AbstractModel'            => 'lib/DB/AbstractModel.php',
	'\\Ipolh\\DPD\\DB\\Order\Table'              => 'lib/DB/Order/Table.php',
	'\\Ipolh\\DPD\\DB\\Order\Model'              => 'lib/DB/Order/Model.php',

	'\\Ipolh\\DPD\\DB\\Location\Table'           => 'lib/DB/Location/Table.php',
	'\\Ipolh\\DPD\\DB\\Location\Agent'           => 'lib/DB/Location/Agent.php',

	'\\Ipolh\\DPD\\DB\\Terminal\Table'           => 'lib/DB/Terminal/Table.php',
	'\\Ipolh\\DPD\\DB\\Terminal\Model'           => 'lib/DB/Terminal/Model.php',
	'\\Ipolh\\DPD\\DB\\Terminal\Agent'           => 'lib/DB/Terminal/Agent.php',

	'\\Ipolh\\DPD\\Delivery\\DPD'                => 'delivery/dpd.php',
));

\CJSCore::RegisterExt('ipolh_dpd_admin_order_detail', array(
	'js' => '/bitrix/js/'. IPOLH_DPD_MODULE .'/admin-order-detail.js',
	'lang' => '/bitrix/modules/'. IPOLH_DPD_MODULE .'/lang/'. LANGUAGE_ID .'/js/admin-order-detail.php',
	'rel' => array('ajax' ,'popup')
));