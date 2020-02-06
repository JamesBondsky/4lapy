<?php
return array(
	array(
		'callback' => '\\Ipolh\\DPD\\Agents::checkOrderStatus();',
		'interval' => 600,
	),

	array(
		'callback'  => '\\Ipolh\\DPD\\Agents::loadExternalData();',
		'interval'  => 86400,
		'next_exec' => date('d.m.Y') .' 23:00:00',
	),
);