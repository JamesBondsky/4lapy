<?php

/*
 * @copyright Copyright (c) NotAgency
 */

namespace FourPaws\External;

class ApplePushNotificationMessage extends \ApnsPHP_Message
{
    /**
     * Патч
     * Метод - точная копия родителя за исключением закоментированного кода
     * @return mixed
     * @throws \ApnsPHP_Message_Exception
     */
    public function getPayload()
    {
        $sJSON = json_encode($this->_getPayload(), 0);

        //КОСТЫЛЬ со старого сайта с комментарием: "тут мы убиваем декод кириллицы"
        //по факту без комментирования этих строк отправка пушей кириллицей не работает...
        /*
        $sJSON = json_encode($this->_getPayload(), defined('JSON_UNESCAPED_UNICODE') ? JSON_UNESCAPED_UNICODE : 0);
        if (!defined('JSON_UNESCAPED_UNICODE') && function_exists('mb_convert_encoding')) {
            $sJSON = preg_replace_callback(
                '~\\\\u([0-9a-f]{4})~i',
                create_function('$aMatches', 'return mb_convert_encoding(pack("H*", $aMatches[1]), "UTF-8", "UTF-16");'),
                $sJSON);
        }
        */

        $sJSONPayload = str_replace(
            '"' . self::APPLE_RESERVED_NAMESPACE . '":[]',
            '"' . self::APPLE_RESERVED_NAMESPACE . '":{}',
            $sJSON
        );

        $nJSONPayloadLen = strlen($sJSONPayload);

        if ($nJSONPayloadLen > self::PAYLOAD_MAXIMUM_SIZE) {
            if ($this->_bAutoAdjustLongPayload) {
                $nMaxTextLen = $nTextLen = strlen($this->_sText) - ($nJSONPayloadLen - self::PAYLOAD_MAXIMUM_SIZE);
                if ($nMaxTextLen > 0) {
                    while (strlen($this->_sText = mb_substr($this->_sText, 0, --$nTextLen, 'UTF-8')) > $nMaxTextLen);
                    return $this->getPayload();
                } else {
                    throw new \ApnsPHP_Message_Exception(
                        "JSON Payload is too long: {$nJSONPayloadLen} bytes. Maximum size is " .
                        self::PAYLOAD_MAXIMUM_SIZE . " bytes. The message text can not be auto-adjusted."
                    );
                }
            } else {
                throw new \ApnsPHP_Message_Exception(
                    "JSON Payload is too long: {$nJSONPayloadLen} bytes. Maximum size is " .
                    self::PAYLOAD_MAXIMUM_SIZE . " bytes"
                );
            }
        }

        return $sJSONPayload;
    }
}