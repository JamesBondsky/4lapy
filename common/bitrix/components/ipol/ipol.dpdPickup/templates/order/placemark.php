<?php
\Bitrix\Main\Localization\Loc::loadMessages(__FILE__);
?>

<div class="DPD_baloon">
	<div class="DPD_iName"><?= $arItem['NAME'] ?></div>
	<div class="DPD_iAdress"><?= $arItem['ADDRESS_FULL'] ?></div>

	<?php foreach (['SELF_PICKUP', 'SELF_DELIVERY', 'SELF_PAYMENT_CASH', 'SELF_PAYMENT_CASHLESS'] as $code) {
		$timetable = $arItem['SCHEDULE_'. $code];
	?>
		<?php if ($timetable) { ?>
			<div class="DPD_iSchedule">
				<div><b><?= GetMessage('IPOLH_DPD_PICKUP_SCHEDULE_'. $code) ?></b></div>
				
				<?php foreach (preg_split('!<br>!', $timetable) as $schedule) { ?>
					<div>
						<div class="DPD_iTime DPD_icon"></div>
						<div class="DPD_baloonDiv"><?= $schedule ?></div>
						<div style="clear: both;"></div>
					</div>
				<?php } ?>
			</div>
		<?php } ?>
	<?php } ?>

	<?php if (!empty($arItem['ADDRESS_DESCR'])) { ?>
		<div class="DPD_address-descr">
			<div><b><?= GetMessage('IPOLH_DPD_PICKUP_ADDRESS_DESCR') ?></b></div>
			<div><?= $arItem['ADDRESS_DESCR'] ?></div>
		</div>
	<?php } ?>

	<a href="javascript:void(0)" class="DPD_button" data-terminal-code="<?= $arItem['ID'] ?>"></a>
</div>