<?php

class BaseMethod extends APIServer
{
	protected function checkCaptcha($key, $code) {
		// del old captcha keys
		App::db()->query(
			'DELETE FROM `captcha` WHERE `dt`<(NOW() - INTERVAL '.$this->conf['captcha']['ttl'].' MINUTE);'
		);
		// check
		$captcha = App::db()->getOne(
			'captcha',
			array('dt'),
			array('key' => $key, 'code' => $code)
		);
		// delete
		App::db()->query(
			'DELETE FROM `captcha` WHERE `key`=:key',
			array('key' => $key)
		);
		if($captcha) {
			return true;
		} else {
			return false;
		}
	}

	// Все базовые функции для методов
	protected function checkUser2Project($projectId) {
		$project = App::db()->fetchAssoc(
			'SELECT `project`.`id`'
			.' FROM `project`'
			.' INNER JOIN `user2project` ON `user2project`.`project_id`=`project`.`id`'
			.' WHERE `project`.`id`=:project_id AND `project`.`is_deleted`=0 AND `user2project`.`user_id`=:user_id',
			array(
				'user_id' => $this->User['id'],
				'project_id' => $projectId,
			)
		);

		if($project) {
			return true;
		} else {
			$this->res['errors'] += $this->ERROR['project_not_found'];
			return false;
		}
	}

	protected function saveDispatcherActivity($dispatcherId) {
		App::db()->query(
			'UPDATE `dispatcher` SET `dt_last_activity`=NOW() WHERE `id`=:process_id;',
			array('process_id' => $dispatcherId)
		);
	}

	// проверка возможности отправки письма
	protected function isCanSendMail($domainCntMailInPeriod = 0) {
		if(
			// vip аккаунт
			$this->User['is_vip']
			// если остался лимит бесплатных писем в данном месяце
			|| ($this->User['tariff_cnt_mail_in_period'] > $domainCntMailInPeriod && $this->User['cnt_unpaid_invoice'] < $this->User['cnt_unpaid_invoice_max'])
			// есть средства на предоплату за каждое письмо на тарифе без абонентской платы
			|| ($this->User['tariff_price'] > 0 && $this->User['tariff_price_domain'] == 0 && $this->User['balance_total'] > 0)
			// принимаем постоплату за подписку на домены
			|| ($this->User['tariff_price_domain'] > 0 && $this->User['cnt_unpaid_invoice'] < $this->User['cnt_unpaid_invoice_max'])
		) {
			return true;
		}
		return false;
	}

	// Получаем количество счетов предоплаты за текущий месяц
	protected function getCntPrepayInvoicesInMonth() {
		$curTime = time();
		$cntPrepayInvoicesInMonth = App::db()->fetchColumn(
			App::db()->query(
				'SELECT COUNT(`id`) as `count`'
				.' FROM `invoice`'
				.' WHERE `contractor_id`=:contractor_id AND `type`=:type_prepay'
				.' AND `dt_add`>=:dt_from AND `dt_add`<=:dt_to;',
				array(
					'contractor_id' => $this->User['contractor_id'],
					'type_prepay' => invoice::TYPE_PREPAY,
					'dt_from' => date('Y-m-01 00:00:00', $curTime),
					'dt_to' => date('Y-m-t 23:59:59', $curTime), // t - Количество дней в указанном месяце
				)
			)
		);

		return $cntPrepayInvoicesInMonth;
	}

	// Списание средств с контрагента за домен
	// Данный вызов должен быть обернут в транзакцию при вызове
	public function contractorDeliveryPayment($contractorId, $balance, $deliveryId) {
		// Получаем баланс по договору
		$contractor = App::db()->fetchAssoc(
			'SELECT `contractor`.`balance_money`, `contractor`.`balance_bonus`,'
			.' `tariff`.`price` as `tariff_price`, `tariff`.`price_domain` as `tariff_price_domain`'
			.' FROM `contractor`'
			.' INNER JOIN `tariff` ON `tariff`.`id`=`contractor`.`tariff_id`'
			.' WHERE `contractor`.`id`=:contractor_id',
			array('contractor_id' => $contractorId)
		);

		// Списание
		$expenseBonus = $contractor['balance_bonus'] >= $balance ? $balance : ($contractor['balance_bonus'] > 0 ? $contractor['balance_bonus'] : 0);
		$expenseLast = $balance - $expenseBonus;
		$expenseMoney = $expenseLast > 0 ? $expenseLast : 0;
		// Высчитываем количество писем оплаченное деньгами и бонусами
		// ВАЖНО: если стоимость письма свыше бесплатного лимита будет не 1 копейка, то следующие величины могут быть дробными
		$cntMailNeedPayBonus = $expenseBonus / $contractor['tariff_price'];
		$cntMailNeedPayMoney = $expenseMoney / $contractor['tariff_price'];

		// Вносим новую запись списания или если такая уже есть увеличиваем количество списанных средств
		App::db()->query(
			($expenseMoney ?
				('INSERT INTO `wallet` (`type`, `balance`, `date`, `delivery_id`, `cnt_mail_paid`, `contractor_id`)'
					.' VALUES ("MONEY", -'.$expenseMoney.', DATE(NOW()), '.$deliveryId.', '.$cntMailNeedPayMoney.', '.$contractorId.')'
					.' ON DUPLICATE KEY UPDATE `balance`=`balance`-'.$expenseMoney.', `cnt_mail_paid`=`cnt_mail_paid`+'.$cntMailNeedPayMoney.';')
				: '')
			.($expenseBonus ?
				('INSERT INTO `wallet` (`type`, `balance`, `date`, `delivery_id`, `cnt_mail_paid`, `contractor_id`)'
					.' VALUES ("BONUS", -'.$expenseBonus.', DATE(NOW()), '.$deliveryId.', '.$cntMailNeedPayBonus.','.$contractorId.')'
					.' ON DUPLICATE KEY UPDATE `balance`=`balance`-'.$expenseBonus.', `cnt_mail_paid`=`cnt_mail_paid`+'.$cntMailNeedPayBonus.';')
				: '')
		);

		return array(
			'balance_money' => $contractor['balance_money'] - $expenseMoney,
			'balance_bonus' => $contractor['balance_bonus'] - $expenseBonus,
		);
	}

	// Проверяем является ли текущий пользователь корневым (на котором завязан договор)
	// Если нет, то выдается ошибка запрета доступа
	protected function isRootRules() {
		if($this->User['is_root']) {
			return true;
		} else {
			$this->res['errors'] += $this->ERROR['access_denied'];
			return false;
		}
	}
}
