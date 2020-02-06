<?php
return array(
	array(
		'module'   => 'sale',
		'name'     => 'onSaleDeliveryHandlersBuildList',
		'callback' => array('\\Ipolh\\DPD\\Delivery\\DPD', 'Init'),
		'sort'     => 100,
		'path'     => '',
		'args'     => array(),
	),

	array(
		'module'   => 'sale',
		'name'     => 'OnSaleComponentOrderOneStepComplete',
		'callback' => array('\\Ipolh\\DPD\\EventListener', 'saveDeliveryInfo'),
		'sort'     => 100,
		'path'     => '',
		'args'     => array(),
	),

	array(
		'module'   => 'sale',
		'name'     => 'OnSaleComponentOrderComplete',
		'callback' => array('\\Ipolh\\DPD\\EventListener', 'saveDeliveryInfo'),
		'sort'     => 100,
		'path'     => '',
		'args'     => array(),
 	),

 	array(
 		'module'   => 'main',
 		'name'     => 'OnEpilog',
 		'callback' => array('\\Ipolh\\DPD\\EventListener', 'showAdminForm'),
 		'sort'     => 100,
 		'path'     => '',
 		'args'     => array(),
 	),
);