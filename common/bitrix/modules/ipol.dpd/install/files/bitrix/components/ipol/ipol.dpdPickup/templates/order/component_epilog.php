<?php
CJSCore::RegisterExt('ipolhDpdMap', array(
	'js'   => $templateFolder .'/map.js',
	'lang' => $templateFolder .'/lang/'. LANGUAGE_ID .'/template.php',
	'rel'  => array('ajax', 'popup'),
));

CJSCore::Init('ipolhDpdMap');