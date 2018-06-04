<?php

namespace FourPaws\External\SmsTraffic\Sms;

/**
 * Interface ConstInterface
 */
interface ConstInterface
{
    /**
     * Date format for API request
     */
    public const DATE_FORMAT = 'Y-m-d H:i:s';
    
    /**
     * All cp1251 symbols will be converted to latin symbols
     */
    public const ENCODING_LATIN = 0;
    
    /**
     * cp1251 sms message
     */
    public const ENCODING_CP1251 = 1;
    
    /**
     * UTF-8 sms message
     */
    public const ENCODING_UTF8 = 5;
    
    /**
     * Sms Traffic Login
     */
    public const PARAMETER_LOGIN = 'login';
    
    /**
     * Sms Traffic Password
     */
    public const PARAMETER_PASSWORD = 'password';
    
    /**
     * List of phone or if individual_messages is on, then phones + messages
     */
    public const PARAMETER_PHONES = 'phones';
    
    /**
     * Sms message
     */
    public const PARAMETER_MESSAGE = 'message';
    
    /**
     * Route
     */
    public const PARAMETER_ROUTE = 'route';
    
    /**
     * Encoding, see self::ENCODING_*
     */
    public const PARAMETER_ENCODING = 'rus';
    
    /**
     * Sender name
     */
    public const PARAMETER_ORIGINATOR = 'originator';
    
    /**
     * Message is flash message
     *
     * @see https://en.wikipedia.org/wiki/Short_Message_Service#Flash_SMS
     */
    public const PARAMETER_FLASH = 'flash';
    
    /**
     * Date, when sms message will be sent
     */
    public const PARAMETER_START_DATE = 'start_date';
    
    /**
     * Maximum parts
     */
    public const PARAMETER_MAX_PARTS = 'max_parts';
    
    /**
     * Crop message automatically, if message length is more
     * than 160 symbols for latin encoding and 70 symbols for others
     */
    public const PARAMETER_AUTOTRUNCATE = 'autotruncate';
    
    /**
     * Delay between message sending, defined in seconds
     */
    public const PARAMETER_GAP = 'gap';
    
    /**
     * If the parameter is set, the phones parameter will be ignored and message
     * will be sent to all phones of the group
     */
    public const PARAMETER_GROUP = 'group';
    
    /**
     * Sms lifetime in seconds
     */
    public const PARAMETER_TIMEOUT = 'timeout';
    
    /**
     * Include Sms Traffic Id to an API answer.
     */
    public const PARAMETER_WANT_SMS_IDS = 'want_sms_ids';
    
    /**
     * Send individual messages
     */
    public const PARAMETER_INDIVIDUAL_MESSAGES = 'individual_messages';
    
    /**
     * User Data Header
     */
    public const PARAMETER_UDH = 'udh';
    
    /**
     * URL for wap push messages
     */
    public const PARAMETER_WAP_PUSH_URL = 'wap_push_url';
    
    /**
     * If parameter is set, platform will create special page and send its URL to a customer
     */
    public const PARAMETER_PUT_TEXT_INTO_WAP_PUSH = 'put_text_into_wap_push';
    
    /**
     * The same as above, plus customer receive first part of the message
     */
    public const PARAMETER_PUT_TEXT_INTO_WAP_REF = 'put_text_into_wap_ref';
}
