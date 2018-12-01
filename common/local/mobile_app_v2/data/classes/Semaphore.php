<?php

// Класс для разделения доступа к базе между скриптами обновляющими её по расписанию
class Semaphore extends BaseMethod
{
	// Ключи процессов, которыми управляем семаформ
	const KEY_DELIVERY_STAT = 'DELIVERY_STAT'; // delivery_stat_update.php
	const KEY_DELIVERY_INIT = 'DELIVERY_INIT'; // delivery_init.php
	const KEY_CHECK_SERVERS = 'CHECK_SERVERS'; // check_server.php
	const KEY_CHECK_SENDER_TASK = 'CHECK_SENDER_TASK'; // check_worker.php ($workerSubscriber->checkSenderTask())
	const KEY_ARCHIVE_POOL = 'ARCHIVE_POOL'; // delivery_pool_log_success_to_archive.php
	const KEY_DOMAIN_PAYMENT = 'DOMAIN_PAYMENT'; // domain_payment.php
	const KEY_DOMAIN_CHECKER = 'DOMAIN_CHECKER'; // domain_checker.php
	const KEY_MONTHLY_FINANCIAL_STATEMENTS = 'MONTHLY_FINANCIAL_STATEMENTS'; // monthly_financial_statements.php

	public function __construct($conf) {
		parent::__construct(['conf' => $conf, 'param' => null]);
	}

	public function check($key, $processIntervalSec = 30) {
		// Страховка от дедлоков
		$dtCurUpdate = date('Y-m-d H:i:s', time());
		$isCanProcess = false;

		$oldSemaphore = App::db()->fetchAssoc(
			'SELECT `dt`, `server`, (`dt` + INTERVAL '.$processIntervalSec.' SECOND) as `dt_allowing`'
			.' FROM `semaphore`'
			.' WHERE `key`=:key;',
			['key' => $key]
		);
		if($oldSemaphore) {
			if(strtotime($oldSemaphore['dt_allowing']) <= time()) {
				// Обновление существующего семафора
				App::db()->getPDO()->beginTransaction();
				try {
					App::db()->query(
						'UPDATE `semaphore` SET `dt`=:cur_dt, `server`=:server'
						.' WHERE `key`=:key AND `dt`=:old_dt AND `server`=:old_server;',
						[
							'key' => $key,
							'server' => $this->conf['server']['cur_server_domain'],
							'cur_dt' => $dtCurUpdate,
							'old_dt' => $oldSemaphore['dt'],
							'old_server' => $oldSemaphore['server'],
						]
					);
					App::db()->getPDO()->commit();
					sleep(2); // задержка чтобы в базу успели дозанестись все записи со временем $dtCurUpdate

					$semaphore = App::db()->fetchAssoc(
						'SELECT `dt`, `server`'
						.' FROM `semaphore` WHERE `key`=:key;',
						['key' => $key]
					);
					if($semaphore['dt'] == $dtCurUpdate && $semaphore['server'] == $this->conf['server']['cur_server_domain']) {
						$isCanProcess = true;
					}
				} catch(PDOExecption $e) {
					App::db()->getPDO()->rollback();
				}
			}
		} else {
			// Добавление семафора с новым ключем
			App::db()->getPDO()->beginTransaction();
			try {
				App::db()->query(
					'INSERT INTO `semaphore` (`key`, `dt`, `server`)'
					.' VALUES (:key, :cur_dt, :server);',
					[
						'key' => $key,
						'server' => $this->conf['server']['cur_server_domain'],
						'cur_dt' => $dtCurUpdate,
					]
				);
				App::db()->getPDO()->commit();
				sleep(2); // задержка чтобы в базу успели дозанестись все записи со временем $dtCurUpdate

				$semaphore = App::db()->fetchAssoc(
					'SELECT `dt`, `server`'
					.' FROM `semaphore` WHERE `key`=:key;',
					['key' => $key]
				);
				if($semaphore['dt'] == $dtCurUpdate && $semaphore['server'] == $this->conf['server']['cur_server_domain']) {
					$isCanProcess = true;
				}
			} catch(PDOExecption $e) {
				App::db()->getPDO()->rollback();
			}
		}

		return $isCanProcess;
	}

	public function getLastUpdate($key) {
		$oldSemaphore = App::db()->fetchAssoc(
			'SELECT `dt` FROM `semaphore` WHERE `key`=:key;',
			[
				'key' => $key,
			]
		);
		if($oldSemaphore) {
			return $oldSemaphore['dt'];
		} else {
			return false;
		}
	}
} 