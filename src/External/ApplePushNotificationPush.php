<?php

/*
 * @copyright Copyright (c) NotAgency
 */

namespace FourPaws\External;

class ApplePushNotificationPush extends \ApnsPHP_Push
{
    /**
     * Патч
     *
     * Проблема:
     * @see https://github.com/immobiliare/ApnsPHP/issues/88
     *
     * Решение проблемы:
     * @see https://github.com/immobiliare/ApnsPHP/issues/88#issuecomment-111251202
     *
     * @throws \ApnsPHP_Push_Exception
     * @throws \ApnsPHP_Message_Exception
     */
    public function send()
    {
        throw new \ApnsPHP_Push_Exception(
            'Test'
        );
        if (!$this->_hSocket) {
            throw new \ApnsPHP_Push_Exception(
                'Not connected to Push Notification Service'
            );
        }

        if (empty($this->_aMessageQueue)) {
            throw new \ApnsPHP_Push_Exception(
                'No notifications queued to be sent'
            );
        }

        $this->_aErrors = array();
        $nRun = 1;
        while (($nMessages = count($this->_aMessageQueue)) > 0) {
            $this->_log("INFO: Sending messages queue, run #{$nRun}: $nMessages message(s) left in queue.");

            $bError = false;
            foreach($this->_aMessageQueue as $k => &$aMessage) {
                if (function_exists('pcntl_signal_dispatch')) {
                    pcntl_signal_dispatch();
                }

                /**
                 * @var $message ApplePushNotificationMessage
                 */
                $message = $aMessage['MESSAGE'];
                $sCustomIdentifier = (string)$message->getCustomIdentifier();
                $sCustomIdentifier = sprintf('[custom identifier: %s]', empty($sCustomIdentifier) ? 'unset' : $sCustomIdentifier);

                $nErrors = 0;
                if (!empty($aMessage['ERRORS'])) {
                    foreach($aMessage['ERRORS'] as $aError) {
                        if ($aError['statusCode'] == 0) {
                            $this->_log("INFO: Message ID {$k} {$sCustomIdentifier} has no error ({$aError['statusCode']}), removing from queue...");
                            $this->_removeMessageFromQueue($k);
                            continue 2;
                        } else if ($aError['statusCode'] > 1 && $aError['statusCode'] <= 8) {
                            $this->_log("WARNING: Message ID {$k} {$sCustomIdentifier} has an unrecoverable error ({$aError['statusCode']}), removing from queue without retrying...");
                            $this->_removeMessageFromQueue($k, true);
                            continue 2;
                        }
                    }
                    if (($nErrors = count($aMessage['ERRORS'])) >= $this->_nSendRetryTimes) {
                        $this->_log(
                            "WARNING: Message ID {$k} {$sCustomIdentifier} has {$nErrors} errors, removing from queue..."
                        );
                        $this->_removeMessageFromQueue($k, true);
                        continue;
                    }
                }

                // START измененная часть кода
                if ($this->_nProtocol === self::PROTOCOL_HTTP) {
                    $nLen = strlen($message->getPayload());
                } else {
                    if ((int) ini_get('mbstring.func_overload') & 2) {
                        $nLen = mb_strlen($aMessage['BINARY_NOTIFICATION'], 'latin1');
                    } else {
                        $nLen = strlen($aMessage['BINARY_NOTIFICATION']);
                    }
                }
                // END измененная часть кода

                $this->_log("STATUS: Sending message ID {$k} {$sCustomIdentifier} (" . ($nErrors + 1) . "/{$this->_nSendRetryTimes}): {$nLen} bytes.");

                $aErrorMessage = null;

                if ($this->_nProtocol === self::PROTOCOL_HTTP) {
                    // потому что метод _httpSend - приватный, мы не можем использовать http для отправки в этом патче
                    /*
                    if (!$this->_httpSend($message, $sReply)) {
                        $aErrorMessage = array(
                            'identifier' => $k,
                            'statusCode' => curl_getinfo($this->_hSocket, CURLINFO_HTTP_CODE),
                            'statusMessage' => $sReply
                        );
                    }
                    */
                } else {
                    if ($nLen !== ($nWritten = (int)@fwrite($this->_hSocket, $aMessage['BINARY_NOTIFICATION']))) {
                        $aErrorMessage = array(
                            'identifier' => $k,
                            'statusCode' => self::STATUS_CODE_INTERNAL_ERROR,
                            'statusMessage' => sprintf('%s (%d bytes written instead of %d bytes)',
                                $this->_aErrorResponseMessages[self::STATUS_CODE_INTERNAL_ERROR], $nWritten, $nLen
                            )
                        );
                    }
                }

                usleep($this->_nWriteInterval);

                $bError = $this->_updateQueue($aErrorMessage);
                if ($bError) {
                    break;
                }
            }

            if (!$bError) {
                if ($this->_nProtocol === self::PROTOCOL_BINARY) {
                    $read = array($this->_hSocket);
                    $null = NULL;
                    $nChangedStreams = @stream_select($read, $null, $null, 0, $this->_nSocketSelectTimeout);
                    if ($nChangedStreams === false) {
                        $this->_log('ERROR: Unable to wait for a stream availability.');
                        break;
                    } else if ($nChangedStreams > 0) {
                        $bError = $this->_updateQueue();
                        if (!$bError) {
                            $this->_aMessageQueue = array();
                        }
                    } else {
                        $this->_aMessageQueue = array();
                    }
                } else {
                    $this->_aMessageQueue = array();
                }
            }

            $nRun++;
        }
    }
}