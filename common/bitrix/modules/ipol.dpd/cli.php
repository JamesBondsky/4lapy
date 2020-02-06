<?php
use \Bitrix\Main\Config\Option;

$_SERVER['DOCUMENT_ROOT'] = realpath(__DIR__ .'/../../../');

require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');

CModule::IncludeModule('ipol.dpd');

$step = Option::get(IPOLH_DPD_MODULE, 'LOAD_EXTERNAL_DATA_STEP', 'LOAD_LOCATION_ALL');

print 'PROCESS START '. date('d.m.Y H:i:s') . PHP_EOL;
print 'CURRENT STEP: '. $step . PHP_EOL;

\Ipolh\DPD\Agents::loadExternalData();

if ($step == 'LOAD_FINISH') {
	print 'COMPLETED';
} else {
	print 'TO CONTINUE, RESTART THIS SCRIPT';
}

print PHP_EOL;