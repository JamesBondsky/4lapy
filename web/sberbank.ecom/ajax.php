<?php

/**
 * Скрипт стандартного модуля sberbank.ecom
 */

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';

if ($_REQUEST['check_https'] === 'Y') {
    $arResult = [
        'SUCCESS' => 'N',
    ];
    
    $ch = curl_init();
    
    $options = [
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_URL            => 'https://' . $_SERVER['SERVER_NAME'],
        CURLOPT_HEADER         => false,
        CURLOPT_SSLVERSION     => CURL_SSLVERSION_TLSv1_2,
    ];
    curl_setopt_array($ch, $options);
    
    $bResult1 = curl_exec($ch);
    
    $options = [
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_URL            => 'https://' . $_SERVER['SERVER_NAME'],
        CURLOPT_SSLVERSION     => CURL_SSLVERSION_TLSv1,
    ];
    
    curl_setopt_array($ch, $options);
    
    $bResult2 = curl_exec($ch);
    
    curl_close($ch);
    
    if ($bResult1 && $bResult2) {
        $arResult['SUCCESS'] = 'Y';
    }
    
    echo CUtil::PhpToJSObject($arResult);
}

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_after.php';
