<?php

/*
 * @copyright Copyright (c) NotAgency
 */

namespace FourPaws\External;

class ApplePushNotificationServer extends \ApnsPHP_Push_Server
{
    /**
     * Патч
     *
     * parent::send() заменен на $this->send()
     * чтобы вызывался пропатченный метод
     *
     * также был заменен parent::add($message) на \ApnsPHP_Push::add($message);
     * так как родитель изменен
     *
     * @throws \ApnsPHP_Push_Exception
     * @throws \ApnsPHP_Message_Exception
     */
    protected function _mainLoop()
    {
        while (true) {
            pcntl_signal_dispatch();

            if (posix_getppid() != $this->_nParentPid) {
                $this->_log("INFO: Parent process {$this->_nParentPid} died unexpectedly, exiting...");
                break;
            }

            sem_acquire($this->_hSem);
            $this->_setQueue(self::SHM_ERROR_MESSAGES_QUEUE_KEY, 0,
                array_merge($this->_getQueue(self::SHM_ERROR_MESSAGES_QUEUE_KEY), parent::getErrors())
            );

            $aQueue = $this->_getQueue(self::SHM_MESSAGES_QUEUE_KEY_START, $this->_nCurrentProcess);
            var_dump($aQueue);
            foreach($aQueue as $message) {
                \ApnsPHP_Push::add($message);
            }
            $this->_setQueue(self::SHM_MESSAGES_QUEUE_KEY_START, $this->_nCurrentProcess);
            sem_release($this->_hSem);

            $nMessages = count($aQueue);
            if ($nMessages > 0) {
                $this->_log('INFO: Process ' . ($this->_nCurrentProcess + 1) . " has {$nMessages} messages, sending...");
                // измененная строка относительно родительского метода
                $this->send();
            } else {
                usleep(self::MAIN_LOOP_USLEEP);
            }
        }
    }

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